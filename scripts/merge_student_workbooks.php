<?php

$sources = [
    ['file' => __DIR__.'/../exports/merge-azhar.xlsx', 'sheets' => [['index' => 0, 'name' => 'جامعة الأزهر', 'source' => 'كشف جامعة الأزهر - محدث']]],
    ['file' => __DIR__.'/../exports/merge-dentistry.xlsx', 'sheets' => [['index' => 0, 'name' => 'طب الأسنان 50', 'source' => 'طلاب أسنان نهائي 50']]],
    ['file' => __DIR__.'/../exports/merge-medicine.xlsx', 'sheets' => [
        ['index' => 0, 'name' => 'الطب البشري 83', 'source' => 'بيانات منحة كلية الطب 83 طالب'],
        ['index' => 1, 'name' => 'الطب - مستوى أول', 'source' => 'الورقة الإضافية: مستوى أول وأقل حاجة'],
    ]],
];
$output = __DIR__.'/../exports/all-student-lists-organized.xlsx';

$parseWorkbook = static function (string $file): array {
    $zip = new ZipArchive();
    if ($zip->open($file) !== true) throw new RuntimeException("Unable to open {$file}");
    $dom = static function (string $xml): array {
        $document = new DOMDocument();
        $document->loadXML($xml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        return [$document, $xpath];
    };
    $shared = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        [, $xpath] = $dom($sharedXml);
        foreach ($xpath->query('//m:si') as $item) {
            $value = '';
            foreach ($xpath->query('.//m:t', $item) as $node) $value .= $node->textContent;
            $shared[] = $value;
        }
    }
    [, $workbookXPath] = $dom($zip->getFromName('xl/workbook.xml'));
    [$relsDocument] = $dom($zip->getFromName('xl/_rels/workbook.xml.rels'));
    $relationships = [];
    foreach ($relsDocument->documentElement->childNodes as $node) {
        if ($node instanceof DOMElement) $relationships[$node->getAttribute('Id')] = 'xl/'.ltrim($node->getAttribute('Target'), '/');
    }
    $sheets = [];
    foreach ($workbookXPath->query('//m:sheet') as $sheet) {
        $id = $sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
        [, $xpath] = $dom($zip->getFromName($relationships[$id]));
        $rows = [];
        foreach ($xpath->query('//m:sheetData/m:row') as $row) {
            $values = [];
            foreach ($xpath->query('./m:c', $row) as $cell) {
                $column = preg_replace('/\d+/', '', $cell->getAttribute('r'));
                $valueNode = $xpath->query('./m:v', $cell)->item(0);
                $value = $valueNode?->textContent ?? '';
                $type = $cell->getAttribute('t');
                if ($type === 's') $value = $shared[(int) $value] ?? '';
                if ($type === 'inlineStr') {
                    $value = '';
                    foreach ($xpath->query('.//m:t', $cell) as $text) $value .= $text->textContent;
                }
                $values[$column] = trim($value);
            }
            if ($values !== []) $rows[] = $values;
        }
        $sheets[] = $rows;
    }
    $zip->close();
    return $sheets;
};

$columnNumber = static function (string $letters): int {
    $number = 0;
    foreach (str_split($letters) as $letter) $number = $number * 26 + ord($letter) - 64;
    return $number;
};
$columnName = static function (int $number): string {
    $name = '';
    while ($number > 0) { $number--; $name = chr(65 + ($number % 26)).$name; $number = intdiv($number, 26); }
    return $name;
};

$finalSheets = [];
foreach ($sources as $source) {
    $parsed = $parseWorkbook($source['file']);
    foreach ($source['sheets'] as $selection) {
        $sourceRows = $parsed[$selection['index']] ?? [];
        $matrix = [];
        foreach ($sourceRows as $row) {
            $max = 0;
            foreach (array_keys($row) as $column) $max = max($max, $columnNumber($column));
            $values = [];
            for ($i = 1; $i <= $max; $i++) $values[] = $row[$columnName($i)] ?? '';
            $matrix[] = $values;
        }
        $finalSheets[] = ['name' => $selection['name'], 'source' => $selection['source'], 'rows' => $matrix];
    }
}

$summary = [['القائمة', 'عدد الطلبة', 'المصدر']];
$total = 0;
foreach ($finalSheets as $sheet) {
    $count = max(0, count($sheet['rows']) - 1);
    $total += $count;
    $summary[] = [$sheet['name'], $count, $sheet['source']];
}
$summary[] = ['الإجمالي عبر القوائم', $total, 'قد يتضمن أسماء مكررة بين القوائم'];
array_unshift($finalSheets, ['name' => 'الفهرس', 'source' => '', 'rows' => $summary]);

$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$sheetXml = static function (array $rows) use ($escape, $columnName): string {
    $xmlRows = '';
    $maxColumns = 1;
    foreach ($rows as $rowIndex => $row) {
        $excelRow = $rowIndex + 1;
        $maxColumns = max($maxColumns, count($row));
        $cells = '';
        foreach ($row as $columnIndex => $value) {
            $ref = $columnName($columnIndex + 1).$excelRow;
            $cells .= '<c r="'.$ref.'" t="inlineStr" s="'.($rowIndex === 0 ? '2' : '1').'"><is><t xml:space="preserve">'.$escape($value).'</t></is></c>';
        }
        $xmlRows .= '<row r="'.$excelRow.'"'.($rowIndex === 0 ? ' ht="30" customHeight="1"' : '').'>'.$cells.'</row>';
    }
    $lastColumn = $columnName($maxColumns);
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView rightToLeft="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="9" customWidth="1"/><col min="2" max="2" width="32" customWidth="1"/><col min="3" max="'.$maxColumns.'" width="20" customWidth="1"/></cols><sheetData>'.$xmlRows.'</sheetData><autoFilter ref="A1:'.$lastColumn.count($rows).'"/><pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5"/></worksheet>';
};

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF176B5B"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="3"><xf/><xf><alignment horizontal="right" vertical="center" wrapText="1"/></xf><xf fontId="1" fillId="2"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs></styleSheet>';

$out = new ZipArchive();
if ($out->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Unable to create workbook.');
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
$workbookSheets = '';
$workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
foreach ($finalSheets as $index => $sheet) {
    $number = $index + 1;
    $contentTypes .= '<Override PartName="/xl/worksheets/sheet'.$number.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    $workbookSheets .= '<sheet name="'.$escape($sheet['name']).'" sheetId="'.$number.'" r:id="rId'.$number.'"/>';
    $workbookRels .= '<Relationship Id="rId'.$number.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$number.'.xml"/>';
    $out->addFromString('xl/worksheets/sheet'.$number.'.xml', $sheetXml($sheet['rows']));
}
$contentTypes .= '</Types>';
$workbookRels .= '<Relationship Id="rId'.(count($finalSheets) + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
$out->addFromString('[Content_Types].xml', $contentTypes);
$out->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$out->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'.$workbookSheets.'</sheets></workbook>');
$out->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
$out->addFromString('xl/styles.xml', $styles);
$out->close();

echo json_encode(['file' => realpath($output), 'sheets' => array_map(fn ($sheet) => ['name' => $sheet['name'], 'students' => max(0, count($sheet['rows']) - 1)], $finalSheets)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
