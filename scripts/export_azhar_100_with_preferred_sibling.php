<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$levels = ['level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'internship'];
$base = MedicalRegistration::query()
    ->where('university_id', 'AUG')
    ->whereIn('academic_level', $levels)
    ->where('is_father_martyr', 'yes')
    ->where('gpa', '>=', 80);

$preferred = (clone $base)
    ->where('has_sibling_student', 'yes')
    ->orderByDesc('gpa')
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderBy('id')
    ->limit(100)
    ->get();

$remaining = max(0, 100 - $preferred->count());
$additional = (clone $base)
    ->where('has_sibling_student', '!=', 'yes')
    ->orderByDesc('gpa')
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderBy('id')
    ->limit($remaining)
    ->get();

$students = $preferred->concat($additional)->values();
$levelNames = [
    'level_2' => 'السنة الثانية', 'level_3' => 'السنة الثالثة',
    'level_4' => 'السنة الرابعة', 'level_5' => 'السنة الخامسة',
    'level_6' => 'السنة السادسة', 'internship' => 'سنة الامتياز',
];
$housing = [
    'tent' => 'خيمة', 'shelter' => 'مركز إيواء', 'house' => 'منزل',
    'apartment' => 'شقة', 'relatives' => 'منزل أقارب', 'other' => 'أخرى',
];

$directory = base_path('exports');
if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
    throw new RuntimeException('Unable to create export directory.');
}
$path = $directory.'/azhar-100-priority-students-'.date('Ymd-His').'.csv';
$file = fopen($path, 'wb');
if ($file === false) throw new RuntimeException('Unable to create export file.');
fwrite($file, "\xEF\xBB\xBF");
fputcsv($file, [
    'الترتيب', 'الفئة', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'الجامعة', 'المستوى',
    'المعدل', 'نوع السكن', 'ابن شهيد/متوفى', 'إعاقة', 'أخ/أخت بالجامعة',
    'اسم الأخ/الأخت', 'جامعة الأخ/الأخت', 'الرقم المرجعي',
]);
foreach ($students as $index => $student) {
    fputcsv($file, [
        $index + 1,
        $student->has_sibling_student === 'yes' ? 'أولوية أولى' : 'اسم إضافي',
        $student->full_name, $student->national_id, $student->mobile_number,
        'جامعة الأزهر بغزة', $levelNames[$student->academic_level] ?? $student->academic_level,
        $student->gpa, $housing[$student->housing_type] ?? $student->housing_type, 'نعم',
        $student->has_disability === 'yes' ? 'نعم' : 'لا',
        $student->has_sibling_student === 'yes' ? 'نعم' : 'لا',
        $student->sibling_name, $student->sibling_university, $student->reference_number,
    ]);
}
fclose($file);

echo json_encode([
    'selected' => $students->count(),
    'preferred_with_sibling' => $preferred->count(),
    'new_additional_without_sibling' => $additional->count(),
    'additional_highest_gpa' => $additional->max('gpa'),
    'additional_lowest_gpa' => $additional->min('gpa'),
    'file' => $path,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
