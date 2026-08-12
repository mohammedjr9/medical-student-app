<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicalRegistrationController;
use App\Http\Controllers\Admin\MedicalRegistrationAdminController;
use App\Http\Controllers\Admin\AuthController;

// Public Registration Form
Route::get('/', [MedicalRegistrationController::class, 'create'])->name('medical-registration.create');
Route::get('/medical-registration', [MedicalRegistrationController::class, 'create']);
Route::get('/islamic-university', [MedicalRegistrationController::class, 'create'])
    ->defaults('university', 'islamic-university')->name('universities.iug');
Route::get('/al-azhar-university', [MedicalRegistrationController::class, 'create'])
    ->defaults('university', 'al-azhar-university')->name('universities.aug');
Route::get('/israa-university', [MedicalRegistrationController::class, 'create'])
    ->defaults('university', 'israa-university')->name('universities.israa');
Route::get('/palestine-university', [MedicalRegistrationController::class, 'create'])
    ->defaults('university', 'palestine-university')->name('universities.upal');
Route::post('/medical-registration', [MedicalRegistrationController::class, 'store'])->name('medical-registration.store');

// Admin Dashboard Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('admin.login.store');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('admin.logout');

    Route::middleware(\App\Http\Middleware\AdminAuthenticated::class)->group(function () {
    Route::get('/', [MedicalRegistrationAdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/students', [MedicalRegistrationAdminController::class, 'index'])->name('admin.students.index');
    Route::get('/students/{id}', [MedicalRegistrationAdminController::class, 'show'])->name('admin.students.show');
    Route::get('/students/{id}/files/edit', [MedicalRegistrationAdminController::class, 'editFiles'])->name('admin.students.files.edit');
    Route::put('/students/{id}/files', [MedicalRegistrationAdminController::class, 'updateFiles'])->name('admin.students.files.update');
    Route::delete('/students/{id}', [MedicalRegistrationAdminController::class, 'destroy'])->name('admin.students.destroy');
    });
});
