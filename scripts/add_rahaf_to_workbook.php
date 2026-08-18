<?php

$path = $argv[1] ?? '';
if ($path === '' || ! is_file($path)) {
    throw new RuntimeException('Workbook not found.');
}

$zip = new ZipArchive();
if ($zip->open($path) !== true) {
    throw new RuntimeException('Unable to open workbook.');
}

$sheetPath = 'xl/worksheets/sheet1.xml';
$xml = $zip->getFromName($sheetPath);
if ($xml === false) {
    throw new RuntimeException('Worksheet not found.');
}

if (str_contains($xml, '409062775') || str_contains($xml, '20211607')) {
    $zip->close();
    echo "already-present\n";
    exit(0);
}

$document = new DOMDocument('1.0', 'UTF-8');
$document->preserveWhiteSpace = false;
$document->formatOutput = false;
$document->loadXML($xml);
$xpath = new DOMXPath($document);
$xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

$rows = $xpath->query('//m:sheetData/m:row');
$lastRow = $rows->item($rows->length - 1);
$rowNumber = ((int) $lastRow->getAttribute('r')) + 1;
$sequence = $rowNumber - 1;
$namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

$row = $document->createElementNS($namespace, 'row');
$row->setAttribute('r', (string) $rowNumber);
$values = [
    ['A', 'n', (string) $sequence, '2'],
    ['B', 'inlineStr', 'رهف رمزي سامي عليان', '3'],
    ['C', 'inlineStr', '20211607', '2'],
    ['D', 'n', '409062775', '2'],
    ['E', 'inlineStr', 'السنة الخامسة', '2'],
    ['F', 'n', '87', '2'],
    ['G', 'inlineStr', 'جامعة الأزهر بغزة', '2'],
];

foreach ($values as [$column, $type, $value, $style]) {
    $cell = $document->createElementNS($namespace, 'c');
    $cell->setAttribute('r', $column.$rowNumber);
    $cell->setAttribute('s', $style);
    $cell->setAttribute('t', $type);
    if ($type === 'inlineStr') {
        $inline = $document->createElementNS($namespace, 'is');
        $text = $document->createElementNS($namespace, 't');
        $text->appendChild($document->createTextNode($value));
        $inline->appendChild($text);
        $cell->appendChild($inline);
    } else {
        $number = $document->createElementNS($namespace, 'v');
        $number->appendChild($document->createTextNode($value));
        $cell->appendChild($number);
    }
    $row->appendChild($cell);
}

$lastRow->parentNode->appendChild($row);
$dimension = $xpath->query('//m:dimension')->item(0);
if ($dimension) {
    $dimension->setAttribute('ref', 'A1:G'.$rowNumber);
}

$zip->addFromString($sheetPath, $document->saveXML($document->documentElement));
$zip->close();
echo json_encode(['row' => $rowNumber, 'sequence' => $sequence], JSON_UNESCAPED_UNICODE)."\n";
