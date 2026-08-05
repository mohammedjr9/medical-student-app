<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_registrations', function (Blueprint $table) {
            $table->id();
            
            // Section 1: Personal Info
            $table->string('full_name');
            $table->string('national_id', 9);
            $table->string('mobile_number');
            $table->date('date_of_birth');

            // Section 2: Academic Info
            $table->string('university_id');
            $table->string('academic_level');
            $table->decimal('gpa', 5, 2);

            // Section 3: Housing Info
            $table->string('housing_type');

            // Section 4: Required Documents Paths
            $table->string('personal_photo_path');
            $table->string('national_id_image_path');
            $table->string('enrollment_cert_path');

            // Section 5: Special Conditions & Conditionals
            $table->enum('is_father_martyr', ['yes', 'no'])->default('no');
            $table->string('father_death_cert_path')->nullable();

            $table->enum('has_disability', ['yes', 'no'])->default('no');
            $table->string('medical_report_path')->nullable();

            $table->enum('has_sibling_student', ['yes', 'no'])->default('no');
            $table->string('sibling_enrollment_cert_path')->nullable();

            $table->string('reference_number')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_registrations');
    }
};
