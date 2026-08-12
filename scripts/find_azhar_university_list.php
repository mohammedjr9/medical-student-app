<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$terms = ['ملك', 'ملاك', 'باسل'];
$records = MedicalRegistration::where('university_id', 'AUG')
    ->where(function ($query) use ($terms) {
        foreach ($terms as $term) $query->orWhere('full_name', 'like', "%{$term}%");
    })
    ->orderBy('full_name')
    ->get(['id', 'full_name', 'national_id', 'reference_number']);

echo json_encode($records, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
