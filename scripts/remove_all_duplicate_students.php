<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$duplicateIds = MedicalRegistration::query()
    ->select('national_id')
    ->groupBy('national_id')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('national_id');

$deleteIds = collect();
$kept = collect();

foreach ($duplicateIds as $nationalId) {
    $ids = MedicalRegistration::query()
        ->where('national_id', $nationalId)
        ->orderBy('created_at')
        ->orderBy('id')
        ->pluck('id');

    $kept->push($ids->first());
    $deleteIds->push(...$ids->slice(1));
}

$records = MedicalRegistration::query()
    ->whereIn('id', $deleteIds)
    ->orderBy('national_id')
    ->orderBy('id')
    ->get();

$backupPath = base_path('exports/deleted-all-duplicates-'.date('Ymd-His').'.json');
$backup = [
    'created_at' => now()->toIso8601String(),
    'strategy' => 'Kept the earliest record per national_id; deleted later records.',
    'kept_ids' => $kept->values(),
    'deleted_count' => $records->count(),
    'deleted_records' => $records->toArray(),
];

file_put_contents(
    $backupPath,
    json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
    LOCK_EX
);

DB::transaction(function () use ($deleteIds): void {
    MedicalRegistration::query()->whereIn('id', $deleteIds)->delete();
});

$remainingDuplicateGroups = MedicalRegistration::query()
    ->select('national_id')
    ->groupBy('national_id')
    ->havingRaw('COUNT(*) > 1')
    ->count();

echo json_encode([
    'backup_path' => $backupPath,
    'deleted_count' => $records->count(),
    'remaining_records' => MedicalRegistration::count(),
    'remaining_duplicate_groups' => $remainingDuplicateGroups,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
