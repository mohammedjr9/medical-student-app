<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$eligibleLevels = ['level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'internship'];

$students = MedicalRegistration::query()
    ->whereIn('academic_level', $eligibleLevels)
    ->orderByRaw("CASE is_father_martyr WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByRaw("CASE has_sibling_student WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderBy('gpa')
    ->orderByRaw("CASE housing_type WHEN 'tent' THEN 0 WHEN 'shelter' THEN 1 ELSE 2 END")
    ->orderBy('id')
    ->limit(100)
    ->get();

$universities = [
    'IUG' => 'الجامعة الإسلامية بغزة',
    'AUG' => 'جامعة الأزهر بغزة',
    'ISRAA' => 'جامعة الإسراء',
    'UPAL' => 'جامعة فلسطين',
];

$levels = [
    'level_2' => 'السنة الثانية',
    'level_3' => 'السنة الثالثة',
    'level_4' => 'السنة الرابعة',
    'level_5' => 'السنة الخامسة',
    'level_6' => 'السنة السادسة',
    'internship' => 'سنة الامتياز',
];

$housingTypes = [
    'tent' => 'خيمة',
    'shelter' => 'مركز إيواء',
    'house' => 'منزل',
    'apartment' => 'شقة',
    'relatives' => 'منزل أقارب',
    'other' => 'أخرى',
];

$exportDirectory = base_path('exports');
if (! is_dir($exportDirectory) && ! mkdir($exportDirectory, 0775, true) && ! is_dir($exportDirectory)) {
    throw new RuntimeException('Unable to create the export directory.');
}

$exportPath = $exportDirectory.'/top-100-needy-students-'.date('Ymd-His').'.csv';
$file = fopen($exportPath, 'wb');
if ($file === false) {
    throw new RuntimeException('Unable to create the export file.');
}

fwrite($file, "\xEF\xBB\xBF");
fputcsv($file, [
    'الترتيب', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'الجامعة', 'المستوى', 'المعدل',
    'نوع السكن', 'ابن شهيد/متوفى', 'إعاقة', 'أخ/أخت بالجامعة', 'سبب الأولوية', 'الرقم المرجعي',
]);

foreach ($students as $index => $student) {
    $reasons = [];
    if ($student->housing_type === 'tent') {
        $reasons[] = 'السكن في خيمة';
    } elseif ($student->housing_type === 'shelter') {
        $reasons[] = 'السكن في مركز إيواء';
    }
    if ($student->is_father_martyr === 'yes') {
        $reasons[] = 'ابن شهيد/متوفى';
    }
    if ($student->has_disability === 'yes') {
        $reasons[] = 'ذو إعاقة';
    }
    if ($student->has_sibling_student === 'yes') {
        $reasons[] = 'له أخ/أخت بالجامعة';
    }

    fputcsv($file, [
        $index + 1,
        $student->full_name,
        $student->national_id,
        $student->mobile_number,
        $universities[$student->university_id] ?? $student->university_id,
        $levels[$student->academic_level] ?? $student->academic_level,
        $student->gpa,
        $housingTypes[$student->housing_type] ?? $student->housing_type,
        $student->is_father_martyr === 'yes' ? 'نعم' : 'لا',
        $student->has_disability === 'yes' ? 'نعم' : 'لا',
        $student->has_sibling_student === 'yes' ? 'نعم' : 'لا',
        implode('، ', $reasons),
        $student->reference_number,
    ]);
}

fclose($file);

$summary = [
    'selected' => $students->count(),
    'eligible_total' => MedicalRegistration::whereIn('academic_level', $eligibleLevels)->count(),
    'by_university' => $students->countBy('university_id')->all(),
    'by_level' => $students->countBy('academic_level')->all(),
    'by_housing' => $students->countBy('housing_type')->all(),
    'martyr_or_deceased' => $students->where('is_father_martyr', 'yes')->count(),
    'disability' => $students->where('has_disability', 'yes')->count(),
    'sibling_student' => $students->where('has_sibling_student', 'yes')->count(),
    'file' => $exportPath,
];

echo json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
