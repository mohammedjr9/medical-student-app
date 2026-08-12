<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

config(['database.connections.mysql.options' => [PDO::ATTR_TIMEOUT => 10]]);
app('db')->purge('mysql');

$grouped = static function (string $column) {
    return MedicalRegistration::query()
        ->select($column)
        ->selectRaw('COUNT(*) as duplicate_count')
        ->groupBy($column)
        ->havingRaw('COUNT(*) > 1')
        ->orderByDesc('duplicate_count')
        ->get();
};

$duplicateIds = $grouped('national_id')->pluck('national_id');

$result = [
    'total_records' => MedicalRegistration::count(),
    'duplicates_by_national_id' => $grouped('national_id'),
    'duplicate_record_details' => MedicalRegistration::query()
        ->whereIn('national_id', $duplicateIds)
        ->orderBy('national_id')
        ->orderBy('id')
        ->get(['id', 'full_name', 'national_id', 'mobile_number', 'university_id', 'reference_number', 'created_at']),
    'duplicates_by_mobile_number' => $grouped('mobile_number'),
    'duplicates_by_full_name' => $grouped('full_name'),
];

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
