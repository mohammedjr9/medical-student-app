<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | إدارة طلبات تسجيل طلاب الطب</title>
    
    <!-- Fonts: Plus Jakarta Sans & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        medical: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb', // Medical Primary Blue
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">

    <!-- Top Admin Header -->
    <header class="sticky top-0 z-30 glass-header border-b border-slate-200/80 px-4 sm:px-8 py-4">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/hudayi-vakfi-logo.svg') }}" alt="Aziz Mahmud Hüdayi Vakfı" class="w-12 h-14 object-contain">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-900">لوحة تحكم سجلات طلاب الطب</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-medical-100 text-medical-800 border border-medical-200">
                            نظام الأدمن
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">إدارة ومراجعة طلبات الاعتماد الأكاديمي والمعاينة المباشرة للمستندات والـ PDF</p>
                </div>
            </div>

            <!-- Header Action Links -->
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="px-4 py-2.5 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 hover:bg-rose-100 font-bold text-xs transition-all flex items-center gap-2">
                        <i data-lucide="log-out" class="w-4 h-4"></i><span>تسجيل الخروج</span>
                    </button>
                </form>
                <a href="{{ route('medical-registration.create') }}" 
                   target="_blank"
                   class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-medical-600 font-bold text-xs shadow-sm transition-all duration-200 flex items-center gap-2">
                    <i data-lucide="external-link" class="w-4 h-4 text-medical-600"></i>
                    <span>فتح نموذج التسجيل العام</span>
                </a>
                
                <button onclick="window.location.reload();" 
                        class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-all"
                        title="تحديث البيانات">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>

        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-8 py-8">

        <!-- Flash Success Message -->
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-800">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        <!-- Quick Stats Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            
            <!-- Card 1: Total -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">إجمالي المسجلين</p>
                    <p class="text-2xl font-black text-slate-900">{{ $totalStudents }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Card 2: High GPA -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">المتفوقين (+85%)</p>
                    <p class="text-2xl font-black text-emerald-600">{{ $highGpaCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Card 3: Martyrs Father -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">أبناء الشهداء/المتوفين</p>
                    <p class="text-2xl font-black text-amber-600">{{ $fatherMartyrsCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="file-badge-2" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Card 4: Disabilities -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">حالات إعاقة / إصابة</p>
                    <p class="text-2xl font-black text-rose-600">{{ $disabilitiesCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Card 5: Siblings -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">إخوة يدرسون بالجامعة</p>
                    <p class="text-2xl font-black text-purple-600">{{ $siblingsCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                </div>
            </div>

        </div>

        <!-- University Quick Filters -->
        <div class="mb-8">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">الطلبات حسب الجامعة</h2>
                    <p class="text-xs text-slate-500 mt-0.5">اضغط على الجامعة لعرض طلباتها فقط</p>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                   class="text-xs font-bold px-3 py-2 rounded-xl border transition-colors {{ request()->hasAny(['university', 'search', 'academic_level', 'special_condition']) ? 'border-medical-200 bg-medical-50 text-medical-700 hover:bg-medical-100' : 'border-slate-200 bg-white text-slate-500' }}">
                    عرض جميع الطلبات
                </a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($universityLogos as $key => $logo)
                    <a href="{{ route('admin.dashboard', array_merge(request()->except('page', 'university'), ['university' => $key])) }}"
                       class="group bg-white p-4 rounded-2xl border shadow-sm flex items-center gap-3 transition-all hover:-translate-y-0.5 hover:shadow-md {{ request('university') === $key ? 'border-medical-500 ring-2 ring-medical-100' : 'border-slate-200/80 hover:border-medical-300' }}">
                        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 p-1.5 shrink-0">
                            <img src="{{ asset($logo) }}" alt="شعار {{ $universities[$key] }}" class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-700 truncate">{{ $universities[$key] }}</p>
                            <p class="text-xl font-black {{ request('university') === $key ? 'text-medical-700' : 'text-slate-900' }}">{{ $universityCounts[$key] ?? 0 }}</p>
                            <p class="text-[10px] text-slate-400">طلب مسجل</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search and Filter Bar Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm mb-8">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">البحث بالاسم أو الهوية أو الجوال أو الرقم المرجعي</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="ابحث بالاسم، الهوية، الجوال، المرجعي..." 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-medical-600 focus:bg-white transition-all">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3 pointer-events-none"></i>
                    </div>
                </div>

                <!-- University Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">تصفية حسب الجامعة</label>
                    <select name="university" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-medical-600">
                        <option value="">جميع الجامعات</option>
                        @foreach($universities as $key => $label)
                            <option value="{{ $key }}" {{ request('university') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Academic Level Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">المستوى الأكاديمي</label>
                    <select name="academic_level" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-medical-600">
                        <option value="">جميع المستويات</option>
                        @foreach($academicLevels as $key => $label)
                            <option value="{{ $key }}" {{ request('academic_level') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Special Conditions Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">الحالات الخاصة</label>
                    <select name="special_condition" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-medical-600">
                        <option value="">جميع الحالات</option>
                        <option value="father_martyr" {{ request('special_condition') == 'father_martyr' ? 'selected' : '' }}>والد شهيد أو أسير</option>
                        <option value="disability" {{ request('special_condition') == 'disability' ? 'selected' : '' }}>إعاقة / إصابة</option>
                        <option value="sibling" {{ request('special_condition') == 'sibling' ? 'selected' : '' }}>أخ طالب بالجامعة</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold text-xs transition-colors flex items-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        مسح الفلاتر
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-medical-600 text-white hover:bg-medical-700 font-bold text-xs shadow-sm transition-colors flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        تطبيق البحث
                    </button>
                </div>

            </form>
        </div>

        <!-- Registered Students Data Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="list" class="w-5 h-5 text-medical-600"></i>
                    <h2 class="text-base font-bold text-slate-900">جدول الطلاب المسجلين في النظام</h2>
                </div>
                <span class="text-xs font-semibold text-slate-500">عدد النتائج: {{ $students->total() }} طالب</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">رقم مرجعي</th>
                            <th class="py-3.5 px-4">اسم الطالب / الهوية</th>
                            <th class="py-3.5 px-4">رقم الجوال</th>
                            <th class="py-3.5 px-4">الجامعة / المستوى</th>
                            <th class="py-3.5 px-4 text-center">المعدل</th>
                            <th class="py-3.5 px-4 text-center">نوع السكن</th>
                            <th class="py-3.5 px-4 text-center">الحالات الخاصة</th>
                            <th class="py-3.5 px-4 text-center">معاينة المستندات والـ PDF</th>
                            <th class="py-3.5 px-4 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- Reference Number -->
                                <td class="py-4 px-4 font-mono font-bold text-medical-700">
                                    {{ $student->reference_number }}
                                </td>

                                <!-- Student Name & ID -->
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900 text-sm mb-0.5">{{ $student->full_name }}</div>
                                    <div class="text-slate-400 font-mono text-[11px]">هوية: {{ $student->national_id }}</div>
                                </td>

                                <!-- Mobile Number & DoB -->
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-800" dir="ltr" style="text-align: right;">{{ $student->mobile_number }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $student->date_of_birth }}</div>
                                </td>

                                <!-- University & Academic Level -->
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-800">{{ $universities[$student->university_id] ?? $student->university_id }}</div>
                                    <div class="text-slate-500 text-[11px]">{{ $academicLevels[$student->academic_level] ?? $student->academic_level }}</div>
                                </td>

                                <!-- GPA Badge with correct LTR formatting -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg font-bold text-xs 
                                        {{ $student->gpa >= 85 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($student->gpa >= 75 ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-slate-100 text-slate-800') }}" dir="ltr">
                                        {{ number_format($student->gpa, 2) }}%
                                    </span>
                                </td>

                                <!-- Housing Type Badge -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $housingTypes[$student->housing_type] ?? $student->housing_type }}
                                    </span>
                                </td>

                                <!-- Special Conditions Badges -->
                                <td class="py-4 px-4 text-center">
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        @if($student->is_father_martyr === 'yes')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                متوفى الوالد
                                            </span>
                                        @endif

                                        @if($student->has_disability === 'yes')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                إعاقة/إصابة
                                            </span>
                                        @endif

                                        @if($student->has_sibling_student === 'yes')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                أخ طالب
                                            </span>
                                        @endif

                                        @if($student->is_father_martyr !== 'yes' && $student->has_disability !== 'yes' && $student->has_sibling_student !== 'yes')
                                            <span class="text-slate-400 text-[11px]">-</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Attachments Quick View Buttons -->
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Photo -->
                                        @if($student->personal_photo_path)
                                            <button onclick="previewFile('الصورة الشخصية - {{ $student->full_name }}', '{{ asset('storage/' . $student->personal_photo_path) }}')" 
                                                    class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center border border-blue-200 transition-all" title="معاينة الصورة الشخصية">
                                                <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif

                                        <!-- National ID -->
                                        @if($student->national_id_image_path)
                                            <button onclick="previewFile('صورة الهوية الوطنية - {{ $student->full_name }}', '{{ asset('storage/' . $student->national_id_image_path) }}')" 
                                                    class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center border border-emerald-200 transition-all" title="معاينة الهوية الوطنية (PDF)">
                                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif

                                        <!-- Enrollment Cert (شهادة قيد) -->
                                        @if($student->enrollment_cert_path)
                                            <button onclick="previewFile('شهادة قيد - {{ $student->full_name }}', '{{ asset('storage/' . $student->enrollment_cert_path) }}')" 
                                                    class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center border border-indigo-200 transition-all" title="معاينة شهادة قيد (PDF)">
                                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Open Details Modal Button -->
                                        <button onclick="openStudentModal({{ json_encode($student) }})" 
                                                class="px-3 py-1.5 rounded-lg bg-medical-50 text-medical-700 hover:bg-medical-600 hover:text-white font-bold text-xs transition-all flex items-center gap-1 shadow-sm">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>تفاصيل الطلب</span>
                                        </button>

                                        <!-- Full Page View / Print -->
                                        <a href="{{ route('admin.students.show', $student->id) }}" 
                                           target="_blank"
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-medical-600 hover:bg-medical-50 transition-colors" 
                                           title="فتح صفحة الطباعة/الملف">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من حذف طلب الطالب {{ $student->full_name }}؟');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="حذف الطالب">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                    <p class="text-sm font-semibold">لا توجد طلبات مسجلة تطابق محددات البحث الحالية.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $students->links() }}
            </div>

        </div>

    </main>

    <!-- STUDENT FULL DETAILS MODAL POPUP -->
    <div id="studentDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 flex items-center justify-center animate-fade-in">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
            
            <!-- Modal Close Button -->
            <button onclick="closeStudentModal()" class="absolute top-5 left-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-all">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Modal Header -->
            <div class="flex items-center gap-4 pb-5 border-b border-slate-100 mb-6">
                <div class="w-12 h-12 rounded-xl bg-medical-50 border border-medical-200 text-medical-600 flex items-center justify-center font-bold text-lg">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900" id="modalStudentName">تفاصيل ملف الطالب</h3>
                    <p class="text-xs text-slate-500 font-mono" id="modalRefNumber">MED-2026-XXXX</p>
                </div>
            </div>

            <!-- Modal Content Grid -->
            <div class="space-y-6 max-h-[70vh] overflow-y-auto pr-1">

                <!-- Section 1: Personal Data -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                    <h4 class="text-xs font-bold text-medical-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="user" class="w-4 h-4"></i> البيانات الشخصية
                    </h4>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div><span class="text-slate-500">رقم الهوية:</span> <strong id="modalNationalId" class="text-slate-800 font-mono"></strong></div>
                        <div><span class="text-slate-500">رقم الجوال:</span> <strong id="modalMobile" class="text-slate-800 font-mono" dir="ltr"></strong></div>
                        <div><span class="text-slate-500">تاريخ الميلاد:</span> <strong id="modalDob" class="text-slate-800"></strong></div>
                        <div><span class="text-slate-500">نوع السكن الحالي:</span> <strong id="modalHousing" class="text-slate-800"></strong></div>
                    </div>
                </div>

                <!-- Section 2: Academic Data -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                    <h4 class="text-xs font-bold text-medical-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i> البيانات الأكاديمية
                    </h4>
                    <div class="grid grid-cols-3 gap-4 text-xs">
                        <div><span class="text-slate-500">الجامعة:</span> <strong id="modalUniversity" class="text-slate-800"></strong></div>
                        <div><span class="text-slate-500">المستوى الدراسي:</span> <strong id="modalLevel" class="text-slate-800"></strong></div>
                        <div><span class="text-slate-500">المعدل التراكمي:</span> <strong id="modalGpa" class="text-emerald-700 font-bold" dir="ltr"></strong></div>
                    </div>
                </div>

                <!-- Section 3: Special Conditions Status -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                    <h4 class="text-xs font-bold text-medical-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="heart-handshake" class="w-4 h-4"></i> الظروف والحالات الخاصة
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-100">
                            <span>والد الطالب شهيد أو أسير:</span>
                            <strong id="modalMartyr" class="font-bold"></strong>
                        </div>
                        <div id="modalMartyrFile"></div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-100">
                            <span>إعاقة أو إصابة موثقة:</span>
                            <strong id="modalDisability" class="font-bold"></strong>
                        </div>
                        <div id="modalDisabilityFile"></div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-100">
                            <span>أخ/أخت يدرس بالجامعة حالياً:</span>
                            <strong id="modalSibling" class="font-bold"></strong>
                        </div>
                        <div id="modalSiblingDetails" class="hidden p-2 rounded-lg bg-purple-50 border border-purple-100 text-[11px] text-purple-800"></div>
                        <div id="modalSiblingFile"></div>
                    </div>
                </div>

                <!-- Section 4: Document Links & Downloads -->
                <div class="p-4 rounded-xl bg-medical-50/50 border border-medical-200/60">
                    <h4 class="text-xs font-bold text-medical-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="folder-check" class="w-4 h-4"></i> المستندات والملفات المرفقة (معاينة وتحميل)
                    </h4>
                    <div class="grid grid-cols-1 gap-3 text-xs" id="modalFilesContainer">
                        <!-- Dynamic file links populated by JS -->
                    </div>
                </div>

            </div>

            <!-- Modal Footer Actions -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between">
                <a id="modalPrintBtn" href="#" target="_blank" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs flex items-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>فتح الاستمارة للطباعة</span>
                </a>

                <button onclick="closeStudentModal()" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-all">
                    إغلاق المعاينة
                </button>
            </div>

        </div>
    </div>

    <!-- DOCUMENT PREVIEW MODAL (PDF & Image Viewer) -->
    <div id="docPreviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md p-4 sm:p-6 flex items-center justify-center animate-fade-in">
        <div class="bg-white rounded-2xl max-w-4xl w-full h-[85vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden relative">
            
            <!-- Viewer Top Bar -->
            <div class="p-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-medical-600 flex items-center justify-center text-white">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" id="docPreviewTitle">معاينة المستند</h3>
                        <p class="text-[11px] text-slate-400">عارض المستندات المباشر (PDF / الصور)</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a id="docPreviewDownloadBtn" href="#" download class="px-3.5 py-1.5 rounded-xl bg-medical-600 hover:bg-medical-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>تحميل المستند</span>
                    </a>
                    
                    <button onclick="closeDocPreviewModal()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Viewer Main Body -->
            <div class="flex-1 bg-slate-100 relative overflow-hidden flex items-center justify-center p-2">
                <!-- PDF iframe -->
                <iframe id="docPreviewIframe" class="w-full h-full rounded-xl border border-slate-200 hidden bg-white"></iframe>
                
                <!-- Image Tag -->
                <img id="docPreviewImg" src="" alt="Preview" class="max-w-full max-h-full object-contain rounded-xl hidden shadow-lg">
            </div>

        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        const universitiesDict = @json($universities);
        const levelsDict = @json($academicLevels);
        const housingDict = @json($housingTypes);

        function openStudentModal(student) {
            document.getElementById('modalStudentName').textContent = student.full_name;
            document.getElementById('modalRefNumber').textContent = 'الرقم المرجعي: ' + student.reference_number;
            document.getElementById('modalNationalId').textContent = student.national_id;
            document.getElementById('modalMobile').textContent = student.mobile_number;
            document.getElementById('modalDob').textContent = student.date_of_birth;
            document.getElementById('modalHousing').textContent = housingDict[student.housing_type] || student.housing_type;
            
            document.getElementById('modalUniversity').textContent = universitiesDict[student.university_id] || student.university_id;
            document.getElementById('modalLevel').textContent = levelsDict[student.academic_level] || student.academic_level;
            
            // Fix GPA LTR formatting
            document.getElementById('modalGpa').textContent = student.gpa + '%';

            document.getElementById('modalMartyr').textContent = student.is_father_martyr === 'yes' ? 'نعم (إثبات الاستشهاد أو الأسر مرفق)' : 'لا';
            document.getElementById('modalDisability').textContent = student.has_disability === 'yes' ? 'نعم (تقرير طبي مرفق)' : 'لا';
            document.getElementById('modalSibling').textContent = student.has_sibling_student === 'yes' ? 'نعم (شهادة قيد مرفقة)' : 'لا';

            document.getElementById('modalPrintBtn').href = '/admin/students/' + student.id;

            // Build Document Links
            const filesContainer = document.getElementById('modalFilesContainer');
            filesContainer.innerHTML = '';
            document.getElementById('modalMartyrFile').innerHTML = '';
            document.getElementById('modalDisabilityFile').innerHTML = '';
            document.getElementById('modalSiblingFile').innerHTML = '';

            const siblingDetails = document.getElementById('modalSiblingDetails');
            siblingDetails.classList.add('hidden');
            siblingDetails.textContent = '';
            if (student.has_sibling_student === 'yes') {
                siblingDetails.textContent = `الاسم: ${student.sibling_name || 'غير مدخل'} — الجامعة: ${student.sibling_university || 'غير مدخلة'}`;
                siblingDetails.classList.remove('hidden');
            }

            const files = [
                { title: 'الصورة الشخصية (Personal Photo)', path: student.personal_photo_path, icon: 'image' },
                { title: 'صورة الهوية الوطنية (National ID Image)', path: student.national_id_image_path, icon: 'credit-card' },
                { title: 'شهادة قيد (University Enrollment Certificate)', path: student.enrollment_cert_path, icon: 'file-check-2' },
            ];

            if (student.is_father_martyr === 'yes' && student.father_death_cert_path) {
                files.push({ title: 'شهادة وفاة / استشهاد الوالد', path: student.father_death_cert_path, icon: 'file-badge-2', container: 'modalMartyrFile' });
            }
            if (student.has_disability === 'yes' && student.medical_report_path) {
                files.push({ title: 'التقرير الطبي / الإعاقة', path: student.medical_report_path, icon: 'activity', container: 'modalDisabilityFile' });
            }
            if (student.has_sibling_student === 'yes' && student.sibling_enrollment_cert_path) {
                files.push({ title: 'شهادة قيد الأخ / الأخت', path: student.sibling_enrollment_cert_path, icon: 'users', container: 'modalSiblingFile' });
            }

            files.forEach(file => {
                const fullUrl = file.path ? '/storage/' + file.path : '#';
                
                const item = document.createElement('div');
                item.className = 'p-3.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between shadow-sm';
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-medical-50 text-medical-600 flex items-center justify-center shrink-0">
                            <i data-lucide="${file.icon}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-xs">${file.title}</p>
                            <p class="text-[10px] text-slate-400">جاهز للمعاينة والتحميل</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="previewFile('${file.title} - ${student.full_name}', '${fullUrl}')" class="px-3 py-1.5 rounded-lg bg-medical-50 text-medical-700 hover:bg-medical-600 hover:text-white font-bold text-xs transition-all flex items-center gap-1">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>معاينة الـ PDF</span>
                        </button>
                        <a href="${fullUrl}" download class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" title="تحميل المستند">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </a>
                    </div>
                `;
                const targetContainer = file.container
                    ? document.getElementById(file.container)
                    : filesContainer;
                targetContainer.appendChild(item);
            });

            lucide.createIcons();
            document.getElementById('studentDetailsModal').classList.remove('hidden');
        }

        function closeStudentModal() {
            document.getElementById('studentDetailsModal').classList.add('hidden');
        }

        // Live Document Viewer Modal
        function previewFile(title, fileUrl) {
            document.getElementById('docPreviewTitle').textContent = title;
            document.getElementById('docPreviewDownloadBtn').href = fileUrl;

            const iframe = document.getElementById('docPreviewIframe');
            const img = document.getElementById('docPreviewImg');

            if (fileUrl.endsWith('.jpg') || fileUrl.endsWith('.png') || fileUrl.endsWith('.jpeg') || fileUrl.endsWith('.svg')) {
                iframe.classList.add('hidden');
                iframe.src = '';
                img.src = fileUrl;
                img.classList.remove('hidden');
            } else {
                img.classList.add('hidden');
                img.src = '';
                iframe.src = fileUrl;
                iframe.classList.remove('hidden');
            }

            document.getElementById('docPreviewModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeDocPreviewModal() {
            document.getElementById('docPreviewModal').classList.add('hidden');
            document.getElementById('docPreviewIframe').src = '';
        }
    </script>
</body>
</html>
