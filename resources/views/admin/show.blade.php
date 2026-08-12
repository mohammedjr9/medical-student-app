<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملف الطالب | {{ $student->full_name }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        medical: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a',
                        }
                    },
                    fontFamily: { sans: ['Tajawal', 'Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen py-8 px-4 sm:px-8">

    <div class="max-w-4xl mx-auto space-y-6">

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <!-- Top Actions Bar -->
        <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs flex items-center gap-2">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                <span>العودة للوحة التحكم</span>
            </a>
            
            <div class="flex items-center gap-2">
            <a href="{{ route('admin.students.files.edit', $student->id) }}" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center gap-2 shadow-md">
                <i data-lucide="files" class="w-4 h-4"></i><span>تعديل الملفات</span>
            </a>
            <button onclick="window.print();" class="px-5 py-2.5 rounded-xl bg-medical-600 hover:bg-medical-700 text-white font-bold text-xs flex items-center gap-2 shadow-md">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>طباعة استمارة الطالب (PDF)</span>
            </button>
            </div>
        </div>

        <!-- Student Master Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-medical-50 text-medical-600 border border-medical-200 flex items-center justify-center font-bold text-xl shadow-sm">
                        <i data-lucide="user-check" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-medical-100 text-medical-800 mb-1">
                            طلب تسجيل معتمد
                        </span>
                        <h1 class="text-2xl font-black text-slate-900">{{ $student->full_name }}</h1>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">الرقم المرجعي للطلب: {{ $student->reference_number }}</p>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-400 block mb-1">تاريخ تقديم الطلب</span>
                    <span class="text-sm font-bold text-slate-700 font-mono" dir="ltr">{{ $student->created_at ? $student->created_at->format('Y-m-d H:i') : date('Y-m-d H:i') }}</span>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">

                <!-- Section 1 -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="text-xs font-bold text-medical-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4"></i> البيانات الشخصية
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-200/60"><span class="text-slate-500">رقم الهوية:</span> <span class="font-bold text-slate-900 font-mono">{{ $student->national_id }}</span></div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60"><span class="text-slate-500">رقم المحمول:</span> <span class="font-bold text-slate-900 font-mono" dir="ltr">{{ $student->mobile_number }}</span></div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60"><span class="text-slate-500">تاريخ الميلاد:</span> <span class="font-bold text-slate-900">{{ $student->date_of_birth }}</span></div>
                        <div class="flex justify-between py-1"><span class="text-slate-500">نوع السكن الحالي:</span> <span class="font-bold text-slate-900">{{ $housingTypes[$student->housing_type] ?? $student->housing_type }}</span></div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="text-xs font-bold text-medical-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i> البيانات الأكاديمية
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-200/60"><span class="text-slate-500">الجامعة المقيد بها:</span> <span class="font-bold text-slate-900">{{ $universities[$student->university_id] ?? $student->university_id }}</span></div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60"><span class="text-slate-500">المستوى الدراسي:</span> <span class="font-bold text-slate-900">{{ $academicLevels[$student->academic_level] ?? $student->academic_level }}</span></div>
                        <div class="flex justify-between py-1"><span class="text-slate-500">المعدل التراكمي:</span> <span class="font-bold text-emerald-700 text-sm" dir="ltr">{{ number_format($student->gpa, 2) }}%</span></div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="md:col-span-2 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h3 class="text-xs font-bold text-medical-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="heart-handshake" class="w-4 h-4"></i> الحالات والظروف الخاصة
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <span>والد الطالب شهيد أو أسير:</span>
                            <span class="font-bold {{ $student->is_father_martyr === 'yes' ? 'text-amber-700' : 'text-slate-600' }}">{{ $student->is_father_martyr === 'yes' ? 'نعم' : 'لا' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <span>إعاقة أو إصابة موثقة:</span>
                            <span class="font-bold {{ $student->has_disability === 'yes' ? 'text-rose-700' : 'text-slate-600' }}">{{ $student->has_disability === 'yes' ? 'نعم' : 'لا' }}</span>
                        </div>
                        @if($student->has_sibling_student === 'yes')
                            <div class="p-3 rounded-xl bg-white border border-purple-200 sm:col-span-3 grid sm:grid-cols-2 gap-2">
                                <span><span class="text-slate-500">اسم الأخ / الأخت:</span> <strong>{{ $student->sibling_name ?: 'غير مدخل' }}</strong></span>
                                <span><span class="text-slate-500">الجامعة:</span> <strong>{{ $student->sibling_university ?: 'غير مدخلة' }}</strong></span>
                            </div>
                        @endif
                        <div class="p-3 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <span>أخ/أخت يدرس بالجامعة حالياً:</span>
                            <span class="font-bold {{ $student->has_sibling_student === 'yes' ? 'text-purple-700' : 'text-slate-600' }}">{{ $student->has_sibling_student === 'yes' ? 'نعم' : 'لا' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 4 Documents -->
                <div class="md:col-span-2 p-5 rounded-2xl bg-medical-50/60 border border-medical-200 space-y-4">
                    <h3 class="text-xs font-bold text-medical-800 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="folder-check" class="w-4 h-4"></i> المستندات المرفقة (معاينة وتحميل)
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Personal Photo -->
                        <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-800">1. الصورة الشخصية</h4>
                                    <p class="text-[11px] text-slate-400">Personal Photo</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ asset($student->personal_photo_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->personal_photo_path) : '#') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-medical-50 text-medical-700 font-bold text-xs hover:bg-medical-600 hover:text-white transition-all flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>معاينة</span>
                                </a>
                                <a href="{{ asset($student->personal_photo_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->personal_photo_path) : '#') }}" download class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="تحميل">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        <!-- National ID -->
                        <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-800">2. صورة الهوية الوطنية</h4>
                                    <p class="text-[11px] text-slate-400">National ID Image</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ asset($student->national_id_image_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->national_id_image_path) : '#') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-medical-50 text-medical-700 font-bold text-xs hover:bg-medical-600 hover:text-white transition-all flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>معاينة PDF</span>
                                </a>
                                <a href="{{ asset($student->national_id_image_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->national_id_image_path) : '#') }}" download class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="تحميل PDF">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Enrollment Certificate -->
                        <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-center justify-between sm:col-span-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i data-lucide="file-check-2" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-800">3. شهادة قيد</h4>
                                    <p class="text-[11px] text-slate-400">University Enrollment Certificate</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ asset($student->enrollment_cert_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->enrollment_cert_path) : '#') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-medical-50 text-medical-700 font-bold text-xs hover:bg-medical-600 hover:text-white transition-all flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>معاينة الملف</span>
                                </a>
                                <a href="{{ asset($student->enrollment_cert_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->enrollment_cert_path) : '#') }}" download class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="تحميل الملف">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Death Cert if applicable -->
                        @if($student->is_father_martyr === 'yes')
                            <div class="p-4 rounded-xl bg-white border border-amber-200 flex items-center justify-between sm:col-span-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                                        <i data-lucide="file-badge-2" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-xs text-slate-800">4. إثبات استشهاد أو أسر الوالد</h4>
                                        <p class="text-[11px] text-slate-400">Father Death Certificate</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ asset($student->father_death_cert_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->father_death_cert_path) : '#') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 font-bold text-xs hover:bg-amber-600 hover:text-white transition-all flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>معاينة PDF</span>
                                    </a>
                                    <a href="{{ asset($student->father_death_cert_path ? 'storage/' . str_replace('medical_student_docs/', 'medical_student_docs/', $student->father_death_cert_path) : '#') }}" download class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="تحميل">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
