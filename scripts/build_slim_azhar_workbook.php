<?php

$source = __DIR__.'/../exports/azhar-review-source.xlsx';
$output = __DIR__.'/../exports/azhar-students-slim-final.xlsx';

$zip = new ZipArchive();
if ($zip->open($source) !== true) throw new RuntimeException('Unable to open source workbook.');

$xmlDocument = static function (string $xml): array {
    $document = new DOMDocument();
    $document->loadXML($xml);
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    return [$document, $xpath];
};

$shared = [];
[, $sharedXPath] = $xmlDocument($zip->getFromName('xl/sharedStrings.xml'));
foreach ($sharedXPath->query('//m:si') as $item) {
    $text = '';
    foreach ($sharedXPath->query('.//m:t', $item) as $node) $text .= $node->textContent;
    $shared[] = $text;
}

$readSheet = static function (string $path) use ($zip, $xmlDocument, $shared): array {
    [, $xpath] = $xmlDocument($zip->getFromName($path));
    $rows = [];
    foreach ($xpath->query('//m:sheetData/m:row') as $row) {
        $values = [];
        foreach ($xpath->query('./m:c', $row) as $cell) {
            $column = preg_replace('/\d+/', '', $cell->getAttribute('r'));
            $node = $xpath->query('./m:v', $cell)->item(0);
            $value = $node?->textContent ?? '';
            if ($cell->getAttribute('t') === 's') $value = $shared[(int) $value] ?? '';
            $values[$column] = trim($value);
        }
        $rows[] = $values;
    }
    return $rows;
};

$selectedRows = $readSheet('xl/worksheets/sheet1.xml');
$inspectionRows = $readSheet('xl/worksheets/sheet2.xml');
$zip->close();

$specialtyByStudentNumber = [];
foreach (array_slice($inspectionRows, 1) as $row) {
    if (($row['B'] ?? '') !== '') $specialtyByStudentNumber[$row['B']] = $row['H'] ?? '';
}

$rows = [];
foreach (array_slice($selectedRows, 1) as $row) {
    $studentNumber = $row['B'] ?? '';
    $rows[] = [
        'name' => $row['A'] ?? '',
        'student_number' => $studentNumber,
        'national_id' => $row['C'] ?? '',
        'specialty' => $specialtyByStudentNumber[$studentNumber] ?? '',
        'level' => $row['F'] ?? '',
        'gpa' => $row['E'] ?? '',
    ];
}

$additions = [
    ['سجود طلال جابر عبد العال', '20231742', '421313495', 'طب بشري', 'السنة الثالثة', '92.89'],
    ['فرح اسامة محمد الاخرس', '', '', 'طب أسنان', 'السنة الخامسة', ''],
    ['نعيم يونس نعيم قنيطة', '', '424081883', 'طب بشري', 'السنة الثانية', '91'],
    ['شامة سيد خضر عاشور البطش', '', '', 'طب بشري', 'السنة الرابعة', ''],
    ['خالد انور خالد حرارة', '', '', 'طب بشري', '', ''],
    ['احمد جميل يوسف الحرازين', '', '', 'طب بشري', '', ''],
    ['رواء هاني محمد ابو حجاج', '', '', 'طب بشري', '', ''],
    ['تسنيم عبد الفتاح حجاج', '', '', 'طب بشري', '', ''],
    ['سعاد أسامة عيادة مرزوق', '', '420478919', 'طب بشري', 'السنة الرابعة', '89'],
    ['أمل محسن الكيلاني', '20254739', '424468502', 'طب أسنان', '', ''],
    ['اسماء عبدالله سكر', '202510329', '422456400', 'طب أسنان', '', ''],
    ['أنور عبدالله محمد سكر', '20223768', '421270711', 'طب بشري', 'السنة الرابعة', '86'],
    ['أسامة فضل أسامة الحرازين', '20251064', '424333995', 'طب بشري', 'السنة الأولى', '92.17'],
    ['رهف رمزي سامي عليان', '20211607', '409062775', 'طب بشري', 'السنة الخامسة', '87'],
];

