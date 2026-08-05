<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_registrations', function (Blueprint $table) {
            $table->string('sibling_name')->nullable()->after('has_sibling_student');
            $table->string('sibling_university')->nullable()->after('sibling_name');
        });
    }

    public function down(): void
    {
        Schema::table('medical_registrations', function (Blueprint $table) {
            $table->dropColumn(['sibling_name', 'sibling_university']);
        });
    }
};
