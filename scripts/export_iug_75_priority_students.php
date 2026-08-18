<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$universityId = $argv[1] ?? 'IUG';
$limit = isset($argv[2]) ? (int) $argv[2] : 75;
$universityNames = ['IUG' => 'الجامعة الإسلامية بغزة', 'UPAL' => 'جامعة فلسطين'];
$universitySlugs = ['IUG' => 'iug', 'UPAL' => 'upal'];
$universityName = $universityNames[$universityId] ?? $universityId;
$universitySlug = $universitySlugs[$universityId] ?? strtolower($universityId);
$includeFirstYear = ($argv[3] ?? '') === 'include-first-year';
$gpaOperator = ($argv[3] ?? '') === 'above-80' ? '>' : '>=';
$levels = $includeFirstYear
    ? ['level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'internship']
    : ['level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'internship'];
$excludedNames = $universityId === 'IUG' ? [
    'شهد زكريا محمد الشيخ علي',
    'احمد طارق احمد عقيلان',
    'تقوى ماجد يوسف الحديدي',
] : [];
$excludedNationalIds = $universityId === 'IUG' ? ['900816174'] : [];
$base = MedicalRegistration::query()
    ->where('university_id', $universityId)
    ->whereIn('academic_level', $levels)
    ->where('gpa', $gpaOperator, 80)
    ->whereNotIn('full_name', $excludedNames)
    ->whereNotIn('national_id', $excludedNationalIds);

$students = (clone $base)
    ->orderByRaw("CASE is_father_martyr WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByRaw("CASE has_sibling_student WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByDesc('gpa')
    ->orderBy('id')
    ->limit($limit)
    ->get();

if ($students->count() < $limit) {
    throw new RuntimeException("لا يوجد {$limit} طالباً مؤهلاً من {$universityName} وفق الشروط المحددة.");
}

$levelNames = [
    'level_1' => 'السنة الأولى',
    'level_2' => 'السنة الثانية', 'level_3' => 'السنة الثالثة',
    'level_4' => 'السنة الرابعة', 'level_5' => 'السنة الخامسة',
    'level_6' => 'السنة السادسة', 'internship' => 'سنة الامتياز',
];
$housingNames = [
    'tent' => 'خيمة', 'shelter' => 'مركز إيواء', 'house' => 'منزل',
    'apartment' => 'شقة', 'relatives' => 'منزل أقارب', 'other' => 'أخرى',
];

$headers = ['الترتيب', 'فئة الأولوية', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'الجامعة', 'السنة الدراسية', 'المعدل', 'السكن', 'الوالد شهيد/متوفى', 'إصابة أو إعاقة', 'أخ/أخت في الجامعة', 'اسم الأخ/الأخت', 'جامعة الأخ/الأخت', 'سبب الأولوية', 'الرقم المرجعي'];
$rows = [$headers];
foreach ($students as $index => $student) {
    $reasons = [];
    if ($student->is_father_martyr === 'yes') $reasons[] = 'الوالد شهيد/متوفى';
    if ($student->has_sibling_student === 'yes') $reasons[] = 'له أخ/أخت في الجامعة';
    if ($student->has_disability === 'yes') $reasons[] = 'إصابة أو إعاقة';
    $score = ($student->is_father_martyr === 'yes' ? 4 : 0)
        + ($student->has_sibling_student === 'yes' ? 2 : 0)
        + ($student->has_disability === 'yes' ? 1 : 0);
    $rows[] = [
        $index + 1, 'أولوية '.$score.'/7', $student->full_name, $student->national_id,
        $student->mobile_number, $universityName, $levelNames[$student->academic_level] ?? $student->academic_level,
        (float) $student->gpa, $housingNames[$student->housing_type] ?? $student->housing_type,
        $student->is_father_martyr === 'yes' ? 'نعم' : 'لا',
        $student->has_disability === 'yes' ? 'نعم' : 'لا',
        $student->has_sibling_student === 'yes' ? 'نعم' : 'لا',
        $student->sibling_name, $student->sibling_university,
        implode('، ', $reasons).($reasons ? '، ' : '').'المعدل: '.$student->gpa,
        $student->reference_number,
    ];
}

$xml = static fn ($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
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
        if ($rowIndex > 0 && in_array($columnIndex, [0, 7], true)) {
            $cells .= '<c r="'.$ref.'" s="'.($columnIndex === 7 ? '2' : '1').'"><v>'.$xml($value).'</v></c>';
        } else {
            $cells .= '<c r="'.$ref.'" t="inlineStr" s="'.($rowIndex === 0 ? '3' : '1').'"><is><t>'.$xml($value).'</t></is></c>';
        }
    }
    $sheetRows .= '<row r="'.$excelRow.'"'.($rowIndex === 0 ? ' ht="30" customHeight="1"' : '').'>'.$cells.'</row>';
}

$sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView rightToLeft="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
    .'<cols><col min="1" max="2" width="14" customWidth="1"/><col min="3" max="3" width="32" customWidth="1"/><col min="4" max="5" width="18" customWidth="1"/><col min="6" max="16" width="23" customWidth="1"/></cols>'
    .'<sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:P'.count($rows).'"/></worksheet>';
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF15803D"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="right" vertical="center" wrapText="1"/></xf><xf numFmtId="2" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs></styleSheet>';

$directory = base_path('exports');
if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) throw new RuntimeException('تعذر إنشاء مجلد التصدير.');
$path = $directory.'/'.$universitySlug.'-'.$limit.'-priority-students-'.date('Ymd-His').'.xlsx';
$zip = new ZipArchive();
if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('تعذر إنشاء ملف Excel.');
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="أولوية '.$xml($universityName).' '.$limit.'" sheetId="1" r:id="rId1"/></sheets></workbook>');
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
$zip->addFromString('xl/styles.xml', $styles);
$zip->close();

echo json_encode([
    'file' => $path, 'eligible_total' => (clone $base)->count(), 'selected' => $students->count(),
    'excluded_names' => $excludedNames,
    'excluded_names_still_selected' => $students->whereIn('full_name', $excludedNames)->values()->pluck('full_name')->all(),
    'excluded_national_ids' => $excludedNationalIds,
    'excluded_national_ids_still_selected' => $students->whereIn('national_id', $excludedNationalIds)->values()->pluck('national_id')->all(),
    'all_three_selected' => $students->filter(fn ($s) => $s->is_father_martyr === 'yes' && $s->has_sibling_student === 'yes' && $s->has_disability === 'yes')->count(),
    'martyr_selected' => $students->where('is_father_martyr', 'yes')->count(),
    'sibling_selected' => $students->where('has_sibling_student', 'yes')->count(),
    'injury_or_disability_selected' => $students->where('has_disability', 'yes')->count(),
    'highest_gpa' => $students->max('gpa'), 'lowest_gpa' => $students->min('gpa'),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
