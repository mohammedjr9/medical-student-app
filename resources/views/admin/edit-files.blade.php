<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل ملفات {{ $student->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 p-4 sm:p-8 font-sans text-slate-800">
<main class="mx-auto max-w-3xl">
    <div class="mb-5 flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-black">تعديل ملفات الطالب</h1><p class="mt-1 text-sm text-slate-500">{{ $student->full_name }} — {{ $student->reference_number }}</p></div>
        <a href="{{ route('admin.students.show', $student->id) }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold shadow-sm">العودة للتفاصيل</a>
    </div>
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif
    <form method="POST" action="{{ route('admin.students.files.update', $student->id) }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf @method('PUT')
        <p class="mb-6 rounded-xl bg-blue-50 p-4 text-sm text-blue-800">اختر فقط الملفات التي تريد استبدالها؛ الملفات الفارغة ستبقى كما هي. الحد الأقصى 5 ميجابايت للملف.</p>
        @php $fields = [
            ['personal_photo', 'الصورة الشخصية', 'image/*'],
            ['national_id_image', 'صورة الهوية', '.jpg,.jpeg,.png,.pdf'],
            ['enrollment_cert', 'شهادة القيد', '.jpg,.jpeg,.png,.pdf'],
            ['father_death_cert', 'شهادة وفاة/استشهاد الوالد', '.jpg,.jpeg,.png,.pdf'],
            ['medical_report', 'التقرير الطبي', '.jpg,.jpeg,.png,.pdf'],
            ['sibling_enrollment_cert', 'شهادة قيد الأخ/الأخت', '.jpg,.jpeg,.png,.pdf'],
        ]; @endphp
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($fields as [$name, $label, $accept])
                <label class="block rounded-xl border border-slate-200 p-4"><span class="mb-2 block text-sm font-bold">{{ $label }}</span><input type="file" name="{{ $name }}" accept="{{ $accept }}" class="block w-full text-sm file:ml-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-bold"></label>
            @endforeach
        </div>
        <button class="mt-6 w-full rounded-xl bg-blue-600 px-5 py-3 font-bold text-white hover:bg-blue-700">حفظ الملفات الجديدة</button>
    </form>
</main>
</body></html>
