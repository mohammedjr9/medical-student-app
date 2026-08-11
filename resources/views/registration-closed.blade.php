<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتهاء مدة التسجيل - {{ $selectedUniversity['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/medical-registration.css') }}">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center p-4" style="font-family: Tajawal, sans-serif;">
    <main class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-8 sm:p-12 text-center shadow-xl">
        <div class="mb-7 flex items-center justify-center gap-4">
            <img src="{{ asset('images/hudayi-vakfi-logo.svg') }}?v=2" alt="شعار الوقف" class="h-24 w-24 object-contain">
            <img src="{{ asset($selectedUniversity['logo']) }}" alt="شعار {{ $selectedUniversity['name'] }}" class="h-24 w-24 object-contain">
        </div>

        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-3xl" aria-hidden="true">⌛</div>
        <p class="mb-2 text-sm font-bold text-blue-700">{{ $selectedUniversity['name'] }}</p>
        <h1 class="mb-4 text-2xl font-extrabold text-slate-900 sm:text-3xl">انتهت مدة التسجيل</h1>
        <p class="text-base leading-8 text-slate-600">
            نعتذر، لقد انتهت المدة المحددة لاستقبال طلبات التسجيل، ولم يعد النموذج متاحًا لإرسال طلبات جديدة.
        </p>
        <p class="mt-6 text-sm font-medium text-slate-500">شكرًا لاهتمامكم، مع تمنياتنا للجميع بالتوفيق.</p>
    </main>
</body>
</html>
