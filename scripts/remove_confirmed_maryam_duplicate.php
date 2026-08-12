<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

config(['database.connections.mysql.options' => [PDO::ATTR_TIMEOUT => 10]]);
app('db')->purge('mysql');

$nationalId = '411155955';
$keepId = 2082;
$deleteId = 2622;

$records = MedicalRegistration::query()
    ->where('national_id', $nationalId)
    ->orderBy('id')
    ->get();

if ($records->pluck('id')->all() !== [$keepId, $deleteId]) {
    fwrite(STDERR, "Safety check failed; no record was deleted.\n");
    exit(1);
}

$duplicate = $records->firstWhere('id', $deleteId);
$backupPath = __DIR__.'/../exports/deleted-duplicate-'.$deleteId.'.json';
file_put_contents(
    $backupPath,
    json_encode($duplicate->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL
);

DB::transaction(static function () use ($deleteId): void {
    $deleted = MedicalRegistration::query()->whereKey($deleteId)->delete();

    if ($deleted !== 1) {
        throw new RuntimeException('Expected exactly one record to be deleted.');
    }
});

$remaining = MedicalRegistration::query()
    ->where('national_id', $nationalId)
    ->get(['id', 'full_name', 'national_id', 'reference_number']);

echo json_encode([
    'deleted_id' => $deleteId,
    'backup' => $backupPath,
    'remaining' => $remaining,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
