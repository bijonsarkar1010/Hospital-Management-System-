<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestrictedActionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SymptomRuleController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Authenticated Reception Admin Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Patients
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/check-duplicate', [PatientController::class, 'checkDuplicate'])->name('patients.check_duplicate');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');

    // 3. Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments/allocate', [AppointmentController::class, 'allocate'])->name('appointments.allocate');
    Route::get('/appointments/confirm-preview', [AppointmentController::class, 'confirmPreview'])->name('appointments.confirm_preview');
    Route::post('/appointments/confirm', [AppointmentController::class, 'confirmBooking'])->name('appointments.confirm');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/slip', [AppointmentController::class, 'slip'])->name('appointments.slip');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update_status');
    Route::get('/appointments/{appointment}/reschedule', [AppointmentController::class, 'rescheduleForm'])->name('appointments.reschedule_form');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // 4. Doctors
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
    Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store');
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');
    Route::get('/doctors/{doctor}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
    Route::put('/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
    Route::post('/doctors/{doctor}/toggle-status', [DoctorController::class, 'toggleStatus'])->name('doctors.toggle_status');

    // 5. Schedules & Duty Roster
    Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/duty-roster', [DoctorScheduleController::class, 'index'])->name('roster.index');
    Route::get('/doctors/{doctor}/schedules', [DoctorScheduleController::class, 'manage'])->name('schedules.manage');
    Route::post('/doctors/{doctor}/schedules', [DoctorScheduleController::class, 'updateSchedule'])->name('schedules.update');
    Route::post('/doctors/{doctor}/breaks', [DoctorScheduleController::class, 'addBreak'])->name('schedules.breaks.add');
    Route::delete('/breaks/{break}', [DoctorScheduleController::class, 'deleteBreak'])->name('schedules.breaks.delete');
    Route::post('/doctors/{doctor}/leaves', [DoctorScheduleController::class, 'addLeave'])->name('schedules.leaves.add');
    Route::delete('/leaves/{unavailability}', [DoctorScheduleController::class, 'deleteLeave'])->name('schedules.leaves.delete');
    Route::get('/api/doctors/{doctor}/slots', [DoctorScheduleController::class, 'getSlots'])->name('api.doctors.slots');

    // 6. Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');

    // 7. Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // 8. Symptom Entity & Specialty Intent Rules Administration
    Route::post('/api/symptoms/predict', [SymptomRuleController::class, 'predict'])->name('api.symptoms.predict');
    Route::get('/settings/symptom-rules', [SymptomRuleController::class, 'index'])->name('settings.symptom_rules.index');
    Route::post('/settings/symptom-rules', [SymptomRuleController::class, 'store'])->name('settings.symptom_rules.store');
    Route::put('/settings/symptom-rules/{rule}', [SymptomRuleController::class, 'update'])->name('settings.symptom_rules.update');
    Route::post('/settings/symptom-rules/{rule}/toggle', [SymptomRuleController::class, 'toggle'])->name('settings.symptom_rules.toggle');
    Route::delete('/settings/symptom-rules/{rule}', [SymptomRuleController::class, 'destroy'])->name('settings.symptom_rules.destroy');
    Route::post('/settings/symptom-rules/test-simulator', [SymptomRuleController::class, 'testSimulator'])->name('settings.symptom_rules.test_simulator');

    // 9. Restricted Administrative Actions (System Administrator Only)
    Route::middleware('restricted.admin')->group(function () {
        Route::get('/settings/restricted/patient-deletion', [RestrictedActionController::class, 'patientDeletionForm'])->name('settings.restricted.patient_deletion');
        Route::get('/settings/restricted/patient-deletion/search', [RestrictedActionController::class, 'searchPatient'])->name('settings.restricted.patient_deletion.search');
        Route::post('/settings/restricted/patient-deletion', [RestrictedActionController::class, 'destroyPatient'])->name('settings.restricted.patient_deletion.destroy');
    });

    // Daily Operations Report
    Route::get('/reports/daily', [ReportController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/daily/csv', [ReportController::class, 'downloadDailyCsv'])->name('reports.daily_csv');
});

