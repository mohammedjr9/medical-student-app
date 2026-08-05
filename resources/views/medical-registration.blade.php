<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نموذج تسجيل طلبة الطب - {{ $selectedUniversity['name'] }}</title>
    
    <!-- Fonts: Plus Jakarta Sans & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Tailwind Configuration -->
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
                            600: '#2563eb', // Primary Medical Blue
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'Plus Jakarta Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.01)',
                        'card-hover': '0 20px 30px -10px rgba(37, 99, 235, 0.08), 0 10px 15px -5px rgba(0, 0, 0, 0.03)',
                    }
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        
        .form-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .form-card:hover {
            border-color: rgba(37, 99, 235, 0.25);
        }

        .conditional-box {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transform: translateY(-8px);
            transition: max-height 0.4s ease, opacity 0.3s ease, transform 0.3s ease, margin 0.3s ease;
        }

        .conditional-box.active {
            max-height: 500px;
            opacity: 1;
            transform: translateY(0);
            margin-top: 1.25rem;
        }

        .dropzone-active {
            border-color: #2563eb !important;
            background-color: #eff6ff !important;
            transform: scale(1.005);
        }

        .radio-tile {
            transition: all 0.2s ease;
        }

        input[type="radio"]:checked + .radio-tile {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #1e40af;
            box-shadow: 0 0 0 2px #2563eb;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen py-8 px-4 sm:px-6 lg:px-8">

    {{-- Fallback Constants mapped from Controller or default DB lists --}}
    @php
        $universities = $universities ?? [
            'IUG' => 'الجامعة الإسلامية بغزة',
            'AUG' => 'جامعة الأزهر بغزة',
            'ISRAA' => 'جامعة الإسراء',
            'UPAL' => 'جامعة فلسطين',
            'OTHER' => 'جامعة أخرى'
        ];

        $academicLevels = $academicLevels ?? [
            'level_1' => 'السنة الأولى',
            'level_2' => 'السنة الثانية',
            'level_3' => 'السنة الثالثة',
            'level_4' => 'السنة الرابعة',
            'level_5' => 'السنة الخامسة',
            'level_6' => 'السنة السادسة',
            'internship' => 'سنة الامتياز'
        ];

        $housingTypes = $housingTypes ?? [
            'house' => 'منزل',
            'tent' => 'خيمة',
            'apartment' => 'شقة',
            'relatives' => 'منزل أقارب',
            'shelter' => 'مركز إيواء',
            'other' => 'أخرى'
        ];

        $governorates = $governorates ?? [
            'north_gaza' => 'شمال غزة',
            'gaza' => 'غزة',
            'deir_al_balah' => 'الوسطى',
            'khan_yunis' => 'خان يونس',
            'rafah' => 'رفح',
        ];
    @endphp

    <!-- Container capped at ~850px -->
    <div class="max-w-[850px] mx-auto">
        
        <!-- Header Banner Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-slate-200/80 mb-8 relative overflow-hidden">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-medical-100 rounded-full blur-3xl opacity-60"></div>
            
            <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-right">
                <div class="shrink-0 flex items-center justify-center gap-3">
                    <div class="bg-white rounded-2xl border border-teal-100 p-2 shadow-sm">
                        <img src="{{ asset('images/hudayi-vakfi-logo.svg') }}?v=2" alt="Aziz Mahmud Hüdayi Vakfı" class="w-24 h-28 sm:w-28 sm:h-32 object-contain">
                    </div>
                    <div class="bg-white rounded-2xl border border-medical-100 p-2 shadow-sm">
                        <img src="{{ asset($selectedUniversity['logo']) }}" alt="شعار {{ $selectedUniversity['name'] }}" class="w-24 h-28 sm:w-28 sm:h-32 object-contain">
                    </div>
                </div>
                <div>
                    <p class="text-sm font-extrabold text-medical-700 mb-1">{{ $selectedUniversity['name'] }}</p>
                    <p dir="ltr" class="text-sm font-extrabold tracking-wide text-teal-700 mb-2">aziz mahmut hüdai vakfı</p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-medical-50 text-medical-700 border border-medical-200/70 mb-2">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        بوابة تسجيل طلاب الكليات الطبية
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">التسجيل في مشروع مساعدات طلبة الطب في الجامعات الفلسطينية</h1>
                    <p class="text-teal-700 text-sm sm:text-base mt-2 font-bold">بتمويل من مؤسسة محمود عزيز هدائي</p>
                    <p class="text-slate-500 text-sm mt-1 font-medium">تنفيذ جمعية جود الخيرية</p>
                </div>
            </div>

            <!-- Form Completion Progress Indicator -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs font-medium text-slate-600 mb-2">
                    <span class="flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-medical-600"></i>
                        تقدم حالة الطلب
                    </span>
                    <span id="progressPercent" class="font-bold text-medical-600">0%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div id="progressBar" class="bg-gradient-to-r from-medical-500 to-medical-600 h-2 rounded-full transition-all duration-500 w-0"></div>
                </div>
            </div>
        </div>

        <!-- Session Status Flash Alert -->
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
                    توجد بعض الأخطاء في إدخال البيانات، يرجى مراجعة الأخطاء أدناه:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 pr-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MAIN FORM -->
        <form id="medicalRegistrationForm" action="{{ route('medical-registration.store') }}" method="POST" enctype="multipart/form-data" novalidate class="space-y-8">
            @csrf

            <!-- ================================================================= -->
            <!-- SECTION 1: Personal Information -->
            <!-- ================================================================= -->
            <div class="form-card bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-slate-200/80">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-medical-50 flex items-center justify-center text-medical-600 border border-medical-100">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">القسم الأول: البيانات الشخصية</h2>
                            <p class="text-xs text-slate-500">معلومات الهوية والاتصال الرئيسية الخاصة بالمتقدم</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">1 من 5</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    
                    <!-- 1. Full Name (Arabic - Four Part Name) -->
                    <div class="sm:col-span-2">
                        <label for="full_name" class="block text-xs font-bold text-slate-700 mb-2">
                            الاسم الرباعي (باللغة العربية) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name') }}"
                                   placeholder="أدخل الاسم الأرباعي كما هو مدون في الهوية"
                                   required
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200">
                            <i data-lucide="signature" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>يُرجى إدخال الاسم الرباعي الصحيح باللغة العربية.</span>
                        </p>
                        @error('full_name')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 2. National ID Number -->
                    <div>
                        <label for="national_id" class="block text-xs font-bold text-slate-700 mb-2">
                            رقم الهوية الوطنية <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="national_id" 
                                   name="national_id" 
                                   value="{{ old('national_id') }}"
                                   placeholder="أدخل 9 أرقام بدون مسافات"
                                   maxlength="9"
                                   pattern="[0-9]*"
                                   inputmode="numeric"
                                   required
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200">
                            <i data-lucide="credit-card" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>رقم الهوية مطلوب ومكون من 9 أرقام فقط.</span>
                        </p>
                        @error('national_id')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 3. Mobile Number -->
                    <div>
                        <label for="mobile_number" class="block text-xs font-bold text-slate-700 mb-2">
                            رقم الهاتف المحمول <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="tel" 
                                   id="mobile_number" 
                                   name="mobile_number" 
                                   value="{{ old('mobile_number') }}"
                                   placeholder="059XXXXXXX أو 056XXXXXXX"
                                   required
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200" dir="ltr" style="text-align: right;">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>رقم الهاتف المحمول مطلوب.</span>
                        </p>
                        @error('mobile_number')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 4. Date of Birth -->
                    <div class="sm:col-span-2">
                        <label for="date_of_birth" class="block text-xs font-bold text-slate-700 mb-2">
                            تاريخ الميلاد <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth') }}"
                                   required
                                   dir="ltr"
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200">
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>تاريخ الميلاد حقل إجباري.</span>
                        </p>
                        @error('date_of_birth')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- ================================================================= -->
            <!-- SECTION 2: Academic Information -->
            <!-- ================================================================= -->
            <div class="form-card bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-slate-200/80">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-medical-50 flex items-center justify-center text-medical-600 border border-medical-100">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">القسم الثاني: البيانات الأكاديمية</h2>
                            <p class="text-xs text-slate-500">بيانات الجامعة والمستوى الدراسي والمعدل التراكمي</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">2 من 5</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <!-- 5. University (fixed by page) -->
                    <div>
                        <label for="university_id" class="block text-xs font-bold text-slate-700 mb-2">
                            الجامعة <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-3 w-full px-4 py-3 bg-medical-50/70 border border-medical-200 rounded-xl text-sm font-bold text-medical-900">
                            <img src="{{ asset($selectedUniversity['logo']) }}" alt="" class="w-7 h-7 object-contain">
                            <span>{{ $selectedUniversity['name'] }}</span>
                        </div>
                        <input type="hidden" id="university_id" name="university_id" value="{{ $selectedUniversity['key'] }}">
                        @error('university_id')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 6. Academic Level -->
                    <div>
                        <label for="academic_level" class="block text-xs font-bold text-slate-700 mb-2">
                            المستوى الأكاديمي <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="academic_level" 
                                    name="academic_level" 
                                    required
                                    class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200 appearance-none">
                                <option value="" disabled {{ old('academic_level') ? '' : 'selected' }}>اختر المستوى الدراسي...</option>
                                @foreach($academicLevels as $key => $label)
                                    <option value="{{ $key }}" {{ old('academic_level') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>يُرجى تحديد المستوى الأكاديمي.</span>
                        </p>
                        @error('academic_level')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 7. GPA Input -->
                    <div class="sm:col-span-2">
                        <label for="gpa" class="block text-xs font-bold text-slate-700 mb-2">
                            المعدل التراكمي (GPA / النسبة المئوية) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="gpa" 
                                   name="gpa" 
                                   value="{{ old('gpa') }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   placeholder="مثال: 88.50 أو 3.80"
                                   required
                                   class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200">
                            <i data-lucide="award" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>أدخل المعدل التراكمي الصحيح.</span>
                        </p>
                        @error('gpa')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- ================================================================= -->
            <!-- SECTION 3: Housing Information -->
            <!-- ================================================================= -->
            <div class="form-card bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-slate-200/80">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-medical-50 flex items-center justify-center text-medical-600 border border-medical-100">
                            <i data-lucide="home" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">القسم الثالث: معلومات السكن</h2>
                            <p class="text-xs text-slate-500">تحديد نوع السكن والإقامة الحالية</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">3 من 5</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- 8. Governorate -->
                    <div>
                        <label for="governorate" class="block text-xs font-bold text-slate-700 mb-2">
                            المحافظة <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="governorate"
                                    name="governorate"
                                    required
                                    class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200 appearance-none">
                                <option value="" disabled {{ old('governorate') ? '' : 'selected' }}>اختر المحافظة...</option>
                                @foreach($governorates as $key => $label)
                                    <option value="{{ $key }}" {{ old('governorate') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        </div>
                        <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>حقل المحافظة مطلوب.</span>
                        </p>
                        @error('governorate')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 9. Current Housing Type -->
                    <div>
                    <label for="housing_type" class="block text-xs font-bold text-slate-700 mb-2">
                        نوع السكن الحالي <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="housing_type" 
                                name="housing_type" 
                                required
                                class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-medical-600 focus:bg-white focus:ring-4 focus:ring-medical-100 transition-all duration-200 appearance-none">
                            <option value="" disabled {{ old('housing_type') ? '' : 'selected' }}>اختر نوع السكن الحالي...</option>
                            @foreach($housingTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('housing_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    </div>
                    <p class="error-msg hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        <span>حقل نوع السكن الحالي مطلوب.</span>
                    </p>
                    @error('housing_type')
                        <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                    </div>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- SECTION 4: Required Documents -->
            <!-- ================================================================= -->
            <div class="form-card bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-slate-200/80">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-medical-50 flex items-center justify-center text-medical-600 border border-medical-100">
                            <i data-lucide="folder-up" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">القسم الرابع: الوثائق والمستندات المطلوبة</h2>
                            <p class="text-xs text-slate-500">رفع الصور والمستندات بصيغة (JPG, PNG, PDF) بحجم أقصى 5 ميجابايت</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">4 من 5</span>
                </div>

                <div class="space-y-6">

                    <!-- Document 1: Personal Photo -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            1. الصورة الشخصية (Personal Photo) <span class="text-rose-500">*</span>
                        </label>
                        <div class="dropzone-container relative" data-field="personal_photo" data-required="true">
                            <input type="file" id="personal_photo" name="personal_photo" accept="image/*" class="sr-only">
                            
                            <div class="dropzone-area border-2 border-dashed border-slate-300 hover:border-medical-500 bg-slate-50/70 hover:bg-medical-50/40 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 group">
                                <div class="w-12 h-12 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400 group-hover:text-medical-600 group-hover:scale-110 group-hover:border-medical-200 transition-all duration-200">
                                    <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-slate-700 group-hover:text-medical-700 transition-colors">
                                    اسحب وأسقط الصورة الشخصية هنا أو انقر للتصفح
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1">الصيغ المسموح بها: JPG, PNG (حد أقصى 5MB)</p>
                            </div>

                            <div class="progress-area hidden border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="loader-2" class="w-4 h-4 text-medical-600 animate-spin"></i>
                                        جاري رفع الملف...
                                    </span>
                                    <span class="progress-text font-bold text-medical-600">0%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="progress-bar-inner bg-medical-600 h-2 rounded-full transition-all duration-150 w-0"></div>
                                </div>
                            </div>

                            <div class="preview-area hidden border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="" alt="Preview" class="image-preview hidden w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <div class="pdf-badge hidden w-12 h-12 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                                        <i data-lucide="file-text" class="w-6 h-6"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <p class="file-name text-xs font-bold text-slate-800 truncate"></p>
                                        </div>
                                        <p class="file-size text-[11px] text-slate-500 mt-0.5"></p>
                                    </div>
                                </div>
                                <button type="button" class="btn-remove-file p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 shrink-0" title="إزالة الملف">
                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                </button>
                            </div>
                            <p class="upload-error hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>يرجى رفع الصورة الشخصية المطلوبة.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Document 2: National ID Image -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            2. صورة الهوية الوطنية (National ID Image) <span class="text-rose-500">*</span>
                        </label>
                        <div class="dropzone-container relative" data-field="national_id_image" data-required="true">
                            <input type="file" id="national_id_image" name="national_id_image" accept="image/*,application/pdf" class="sr-only">
                            
                            <div class="dropzone-area border-2 border-dashed border-slate-300 hover:border-medical-500 bg-slate-50/70 hover:bg-medical-50/40 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 group">
                                <div class="w-12 h-12 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400 group-hover:text-medical-600 group-hover:scale-110 group-hover:border-medical-200 transition-all duration-200">
                                    <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-slate-700 group-hover:text-medical-700 transition-colors">
                                    اسحب وأسقط صورة الهوية الوطنية أو ملف PDF هنا
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1">الصيغ المسموح بها: JPG, PNG, PDF (حد أقصى 5MB)</p>
                            </div>

                            <div class="progress-area hidden border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="loader-2" class="w-4 h-4 text-medical-600 animate-spin"></i>
                                        جاري رفع الملف...
                                    </span>
                                    <span class="progress-text font-bold text-medical-600">0%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="progress-bar-inner bg-medical-600 h-2 rounded-full transition-all duration-150 w-0"></div>
                                </div>
                            </div>

                            <div class="preview-area hidden border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="" alt="Preview" class="image-preview hidden w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <div class="pdf-badge hidden w-12 h-12 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                                        <i data-lucide="file-text" class="w-6 h-6"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <p class="file-name text-xs font-bold text-slate-800 truncate"></p>
                                        </div>
                                        <p class="file-size text-[11px] text-slate-500 mt-0.5"></p>
                                    </div>
                                </div>
                                <button type="button" class="btn-remove-file p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 shrink-0" title="إزالة الملف">
                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                </button>
                            </div>
                            <p class="upload-error hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>يرجى رفع صورة الهوية الوطنية.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Document 3: University Enrollment Certificate (شهادة قيد) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            3. شهادة قيد (University Enrollment Certificate) <span class="text-rose-500">*</span>
                        </label>
                        <div class="dropzone-container relative" data-field="enrollment_cert" data-required="true">
                            <input type="file" id="enrollment_cert" name="enrollment_cert" accept="image/*,application/pdf" class="sr-only">
                            
                            <div class="dropzone-area border-2 border-dashed border-slate-300 hover:border-medical-500 bg-slate-50/70 hover:bg-medical-50/40 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 group">
                                <div class="w-12 h-12 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400 group-hover:text-medical-600 group-hover:scale-110 group-hover:border-medical-200 transition-all duration-200">
                                    <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-slate-700 group-hover:text-medical-700 transition-colors">
                                    اسحب وأسقط شهادة قيد هنا أو انقر للتصفح
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1">الصيغ المسموح بها: JPG, PNG, PDF (حد أقصى 5MB)</p>
                            </div>

                            <div class="progress-area hidden border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="loader-2" class="w-4 h-4 text-medical-600 animate-spin"></i>
                                        جاري رفع الملف...
                                    </span>
                                    <span class="progress-text font-bold text-medical-600">0%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="progress-bar-inner bg-medical-600 h-2 rounded-full transition-all duration-150 w-0"></div>
                                </div>
                            </div>

                            <div class="preview-area hidden border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="" alt="Preview" class="image-preview hidden w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <div class="pdf-badge hidden w-12 h-12 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                                        <i data-lucide="file-text" class="w-6 h-6"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <p class="file-name text-xs font-bold text-slate-800 truncate"></p>
                                        </div>
                                        <p class="file-size text-[11px] text-slate-500 mt-0.5"></p>
                                    </div>
                                </div>
                                <button type="button" class="btn-remove-file p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 shrink-0" title="إزالة الملف">
                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                </button>
                            </div>
                            <p class="upload-error hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>يرجى رفع شهادة قيد.</span>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================================================================= -->
            <!-- SECTION 5: Special Conditions -->
            <!-- ================================================================= -->
            <div class="form-card bg-white rounded-2xl p-6 sm:p-8 shadow-soft border border-slate-200/80">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-medical-50 flex items-center justify-center text-medical-600 border border-medical-100">
                            <i data-lucide="heart-handshake" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">القسم الخامس: الحالات والظروف الخاصة</h2>
                            <p class="text-xs text-slate-500">إجابة الأسئلة الشرطية وإرفاق الإثباتات اللازمة في حال الانطباق</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">5 من 5</span>
                </div>

                <div class="space-y-8 divide-y divide-slate-100">

                    <!-- Question 1: Father Deceased / Martyr -->
                    <div class="pt-4 first:pt-0">
                        <label class="block text-sm font-semibold text-slate-800 mb-3">
                            هل والد الطالب شهيد أو أسير؟ <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4 max-w-xs">
                            <label class="cursor-pointer">
                                <input type="radio" name="is_father_martyr" value="yes" class="sr-only conditional-radio" data-target="father_death_cert_box" required>
                                <div class="radio-tile flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl text-slate-600 font-medium text-sm">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>نعم</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="is_father_martyr" value="no" class="sr-only conditional-radio" data-target="father_death_cert_box" required checked>
                                <div class="radio-tile flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl text-slate-600 font-medium text-sm">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                    <span>لا</span>
                                </div>
                            </label>
                        </div>

                        <!-- Conditional Upload: Father Death Certificate -->
                        <div id="father_death_cert_box" class="conditional-box">
                            <div class="p-4 bg-amber-50/50 border border-amber-200/70 rounded-xl">
                                <label class="block text-xs font-bold text-amber-900 mb-2">
                                    رفع إثبات استشهاد أو أسر الوالد <span class="text-rose-500">*</span>
                                </label>
                                <div class="dropzone-container relative" data-field="father_death_cert" data-required="false">
                                    <input type="file" id="father_death_cert" name="father_death_cert" accept="image/*,application/pdf" class="sr-only">
                                    
                                    <div class="dropzone-area border-2 border-dashed border-amber-300 hover:border-amber-500 bg-white rounded-xl p-5 text-center cursor-pointer transition-all duration-200 group">
                                        <div class="w-10 h-10 rounded-full bg-amber-100/60 flex items-center justify-center mx-auto mb-2 text-amber-700 group-hover:scale-110 transition-all">
                                            <i data-lucide="file-badge-2" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-700 group-hover:text-amber-800">
                                            اسحب وأسقط إثبات الاستشهاد أو الأسر هنا أو انقر للتصفح
                                        </p>
                                    </div>

                                    <div class="progress-area hidden border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-amber-600 animate-spin"></i>
                                                جاري الرفع...
                                            </span>
                                            <span class="progress-text font-bold text-amber-600">0%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="progress-bar-inner bg-amber-600 h-2 rounded-full transition-all duration-150 w-0"></div>
                                        </div>
                                    </div>

                                    <div class="preview-area hidden border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img src="" alt="Preview" class="image-preview hidden w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                            <div class="pdf-badge hidden w-12 h-12 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                                                <i data-lucide="file-text" class="w-6 h-6"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <p class="file-name text-xs font-bold text-slate-800 truncate"></p>
                                                </div>
                                                <p class="file-size text-[11px] text-slate-500 mt-0.5"></p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-remove-file p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 shrink-0">
                                            <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                        </button>
                                    </div>
                                    <p class="upload-error hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>يرجى إرفاق إثبات استشهاد أو أسر الوالد.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Question 2: Disability or Injury -->
                    <div class="pt-6">
                        <label class="block text-sm font-semibold text-slate-800 mb-3">
                            هل يعاني الطالب من إعاقة أو إصابة موثقة؟ <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4 max-w-xs">
                            <label class="cursor-pointer">
                                <input type="radio" name="has_disability" value="yes" class="sr-only conditional-radio" data-target="disability_report_box" required>
                                <div class="radio-tile flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl text-slate-600 font-medium text-sm">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>نعم</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="has_disability" value="no" class="sr-only conditional-radio" data-target="disability_report_box" required checked>
                                <div class="radio-tile flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl text-slate-600 font-medium text-sm">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                    <span>لا</span>
                                </div>
                            </label>
                        </div>

                        <!-- Conditional Upload: Disability / Medical Report -->
                        <div id="disability_report_box" class="conditional-box">
                            <div class="p-4 bg-amber-50/50 border border-amber-200/70 rounded-xl">
                                <label class="block text-xs font-bold text-amber-900 mb-2">
                                    رفع التقارير الطبية / إثبات الإعاقة أو الإصابة <span class="text-rose-500">*</span>
                                </label>
                                <div class="dropzone-container relative" data-field="medical_report" data-required="false">
                                    <input type="file" id="medical_report" name="medical_report" accept="image/*,application/pdf" class="sr-only">
                                    
                                    <div class="dropzone-area border-2 border-dashed border-amber-300 hover:border-amber-500 bg-white rounded-xl p-5 text-center cursor-pointer transition-all duration-200 group">
                                        <div class="w-10 h-10 rounded-full bg-amber-100/60 flex items-center justify-center mx-auto mb-2 text-amber-700 group-hover:scale-110 transition-all">
                                            <i data-lucide="activity" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-700 group-hover:text-amber-800">
                                            اسحب وأسقط التقرير الطبي المعتمد هنا أو انقر للتصفح
                                        </p>
                                    </div>

                                    <div class="progress-area hidden border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-amber-600 animate-spin"></i>
                                                جاري الرفع...
                                            </span>
                                            <span class="progress-text font-bold text-amber-600">0%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="progress-bar-inner bg-amber-600 h-2 rounded-full transition-all duration-150 w-0"></div>
                                        </div>
                                    </div>

                                    <div class="preview-area hidden border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img src="" alt="Preview" class="image-preview hidden w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                            <div class="pdf-badge hidden w-12 h-12 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                                                <i data-lucide="file-text" class="w-6 h-6"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <p class="file-name text-xs font-bold text-slate-800 truncate"></p>
                                                </div>
                                                <p class="file-size text-[11px] text-slate-500 mt-0.5"></p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-remove-file p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 shrink-0">
                                            <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                        </button>
                                    </div>
                                    <p class="upload-error hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>يرجى إرفاق التقرير الطبي المعتمد.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Question 3: Sibling Studying at University -->
                    <div class="pt-6">
                        <label class="block text-sm font-semibold text-slate-800 mb-3">
                            هل يوجد أخ أو أخت للمُقدم يدرس بالجامعة حالياً؟ <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4 max-w-xs">
                            <label class="cursor-pointer">
                                <input type="radio" name="has_sibling_student" value="yes" class="sr-only conditional-radio" data-target="sibling_cert_box" required>
                                <div class="radio-tile flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl text-slate-600 font-medium text-sm">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>نعم</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="has_sibling_student" value="no" class="sr-only conditional-radio" data-target="sibling_cert_box" required checked>
                                <div class="radio-tile flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-xl text-slate-600 font-medium text-sm">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                    <span>لا</span>
                                </div>
                            </label>
                        </div>

                        <!-- Conditional Upload: Sibling Enrollment Cert -->
                        <div id="sibling_cert_box" class="conditional-box">
                            <div class="p-4 bg-amber-50/50 border border-amber-200/70 rounded-xl">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                    <div>
                                        <label for="sibling_name" class="block text-xs font-bold text-amber-900 mb-2">اسم الأخ / الأخت <span class="text-rose-500">*</span></label>
                                        <input type="text" id="sibling_name" name="sibling_name" value="{{ old('sibling_name') }}" placeholder="الاسم الكامل" class="w-full px-4 py-3 bg-white border border-amber-200 rounded-xl text-sm focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                        @error('sibling_name')<p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="sibling_university" class="block text-xs font-bold text-amber-900 mb-2">اسم الجامعة <span class="text-rose-500">*</span></label>
                                        <input type="text" id="sibling_university" name="sibling_university" value="{{ old('sibling_university') }}" placeholder="الجامعة التي يدرس فيها" class="w-full px-4 py-3 bg-white border border-amber-200 rounded-xl text-sm focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                        @error('sibling_university')<p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <label class="block text-xs font-bold text-amber-900 mb-2">
                                    رفع شهادة قيد للأخ / الأخت <span class="text-rose-500">*</span>
                                </label>
                                <div class="dropzone-container relative" data-field="sibling_enrollment_cert" data-required="false">
                                    <input type="file" id="sibling_enrollment_cert" name="sibling_enrollment_cert" accept="image/*,application/pdf" class="sr-only">
                                    
                                    <div class="dropzone-area border-2 border-dashed border-amber-300 hover:border-amber-500 bg-white rounded-xl p-5 text-center cursor-pointer transition-all duration-200 group">
                                        <div class="w-10 h-10 rounded-full bg-amber-100/60 flex items-center justify-center mx-auto mb-2 text-amber-700 group-hover:scale-110 transition-all">
                                            <i data-lucide="users" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-700 group-hover:text-amber-800">
                                            اسحب وأسقط شهادة قيد الأخ/الأخت هنا أو انقر للتصفح
                                        </p>
                                    </div>

                                    <div class="progress-area hidden border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-amber-600 animate-spin"></i>
                                                جاري الرفع...
                                            </span>
                                            <span class="progress-text font-bold text-amber-600">0%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="progress-bar-inner bg-amber-600 h-2 rounded-full transition-all duration-150 w-0"></div>
                                        </div>
                                    </div>

                                    <div class="preview-area hidden border border-emerald-200 bg-emerald-50/40 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img src="" alt="Preview" class="image-preview hidden w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                            <div class="pdf-badge hidden w-12 h-12 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                                                <i data-lucide="file-text" class="w-6 h-6"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <p class="file-name text-xs font-bold text-slate-800 truncate"></p>
                                                </div>
                                                <p class="file-size text-[11px] text-slate-500 mt-0.5"></p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-remove-file p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 shrink-0">
                                            <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                        </button>
                                    </div>
                                    <p class="upload-error hidden text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>يرجى رفع إفادة التسجيل الجامعي للأخ/الأخت.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================================================================= -->
            <!-- BOTTOM ACTIONS BAR -->
            <!-- ================================================================= -->
            <div id="submitError" role="alert" class="hidden p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold"></div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-4">
                
                <button type="button" 
                        onclick="if(confirm('هل أنت تأكد من إغلاق وإلغاء التسجيل؟')) location.reload();"
                        class="w-full sm:w-auto px-6 py-3.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                    <span>إلغاء</span>
                </button>

                <button type="submit" 
                        id="btnSubmit"
                        class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-medical-600 to-medical-700 hover:from-medical-700 hover:to-medical-800 text-white font-bold text-sm shadow-md hover:shadow-lg focus:ring-4 focus:ring-medical-200 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2">
                    <span id="submitSpinner" class="hidden">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <i id="submitIcon" data-lucide="send" class="w-4 h-4"></i>
                    <span id="submitText">تقديم التسجيل</span>
                </button>
            </div>

        </form>

        <!-- ================================================================= -->
        <!-- SUCCESS STATE SCREEN -->
        <!-- ================================================================= -->
        <div id="successModal" class="hidden bg-white rounded-2xl p-8 sm:p-12 shadow-card-hover border border-emerald-200 text-center max-w-lg mx-auto my-12 animate-fade-in">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i data-lucide="check-circle-2" class="w-10 h-10"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 mb-2">تم تقديم طلبك بنجاح!</h2>
            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                شكراً لك. تم إرسال كافة البيانات والوثائق المرفقة بنجاح إلى لجنة الاعتماد الطبي. سيتم مراجعة الطلب والتواصل معك قريباً.
            </p>
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 font-mono mb-6">
                رقم مرجعية الطلب: <span class="font-bold text-slate-800" id="refNumber">MED-2026-9481</span>
            </div>
            <button onclick="window.location.reload();" class="px-6 py-3 rounded-xl bg-slate-900 text-white font-bold text-sm hover:bg-slate-800 transition-all">
                تقديم طلب جديد
            </button>
        </div>

    </div>

    <!-- JAVASCRIPT INTERACTIVE LOGIC -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            const form = document.getElementById('medicalRegistrationForm');
            const btnSubmit = document.getElementById('btnSubmit');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            const submitIcon = document.getElementById('submitIcon');
            const submitError = document.getElementById('submitError');
            const successModal = document.getElementById('successModal');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');

            // -------------------------------------------------------------
            // 1. Conditional Fields Animation & Logic
            // -------------------------------------------------------------
            const conditionalRadios = document.querySelectorAll('.conditional-radio');

            conditionalRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const targetId = this.getAttribute('data-target');
                    const targetBox = document.getElementById(targetId);
                    if (!targetBox) return;

                    const fileZone = targetBox.querySelector('.dropzone-container');
                    const fileInput = targetBox.querySelector('input[type="file"]');

                    if (this.value === 'yes') {
                        targetBox.classList.add('active');
                        if (fileZone) fileZone.dataset.conditionalRequired = "true";
                        if (fileInput) fileInput.dataset.conditionalRequired = "true";
                    } else {
                        targetBox.classList.remove('active');
                        if (fileZone) delete fileZone.dataset.conditionalRequired;
                        if (fileInput) delete fileInput.dataset.conditionalRequired;
                    }
                    updateProgress();
                });
            });

            // -------------------------------------------------------------
            // 2. Drag & Drop File Upload Handlers with Live Preview & Progress
            // -------------------------------------------------------------
            const uploadDropzones = document.querySelectorAll('.dropzone-container');

            uploadDropzones.forEach(zone => {
                const fileInput = zone.querySelector('input[type="file"]');
                const dropzoneArea = zone.querySelector('.dropzone-area');
                const previewArea = zone.querySelector('.preview-area');
                const progressArea = zone.querySelector('.progress-area');
                const progressBarInner = zone.querySelector('.progress-bar-inner');
                const progressText = zone.querySelector('.progress-text');
                const fileNameEl = zone.querySelector('.file-name');
                const fileSizeEl = zone.querySelector('.file-size');
                const imagePreview = zone.querySelector('.image-preview');
                const pdfBadge = zone.querySelector('.pdf-badge');
                const btnRemove = zone.querySelector('.btn-remove-file');

                if (dropzoneArea) {
                    dropzoneArea.addEventListener('click', () => fileInput.click());

                    ['dragenter', 'dragover'].forEach(eventName => {
                        dropzoneArea.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzoneArea.classList.add('dropzone-active');
                        }, false);
                    });

                    ['dragleave', 'drop'].forEach(eventName => {
                        dropzoneArea.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzoneArea.classList.remove('dropzone-active');
                        }, false);
                    });

                    dropzoneArea.addEventListener('drop', (e) => {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        if (files && files.length > 0) {
                            fileInput.files = files;
                            handleFileSelection(files[0]);
                        }
                    });
                }

                if (fileInput) {
                    fileInput.addEventListener('change', function () {
                        if (this.files && this.files.length > 0) {
                            handleFileSelection(this.files[0]);
                        }
                    });
                }

                function handleFileSelection(file) {
                    const errEl = zone.querySelector('.upload-error');
                    if (errEl) errEl.classList.add('hidden');

                    if (dropzoneArea) dropzoneArea.classList.add('hidden');
                    if (progressArea) progressArea.classList.remove('hidden');
                    if (previewArea) previewArea.classList.add('hidden');

                    let currentProgress = 0;
                    if (progressBarInner) progressBarInner.style.width = '0%';
                    if (progressText) progressText.textContent = '0%';

                    const interval = setInterval(() => {
                        currentProgress += Math.floor(Math.random() * 25) + 15;
                        if (currentProgress >= 100) {
                            currentProgress = 100;
                            clearInterval(interval);

                            setTimeout(() => {
                                if (progressArea) progressArea.classList.add('hidden');
                                if (previewArea) previewArea.classList.remove('hidden');

                                if (fileNameEl) fileNameEl.textContent = file.name;
                                if (fileSizeEl) fileSizeEl.textContent = formatBytes(file.size);

                                if (file.type.startsWith('image/')) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        if (imagePreview) {
                                            imagePreview.src = e.target.result;
                                            imagePreview.classList.remove('hidden');
                                        }
                                        if (pdfBadge) pdfBadge.classList.add('hidden');
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    if (imagePreview) imagePreview.classList.add('hidden');
                                    if (pdfBadge) pdfBadge.classList.remove('hidden');
                                }
                                updateProgress();
                            }, 300);
                        }
                        if (progressBarInner) progressBarInner.style.width = currentProgress + '%';
                        if (progressText) progressText.textContent = currentProgress + '%';
                    }, 100);
                }

                if (btnRemove) {
                    btnRemove.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (fileInput) fileInput.value = '';
                        if (previewArea) previewArea.classList.add('hidden');
                        if (dropzoneArea) dropzoneArea.classList.remove('hidden');
                        updateProgress();
                    });
                }
            });

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            // -------------------------------------------------------------
            // 3. Dynamic Form Progress Counter
            // -------------------------------------------------------------
            function updateProgress() {
                const inputs = form.querySelectorAll('input[required]:not([type="radio"]), select[required]');
                let filled = 0;
                let total = inputs.length;

                inputs.forEach(input => {
                    if (input.value.trim() !== '') filled++;
                });

                // Check standard required file zones
                const requiredZones = form.querySelectorAll('.dropzone-container[data-required="true"]');
                requiredZones.forEach(zone => {
                    total++;
                    const fileInput = zone.querySelector('input[type="file"]');
                    if (fileInput && fileInput.files && fileInput.files.length > 0) filled++;
                });

                // Check active conditional file zones
                const conditionalZones = form.querySelectorAll('.dropzone-container[data-conditional-required="true"]');
                conditionalZones.forEach(zone => {
                    total++;
                    const fileInput = zone.querySelector('input[type="file"]');
                    if (fileInput && fileInput.files && fileInput.files.length > 0) filled++;
                });

                const percent = Math.min(100, Math.round((filled / Math.max(total, 1)) * 100));
                if (progressBar) progressBar.style.width = percent + '%';
                if (progressPercent) progressPercent.textContent = percent + '%';
            }

            form.addEventListener('input', updateProgress);
            form.addEventListener('change', updateProgress);

            // -------------------------------------------------------------
            // 4. Form Validation & Submission Logic
            // -------------------------------------------------------------
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                let isValid = true;

                // Validate Text/Select/Date Inputs
                const inputs = form.querySelectorAll('input[required]:not([type="radio"]), select[required]');
                inputs.forEach(input => {
                    const errorMsg = input.closest('div')?.querySelector('.error-msg');
                    if (!input.value || input.value.trim() === '') {
                        isValid = false;
                        input.classList.add('border-rose-500');
                        if (errorMsg) errorMsg.classList.remove('hidden');
                    } else {
                        input.classList.remove('border-rose-500');
                        if (errorMsg) errorMsg.classList.add('hidden');
                    }
                });

                // Validate Required Uploads
                const uploadContainers = form.querySelectorAll('.dropzone-container');
                uploadContainers.forEach(zone => {
                    const isReq = zone.dataset.required === "true" || zone.dataset.conditionalRequired === "true";
                    const fileInput = zone.querySelector('input[type="file"]');
                    const errEl = zone.querySelector('.upload-error');

                    if (isReq && (!fileInput || !fileInput.files || fileInput.files.length === 0)) {
                        isValid = false;
                        if (errEl) errEl.classList.remove('hidden');
                    } else {
                        if (errEl) errEl.classList.add('hidden');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    const firstError = form.querySelector('.border-rose-500, .upload-error:not(.hidden)');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }

                // Upload with real progress and a finite timeout. This prevents
                // the production form from appearing to load forever.
                btnSubmit.disabled = true;
                if (submitSpinner) submitSpinner.classList.remove('hidden');
                if (submitIcon) submitIcon.classList.add('hidden');
                if (submitText) submitText.textContent = 'جاري رفع الملفات... 0%';
                if (submitError) submitError.classList.add('hidden');

                const resetSubmitButton = () => {
                    btnSubmit.disabled = false;
                    if (submitSpinner) submitSpinner.classList.add('hidden');
                    if (submitIcon) submitIcon.classList.remove('hidden');
                    if (submitText) submitText.textContent = 'إعادة المحاولة';
                };

                const showSubmitError = (message) => {
                    resetSubmitButton();
                    if (!submitError) return;
                    submitError.textContent = message;
                    submitError.classList.remove('hidden');
                    submitError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                };

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.responseType = 'json';
                xhr.timeout = 120000;
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', (event) => {
                    if (!event.lengthComputable || !submitText) return;
                    const percent = Math.min(99, Math.round((event.loaded / event.total) * 100));
                    submitText.textContent = `جاري رفع الملفات... ${percent}%`;
                });

                xhr.upload.addEventListener('load', () => {
                    if (submitText) submitText.textContent = 'جاري حفظ الطلب...';
                });

                xhr.addEventListener('load', () => {
                    const response = xhr.response || {};
                    if (xhr.status >= 200 && xhr.status < 300) {
                        form.classList.add('hidden');
                        if (successModal) successModal.classList.remove('hidden');
                        const refNumber = document.getElementById('refNumber');
                        if (refNumber && response.reference_number) {
                            refNumber.textContent = response.reference_number;
                        }
                        successModal?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    if (xhr.status === 422 && response.errors) {
                        const firstMessage = Object.values(response.errors).flat()[0];
                        showSubmitError(firstMessage || 'بعض البيانات غير صحيحة. راجع الحقول وحاول مجددًا.');
                    } else if (xhr.status === 413) {
                        showSubmitError('حجم الملفات أكبر من الحد المسموح على الخادم. صغّر الملفات ثم حاول مجددًا.');
                    } else if (xhr.status === 419) {
                        showSubmitError('انتهت جلسة الصفحة. حدّث الصفحة ثم أرسل الطلب مجددًا.');
                    } else {
                        showSubmitError(`تعذّر حفظ الطلب (خطأ ${xhr.status || 'غير معروف'}). حاول مجددًا.`);
                    }
                });

                xhr.addEventListener('timeout', () => {
                    showSubmitError('انتهت مهلة الإرسال بعد دقيقتين. تحقّق من الإنترنت ثم حاول مجددًا.');
                });

                xhr.addEventListener('error', () => {
                    showSubmitError('انقطع الاتصال أثناء إرسال الطلب. بياناتك ما زالت في الصفحة؛ حاول مجددًا.');
                });

                xhr.send(new FormData(form));
            });

            // Initial progress calculation
            updateProgress();
        });
    </script>
</body>
</html>
