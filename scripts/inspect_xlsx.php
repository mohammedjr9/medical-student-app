<?php

if ($argc < 2) {
    fwrite(STDERR, "Usage: php inspect_xlsx.php <file.xlsx>\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($argv[1]) !== true) {
    throw new RuntimeException('Unable to open workbook.');
}

$loadXml = static function (string $xml): array {
    $document = new DOMDocument();
    $document->loadXML($xml);
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    return [$document, $xpath];
};

$shared = [];
$sharedXml = $zip->getFromName('xl/sharedStrings.xml');
if ($sharedXml !== false) {
    [, $xpath] = $loadXml($sharedXml);
    foreach ($xpath->query('//m:si') as $item) {
        $parts = [];
        foreach ($xpath->query('.//m:t', $item) as $text) $parts[] = $text->textContent;
        $shared[] = implode('', $parts);
    }
}

[, $workbookXPath] = $loadXml($zip->getFromName('xl/workbook.xml'));
[$relationshipsDocument] = $loadXml($zip->getFromName('xl/_rels/workbook.xml.rels'));
$relationshipMap = [];
foreach ($relationshipsDocument->documentElement->childNodes as $relationship) {
    if ($relationship instanceof DOMElement) {
        $relationshipMap[$relationship->getAttribute('Id')] = 'xl/'.ltrim($relationship->getAttribute('Target'), '/');
    }
}

foreach ($workbookXPath->query('//m:sheet') as $sheet) {
    $path = $relationshipMap[$sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id')] ?? null;
    if (! $path) continue;
    [, $sheetXPath] = $loadXml($zip->getFromName($path));
    echo 'SHEET: '.$sheet->getAttribute('name').PHP_EOL;
    foreach ($sheetXPath->query('//m:sheetData/m:row') as $row) {
        $values = [];
        foreach ($sheetXPath->query('./m:c', $row) as $cell) {
            $type = $cell->getAttribute('t');
            $valueNode = $sheetXPath->query('./m:v', $cell)->item(0);
            if ($type === 's') {
                $value = $shared[(int) ($valueNode?->textContent ?? -1)] ?? '';
            } elseif ($type === 'inlineStr') {
                $parts = [];
                foreach ($sheetXPath->query('.//m:t', $cell) as $text) $parts[] = $text->textContent;
                $value = implode('', $parts);
            } else {
                $value = $valueNode?->textContent ?? '';
            }
            $values[$cell->getAttribute('r')] = $value;
        }
        echo json_encode($values, JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}

$zip->close();
