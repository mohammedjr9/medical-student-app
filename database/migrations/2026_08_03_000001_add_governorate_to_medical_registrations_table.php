<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_registrations', function (Blueprint $table) {
            $table->string('governorate')->nullable()->after('gpa');
        });
    }

    public function down(): void
    {
        Schema::table('medical_registrations', function (Blueprint $table) {
            $table->dropColumn('governorate');
        });
    }
};
