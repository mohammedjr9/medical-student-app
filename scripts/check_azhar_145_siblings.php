<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$base = MedicalRegistration::where('university_id', 'AUG')->where('gpa', '>', 80)
    ->where('is_father_martyr', 'yes')->get();
$additional = MedicalRegistration::where('university_id', 'AUG')->where('gpa', '>', 80)
    ->where('is_father_martyr', '!=', 'yes')
    ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderByDesc('gpa')->orderByRaw("CASE has_sibling_student WHEN 'yes' THEN 0 ELSE 1 END")
    ->orderBy('id')->limit(22)->get();
$selected = $base->concat($additional);

echo json_encode([
    'with_sibling' => $selected->where('has_sibling_student', 'yes')->count(),
    'without_sibling' => $selected->where('has_sibling_student', '!=', 'yes')->count(),
    'additional_with_sibling' => $additional->where('has_sibling_student', 'yes')->count(),
], JSON_UNESCAPED_UNICODE).PHP_EOL;
