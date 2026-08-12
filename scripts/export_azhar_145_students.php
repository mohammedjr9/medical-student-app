<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$base = MedicalRegistration::query()
    ->where('university_id', 'AUG')
    ->where('academic_level', '!=', 'level_1')
    ->where('gpa', '>', 80)
    ->where('is_father_martyr', 'yes')
    ->orderByRaw("CASE has_sibling_student WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByDesc('gpa')
    ->orderBy('id')
    ->get();

$needed = max(0, 145 - $base->count());
$additional = MedicalRegistration::query()
    ->where('university_id', 'AUG')
    ->where('academic_level', '!=', 'level_1')
    ->where('gpa', '>', 80)
    ->where('is_father_martyr', '!=', 'yes')
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByDesc('gpa')
    ->orderByRaw("CASE has_sibling_student WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderBy('id')
    ->limit($needed)
    ->get();

$students = $base->map(fn ($student) => [$student, 'أساسي: معدل فوق 80 + الأب شهيد/أسير'])
    ->concat($additional->map(fn ($student) => [$student, 'استكمال: إصابة/إعاقة ثم أعلى معدل']))
    ->values();

$yellowStudentIds = collect([1187, 320, 1875, 408, 962, 2508, 169]);
$existingIds = $students->pluck(0)->pluck('id');
$yellowAdditions = MedicalRegistration::query()
    ->where('university_id', 'AUG')
    ->whereIn('id', $yellowStudentIds->diff($existingIds))
    ->orderBy('full_name')
    ->get();
$students = $students->concat(
    $yellowAdditions->map(fn ($student) => [$student, 'إعادة من الصفوف الصفراء في الملف القديم'])
)->values();

if ($students->count() < 145) {
    throw new RuntimeException('لا يوجد عدد كافٍ للوصول إلى 145 طالبًا وفق المعايير المحددة.');
}

$levels = [
    'level_1' => 'السنة الأولى', 'level_2' => 'السنة الثانية', 'level_3' => 'السنة الثالثة',
    'level_4' => 'السنة الرابعة', 'level_5' => 'السنة الخامسة', 'level_6' => 'السنة السادسة',
    'internship' => 'سنة الامتياز',
];
$housing = [
    'tent' => 'خيمة', 'shelter' => 'مركز إيواء', 'house' => 'منزل',
    'apartment' => 'شقة', 'relatives' => 'منزل أقارب', 'other' => 'أخرى',
];
$headers = ['الترتيب', 'فئة الاختيار', 'من الصفوف الصفراء القديمة', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'المعدل', 'السنة الدراسية', 'السكن', 'الأب شهيد/أسير', 'إصابة أو إعاقة', 'أخ/أخت في الجامعة', 'اسم الأخ/الأخت', 'جامعة الأخ/الأخت', 'الرقم المرجعي'];
$rows = [$headers];
foreach ($students as $index => [$student, $category]) {
    $rows[] = [
        $index + 1, $category, $yellowStudentIds->contains($student->id) ? 'نعم' : 'لا', $student->full_name, $student->national_id, $student->mobile_number,
        (float) $student->gpa, $levels[$student->academic_level] ?? $student->academic_level,
        $housing[$student->housing_type] ?? $student->housing_type,
        $student->is_father_martyr === 'yes' ? 'نعم' : 'لا',
        $student->has_disability === 'yes' ? 'نعم' : 'لا',
        $student->has_sibling_student === 'yes' ? 'نعم' : 'لا',
        $student->sibling_name, $student->sibling_university, $student->reference_number,
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
    $isYellowRow = $rowIndex > 0 && $yellowStudentIds->contains($students[$rowIndex - 1][0]->id);
    $cells = '';
    foreach ($row as $columnIndex => $value) {
        $ref = $columnName($columnIndex + 1).$excelRow;
        if ($rowIndex > 0 && in_array($columnIndex, [0, 6], true)) {
            $cells .= '<c r="'.$ref.'" s="'.($isYellowRow ? ($columnIndex === 6 ? '5' : '4') : ($columnIndex === 6 ? '2' : '1')).'"><v>'.$xml($value).'</v></c>';
        } else {
            $cells .= '<c r="'.$ref.'" t="inlineStr" s="'.($rowIndex === 0 ? '3' : ($isYellowRow ? '4' : '1')).'"><is><t>'.$xml($value).'</t></is></c>';
        }
    }
    $sheetRows .= '<row r="'.$excelRow.'"'.($rowIndex === 0 ? ' ht="28" customHeight="1"' : '').'>'.$cells.'</row>';
}

$sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    .'<sheetViews><sheetView rightToLeft="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
    .'<cols><col min="1" max="1" width="10" customWidth="1"/><col min="2" max="3" width="38" customWidth="1"/><col min="4" max="4" width="32" customWidth="1"/><col min="5" max="6" width="17" customWidth="1"/><col min="7" max="7" width="12" customWidth="1"/><col min="8" max="15" width="22" customWidth="1"/></cols>'
    .'<sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:O'.count($rows).'"/></worksheet>';

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    .'<fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts>'
    .'<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1D4ED8"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFEB9C"/><bgColor indexed="64"/></patternFill></fill></fills>'
    .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
    .'<cellXfs count="6"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="right" vertical="center" wrapText="1"/></xf><xf numFmtId="2" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"><alignment horizontal="right" vertical="center" wrapText="1"/></xf><xf numFmtId="2" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"><alignment horizontal="center"/></xf></cellXfs>'
    .'</styleSheet>';

$downloads = getenv('USERPROFILE').'\\Downloads';
$path = $downloads.'\\طلاب-جامعة-الأزهر-مع-إعادة-الصفوف-الصفراء-'.date('Ymd-His').'.xlsx';
$zip = new ZipArchive();
if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('تعذر إنشاء ملف Excel.');
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="طلاب الأزهر 145" sheetId="1" r:id="rId1"/></sheets></workbook>');
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
$zip->addFromString('xl/styles.xml', $styles);
$zip->close();

echo json_encode(['file' => $path, 'selected' => $students->count(), 'yellow_students' => $yellowStudentIds->count(), 'yellow_already_present' => $yellowStudentIds->intersect($existingIds)->count(), 'yellow_added' => $yellowAdditions->count(), 'first_year' => $students->pluck(0)->where('academic_level', 'level_1')->count()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