$normalize = static function (string $value): string {
    $value = str_replace(['أ', 'إ', 'آ', 'ى', 'ة', 'ؤ', 'ئ'], ['ا', 'ا', 'ا', 'ي', 'ه', 'و', 'ي'], $value);
    return preg_replace('/[^\p{Arabic}0-9]+/u', '', $value);
};

foreach ($additions as [$name, $studentNumber, $nationalId, $specialty, $level, $gpa]) {
    $match = null;
    foreach ($rows as $index => $row) {
        if (($nationalId !== '' && $row['national_id'] === $nationalId)
            || ($studentNumber !== '' && $row['student_number'] === $studentNumber)
            || $normalize($row['name']) === $normalize($name)) {
            $match = $index;
            break;
        }
    }
    if ($match === null) {
        $rows[] = compact('name', 'studentNumber', 'nationalId', 'specialty', 'level', 'gpa');
        $last = array_key_last($rows);
        $rows[$last] = [
            'name' => $name, 'student_number' => $studentNumber, 'national_id' => $nationalId,
            'specialty' => $specialty, 'level' => $level, 'gpa' => $gpa,
        ];
    } else {
        foreach (['student_number' => $studentNumber, 'national_id' => $nationalId, 'specialty' => $specialty, 'level' => $level, 'gpa' => $gpa] as $key => $value) {
            if ($rows[$match][$key] === '' && $value !== '') $rows[$match][$key] = $value;
        }
    }
}

$headers = ['م', 'الاسم', 'الرقم الجامعي', 'رقم الهوية', 'التخصص', 'المستوى', 'المعدل'];
$data = [$headers];
foreach ($rows as $index => $row) {
    $data[] = [$index + 1, $row['name'], $row['student_number'], $row['national_id'], $row['specialty'], $row['level'], $row['gpa']];
}

$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$column = static fn (int $index) => chr(65 + $index);
$sheetRows = '';
foreach ($data as $rowIndex => $row) {
    $excelRow = $rowIndex + 1;
    $cells = '';
    foreach ($row as $columnIndex => $value) {
        $ref = $column($columnIndex).$excelRow;
        if ($rowIndex > 0 && in_array($columnIndex, [0, 6], true) && $value !== '') {
            $cells .= '<c r="'.$ref.'" s="'.($columnIndex === 6 ? '2' : '1').'"><v>'.$escape($value).'</v></c>';
        } else {
            $cells .= '<c r="'.$ref.'" t="inlineStr" s="'.($rowIndex === 0 ? '3' : '1').'"><is><t>'.$escape($value).'</t></is></c>';
        }
    }
    $sheetRows .= '<row r="'.$excelRow.'"'.($rowIndex === 0 ? ' ht="30" customHeight="1"' : '').'>'.$cells.'</row>';
}

$sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView rightToLeft="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="7" customWidth="1"/><col min="2" max="2" width="34" customWidth="1"/><col min="3" max="4" width="18" customWidth="1"/><col min="5" max="6" width="22" customWidth="1"/><col min="7" max="7" width="12" customWidth="1"/></cols><sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:G'.count($data).'"/></worksheet>';
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF176B5B"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="4"><xf/><xf><alignment horizontal="right" vertical="center"/></xf><xf numFmtId="2"><alignment horizontal="center"/></xf><xf fontId="1" fillId="2"><alignment horizontal="center" vertical="center"/></xf></cellXfs></styleSheet>';

$out = new ZipArchive();
if ($out->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Unable to create output workbook.');
$out->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
$out->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$out->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="طلاب جامعة الأزهر" sheetId="1" r:id="rId1"/></sheets></workbook>');
$out->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
$out->addFromString('xl/worksheets/sheet1.xml', $sheet);
$out->addFromString('xl/styles.xml', $styles);
$out->close();

echo json_encode(['file' => realpath($output), 'original_students' => count($selectedRows) - 1, 'final_students' => count($rows), 'added' => count($rows) - (count($selectedRows) - 1)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
