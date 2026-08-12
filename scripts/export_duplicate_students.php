<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$duplicateIds = MedicalRegistration::query()
    ->select('national_id')->groupBy('national_id')->havingRaw('COUNT(*) > 1')->pluck('national_id');

$records = MedicalRegistration::query()
    ->whereIn('national_id', $duplicateIds)
    ->orderBy('national_id')->orderBy('id')
    ->get(['id', 'full_name', 'national_id', 'mobile_number', 'university_id', 'reference_number', 'created_at']);

$path = base_path('exports/duplicate-students-'.date('Ymd-His').'.csv');
$file = fopen($path, 'wb');
fwrite($file, "\xEF\xBB\xBF");
fputcsv($file, ['رقم الطلب', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'الجامعة', 'الرقم المرجعي', 'تاريخ التسجيل']);

foreach ($records as $record) {
    fputcsv($file, [
        $record->id, $record->full_name, $record->national_id, $record->mobile_number,
        $record->university_id, $record->reference_number, $record->created_at,
    ]);
}

fclose($file);
echo json_encode(['path' => $path, 'records' => $records->count(), 'groups' => $duplicateIds->count()], JSON_UNESCAPED_UNICODE).PHP_EOL;
