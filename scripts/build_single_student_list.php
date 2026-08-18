<?php

$source = __DIR__.'/../exports/all-student-lists-organized.xlsx';
$output = __DIR__.'/../exports/single-student-list.xlsx';

$zip = new ZipArchive();
if ($zip->open($source) !== true) throw new RuntimeException('Unable to open source workbook.');

$loadXml = static function (string $xml): array {
    $document = new DOMDocument();
    $document->loadXML($xml);
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    return [$document, $xpath];
};

[, $workbookXPath] = $loadXml($zip->getFromName('xl/workbook.xml'));
[$relationshipsDocument] = $loadXml($zip->getFromName('xl/_rels/workbook.xml.rels'));
$relationships = [];
foreach ($relationshipsDocument->documentElement->childNodes as $node) {
    if ($node instanceof DOMElement) $relationships[$node->getAttribute('Id')] = 'xl/'.ltrim($node->getAttribute('Target'), '/');
}

$sheets = [];
foreach ($workbookXPath->query('//m:sheet') as $sheetIndex => $sheet) {
    if ($sheetIndex === 0) continue;
    $id = $sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
    [, $xpath] = $loadXml($zip->getFromName($relationships[$id]));
    $rows = [];
    foreach ($xpath->query('//m:sheetData/m:row') as $row) {
        $values = [];
        foreach ($xpath->query('./m:c', $row) as $cell) {
            $column = preg_replace('/\d+/', '', $cell->getAttribute('r'));
            $value = '';
            foreach ($xpath->query('.//m:t', $cell) as $text) $value .= $text->textContent;
            $values[$column] = trim($value);
        }
        $rows[] = $values;
    }
    $sheets[] = $rows;
}
$zip->close();

$columnNumber = static function (string $letters): int {
    $number = 0;
    foreach (str_split($letters) as $letter) $number = $number * 26 + ord($letter) - 64;
    return $number;
};

$aliases = [
    'student_number' => ['رقم الطالب', 'الرقم الجامعي'],
    'name' => ['اسم الطالب', 'الاسم'],
    'national_id' => ['رقم الهوية'],
    'specialty' => ['التخصص'],
    'mobile' => ['رقم الجوال'],
    'status' => ['الحالة', 'حالة الطالب'],
    'level' => ['المستوى'],
    'gpa' => ['المعدل', 'المعدل التراكمي'],
];

$students = [];
$seen = [];
foreach ($sheets as $rows) {
    if ($rows === []) continue;
    $headerMap = [];
    foreach ($rows[0] as $column => $header) {
        foreach ($aliases as $key => $names) {
            if (in_array($header, $names, true)) $headerMap[$key] = $column;
        }
    }
    foreach (array_slice($rows, 1) as $row) {
        $student = [];
        foreach (array_keys($aliases) as $key) $student[$key] = isset($headerMap[$key]) ? ($row[$headerMap[$key]] ?? '') : '';
        if ($student['name'] === '') continue;
        if ($student['level'] === '1' || preg_match('/السنة\s+(الأولى|الاولى)/u', $student['level'])) {
            $student['level'] = '';
        }
        if (str_contains($student['specialty'], 'المرحلة الأساسية')
            || str_contains($student['specialty'], 'المرحلة الاساسية')
            || str_contains($student['specialty'], '1002')) {
            $student['specialty'] = '';
        }
        $identity = $student['national_id'] !== '' ? 'id:'.$student['national_id']
            : ($student['student_number'] !== '' ? 'number:'.$student['student_number'] : 'name:'.preg_replace('/\s+/u', '', $student['name']));
        if (isset($seen[$identity])) {
            $index = $seen[$identity];
            foreach ($student as $key => $value) if ($students[$index][$key] === '' && $value !== '') $students[$index][$key] = $value;
            continue;
        }
        $seen[$identity] = count($students);
        $students[] = $student;
    }
}

$headers = ['م', 'رقم الطالب', 'اسم الطالب', 'رقم الهوية', 'التخصص', 'رقم الجوال', 'الحالة', 'المستوى', 'المعدل'];
$rows = [$headers];
foreach ($students as $index => $student) {
    $rows[] = [$index + 1, $student['student_number'], $student['name'], $student['national_id'], $student['specialty'], $student['mobile'], $student['status'], $student['level'], $student['gpa']];
}

$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$columnName = static function (int $number): string {
    $name = '';
    while ($number > 0) { $number--; $name = chr(65 + ($number % 26)).$name; $number = intdiv($number, 26); }
    return $name;
};
$sheetRows = '';
foreach ($rows as $rowIndex => $row) {
    $excelRow = $rowIndex + 1;
    $cells = '';
    foreach ($row as $columnIndex => $value) {
        $ref = $columnName($columnIndex + 1).$excelRow;
        $cells .= '<c r="'.$ref.'" t="inlineStr" s="'.($rowIndex === 0 ? '2' : '1').'"><is><t xml:space="preserve">'.$escape($value).'</t></is></c>';
    }
    $sheetRows .= '<row r="'.$excelRow.'"'.($rowIndex === 0 ? ' ht="30" customHeight="1"' : '').'>'.$cells.'</row>';
}

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView rightToLeft="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="8" customWidth="1"/><col min="2" max="2" width="18" customWidth="1"/><col min="3" max="3" width="34" customWidth="1"/><col min="4" max="4" width="18" customWidth="1"/><col min="5" max="9" width="19" customWidth="1"/></cols><sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:I'.count($rows).'"/><pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5"/></worksheet>';
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF176B5B"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="3"><xf/><xf><alignment horizontal="right" vertical="center" wrapText="1"/></xf><xf fontId="1" fillId="2"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs></styleSheet>';

$out = new ZipArchive();
if ($out->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Unable to create output workbook.');
$out->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
$out->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$out->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="كشف الطلبة" sheetId="1" r:id="rId1"/></sheets></workbook>');
$out->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
$out->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$out->addFromString('xl/styles.xml', $styles);
$out->close();

echo realpath($output).PHP_EOL;
