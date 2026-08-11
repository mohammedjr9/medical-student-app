<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$eligibleLevels = ['level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'internship'];
$query = MedicalRegistration::query()
    ->where('university_id', 'AUG')
    ->whereIn('academic_level', $eligibleLevels)
    ->where('is_father_martyr', 'yes')
    ->where('has_sibling_student', 'yes');

$eligibleTotal = (clone $query)->count();
$students = $query
    ->orderByDesc('gpa')
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByRaw("CASE housing_type WHEN 'tent' THEN 0 WHEN 'shelter' THEN 1 ELSE 2 END")
    ->orderBy('id')
    ->limit(100)
    ->get();

$levels = [
    'level_2' => 'السنة الثانية', 'level_3' => 'السنة الثالثة',
    'level_4' => 'السنة الرابعة', 'level_5' => 'السنة الخامسة',
    'level_6' => 'السنة السادسة', 'internship' => 'سنة الامتياز',
];
$housingTypes = [
    'tent' => 'خيمة', 'shelter' => 'مركز إيواء', 'house' => 'منزل',
    'apartment' => 'شقة', 'relatives' => 'منزل أقارب', 'other' => 'أخرى',
];

$exportDirectory = base_path('exports');
if (! is_dir($exportDirectory) && ! mkdir($exportDirectory, 0775, true) && ! is_dir($exportDirectory)) {
    throw new RuntimeException('Unable to create export directory.');
}
$exportPath = $exportDirectory.'/azhar-top-priority-students-'.date('Ymd-His').'.csv';
$file = fopen($exportPath, 'wb');
if ($file === false) {
    throw new RuntimeException('Unable to create export file.');
}
fwrite($file, "\xEF\xBB\xBF");
fputcsv($file, [
    'الترتيب', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'الجامعة', 'المستوى', 'المعدل',
    'نوع السكن', 'ابن شهيد/متوفى', 'إعاقة', 'أخ/أخت بالجامعة', 'اسم الأخ/الأخت',
    'جامعة الأخ/الأخت', 'الرقم المرجعي',
]);
foreach ($students as $index => $student) {
    fputcsv($file, [
        $index + 1, $student->full_name, $student->national_id, $student->mobile_number,
        'جامعة الأزهر بغزة', $levels[$student->academic_level] ?? $student->academic_level,
        $student->gpa, $housingTypes[$student->housing_type] ?? $student->housing_type,
        'نعم', $student->has_disability === 'yes' ? 'نعم' : 'لا', 'نعم',
        $student->sibling_name, $student->sibling_university, $student->reference_number,
    ]);
}
fclose($file);

echo json_encode([
    'eligible_total' => $eligibleTotal,
    'selected' => $students->count(),
    'highest_gpa' => $students->max('gpa'),
    'lowest_selected_gpa' => $students->min('gpa'),
    'file' => $exportPath,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
