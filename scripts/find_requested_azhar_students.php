<?php

use App\Models\MedicalRegistration;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$names = [
    'سجود طلال جابر عبد العال',
    'فرح اسامة محمد الاخرس',
    'نعيم يونس نعيم قنيطة',
    'شامة سيد خضر عاشور البطش',
    'خالد انور خالد حرارة',
    'احمد جميل يوسف الحرازين',
    'رواء هاني محمد ابو حجاج',
    'تسنيم عبد الفتاح حجاج',
    'سعاد أسامة عيادة مرزوق',
    'أمل محسن الكيلاني',
    'اسماء عبدالله سكر',
    'أنور عبدالله محمد سكر',
    'أسامة فضل أسامة الحرازين',
];

foreach ($names as $name) {
    $tokens = array_values(array_filter(preg_split('/\s+/u', $name)));
    $query = MedicalRegistration::query();
    foreach ($tokens as $token) $query->where('full_name', 'like', "%{$token}%");
    $matches = $query->get([
        'id', 'full_name', 'national_id', 'university_id', 'academic_level', 'gpa', 'reference_number',
    ]);
    echo json_encode(['requested' => $name, 'matches' => $matches], JSON_UNESCAPED_UNICODE).PHP_EOL;
}
