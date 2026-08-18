<?php

$path = $argv[1] ?? '';
if ($path === '' || ! is_file($path)) throw new RuntimeException('Workbook not found.');

$zip = new ZipArchive();
if ($zip->open($path) !== true) throw new RuntimeException('Unable to open workbook.');
$sheetPath = 'xl/worksheets/sheet1.xml';
$xml = $zip->getFromName($sheetPath);
if ($xml === false) throw new RuntimeException('Worksheet not found.');
if (str_contains($xml, '421482258')) {
    $zip->close();
    echo "already-present\n";
    exit(0);
}

$document = new DOMDocument('1.0', 'UTF-8');
$document->preserveWhiteSpace = false;
$document->loadXML($xml);
$xpath = new DOMXPath($document);
$xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
$rows = $xpath->query('//m:sheetData/m:row');
$lastRow = $rows->item($rows->length - 1);
$rowNumber = (int) $lastRow->getAttribute('r') + 1;
$sequence = $rowNumber - 1;
$namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
$row = $document->createElementNS($namespace, 'row');
$row->setAttribute('r', (string) $rowNumber);

$values = [
    ['A', 'n', (string) $sequence, '2'],
    ['B', 'inlineStr', 'يمنى أيمن يوسف أمن', '3'],
    ['C', 'n', '', '2'],
    ['D', 'n', '421482258', '2'],
    ['E', 'n', '', '2'],
    ['F', 'n', '', '2'],
    ['G', 'inlineStr', 'الجامعة الإسلامية بغزة', '2'],
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
    } elseif ($value !== '') {
        $number = $document->createElementNS($namespace, 'v');
        $number->appendChild($document->createTextNode($value));
        $cell->appendChild($number);
    }
    $row->appendChild($cell);
}
$lastRow->parentNode->appendChild($row);
$dimension = $xpath->query('//m:dimension')->item(0);
if ($dimension) $dimension->setAttribute('ref', 'A1:G'.$rowNumber);
$zip->addFromString($sheetPath, $document->saveXML($document->documentElement));
$zip->close();
echo json_encode(['row' => $rowNumber, 'sequence' => $sequence], JSON_UNESCAPED_UNICODE)."\n";
