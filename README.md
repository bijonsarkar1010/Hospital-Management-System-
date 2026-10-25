# CarePoint Clinic Management System (CMS)

A simple, functional, and modern **Clinic Management System (CMS)** built with **Laravel**, **Blade templates**, **Bootstrap 5**, and **MySQL**. Designed specifically for hospital and clinic reception staff to manage patient registration, real-time doctor availability, dynamic appointment scheduling, duty rosters, and daily clinic operations from a unified minimalist dashboard.

---

## 1. Key Highlights & Architectural Features

- **Full-Stack Monolith (No SPA/React/Node required):** Server-rendered Laravel Blade templates with Bootstrap 5 and vanilla JavaScript.
- **Dynamic Scheduling Engine (`AppointmentSchedulingService`):**
  - Evaluates doctor working hours, consultation duration, recurring breaks, leave/holidays, existing appointments, and clinic operating hours.
  - Supports **Option A: Automatic Assignment** (earliest slot, lowest workload tie-breaker), **Option B: Specialty Selection**, and **Option C: Particular Doctor Selection**.
  - Automatically searches forward within the configurable booking horizon (up to 30 days) if requested slot is occupied and flags alternative allocations.
- **Concurrency & Double-Booking Protection:** Pessimistic row locking (`lockForUpdate()`) and atomic re-validation inside database transactions to prevent race-condition double-booking.
- **Receptionist Usability & Duplicate Detection:** Real-time phone number duplicate verification via AJAX during registration, with 1-click access to existing patient records.
- **Printable Appointment Slip:** Print-friendly blade view with dedicated print CSS suitable for standard A4 paper or compact receipt printers.
- **Duty Roster & Conflict Safeguard:** Prevents silent cancellation of booked appointments when doctor working schedules are modified.
- **Operational Reports:** Filterable summaries by doctor, specialty, date, and status with 1-click **CSV Export**.
- **Comprehensive Demo Database:** Seeded with 4 reception administrators, 15 medical specialties, 30 fictional doctors, 55 patients, schedules, breaks, leaves, and appointments.
- **Automated Test Suite:** 23 passing feature tests with 76 assertions covering authentication, registration, scheduling, conflict prevention, and slips.

---

## 2. Technology Stack

- **Backend:** PHP 8.2+ with Laravel Framework
- **Frontend:** Laravel Blade Templates
- **Styling:** Bootstrap 5.3.3 & Bootstrap Icons 1.11.3 (minimal clean custom CSS)
- **JavaScript:** Vanilla JavaScript (dynamic duplicate checking, mode toggling, live clock)
- **Database:** MySQL / MariaDB (persisting patients, doctors, appointments, schedules, settings, and users)
- **Authentication:** Laravel session-based authentication with Bcrypt password hashing

---

## 3. Demo Reception Administrator Credentials

The database seeder provisions 4 pre-configured reception administrator accounts for immediate testing:

| Staff Member | Role | Demo Email Address | Password |
|---|---|---|---|
| **Eleanor Vance** | Lead Reception Admin | `admin@carepoint.com` | `password` |
| **Sarah Jenkins** | Senior Receptionist | `sarah.reception@carepoint.com` | `password` |
| **David Miller** | Day Shift Receptionist | `david.reception@carepoint.com` | `password` |
| **Emily Clark** | Evening Receptionist | `emily.reception@carepoint.com` | `password` |

> **Security Notice:** These credentials are for local development and demonstration purposes only. Proper credentials and environment secrets must be configured before production deployment.

---

## 4. Installation & Setup Guide

### Step 1: Clone or Navigate to the Repository
```bash
cd c:\xampp\htdocs\hospital_management
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
copy .env.example .env
```
Generate the application encryption key:
```bash
php artisan key:generate
```

### Step 4: Configure the MySQL Database
Ensure MySQL is running (e.g. via XAMPP Control Panel or command line). Create the database in MySQL:
```sql
CREATE DATABASE clinic_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Verify your `.env` database configuration:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinic_management
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Run Migrations and Seed Database
Run database migrations and populate the complete demo environment (30 doctors, 15 specialties, 55 patients, schedules, and past/today/upcoming appointments):
```bash
php artisan migrate:fresh --seed
```

### Step 6: Start the Application Server
Start the local Laravel development server:
```bash
php artisan serve
```
By default, the server runs on `http://127.0.0.1:8000` (or `http://127.0.0.1:8008` if port 8000 is occupied).

Open your browser and navigate to:
```
http://127.0.0.1:8000/login
```

---

## 5. Main Application Modules

### 1. Reception Operations Dashboard (`/dashboard`)
- **7 Dynamic KPI Cards:** Total Patients, Today's Appointments, Upcoming Appointments, Doctors on Duty, Available Doctors Now, Completed Appointments Today, Cancelled Appointments Today.
- **Today's Queue Table:** Searchable by patient/ID, filterable by status/specialty, with quick status transitions (`Scheduled` $\rightarrow$ `Checked In` $\rightarrow$ `In Consultation` $\rightarrow$ `Completed` / `Cancelled` / `No Show`).
- **Doctor Availability Panel:** Real-time duty calculation distinguishing between *On Duty & Available*, *In Consultation*, *Fully Booked Today*, *On Break*, and *Off Duty*. Shows current patient and next available appointment slot.
- **Upcoming Appointments Pipeline:** Filter by *Today*, *Tomorrow*, *Next 7 Days*, or *Custom Date Range*.

### 2. Patient Registration (`/patients/create`)
- Captures required patient demographics (Full Name, Age/DOB, Sex, Phone, Address).
- Optional clinical background: Blood Group (`A+`, `A-`, `B+`, `B-`, `AB+`, `AB-`, `O+`, `O-`, `Unknown`), Diabetes Status, Blood Pressure (Systolic/Diastolic mmHg), Allergies, Medications, Emergency Contact.
- **Real-Time Phone Duplicate Alert:** Checks mobile numbers via debounced AJAX and offers 1-click access to the existing patient file to avoid duplicate entries.
- Directly connects into doctor selection modes:
  - **Option A:** Automatic Assignment (smart load balancing)
  - **Option B:** Medical Specialty Selection (dropdown of 15 departments)
  - **Option C:** Specific Doctor Selection

### 3. Smart Scheduling & Allocation Preview (`/appointments/confirm-preview`)
- Executes **Step 6 Allocation Review**:
  - Compares Requested Date & Time vs Actual Assigned Slot.
  - Highlights alternative slot allocation if the requested time was occupied or outside doctor availability.
  - Finalizes booking with concurrency transaction lock (`lockForUpdate`).

### 4. Printable Appointment Slip (`/appointments/{id}/slip`)
- Clean, compact slip with clinic header, patient code, assigned physician, specialty, consultation room, date/time window, and patient arrival instructions.
- Includes a **Print Appointment Slip** button and dedicated `@media print` CSS.

### 5. Doctor Directory & Profiles (`/doctors`)
- Directory of 30 doctors across 15 medical specialties with consultation durations (15m, 20m, 30m, 45m) and rooms.
- Doctor profiles displaying weekly hours, breaks, leaves, and today's schedule.

### 6. Weekly Duty Roster (`/duty-roster` & `/doctor-schedules`)
- 7-day grid showing working shifts for all physicians.
- Interactive schedule editor with **Conflict Protection**: detects if deactivating a weekday or modifying hours conflicts with existing future bookings.

### 7. Operational Reports & CSV Export (`/reports`)
- Tabular breakdown of appointment volumes by doctor, specialty, date, and status.
- **Export to CSV:** Generates downloadable spreadsheet records with full patient and physician metadata.

### 8. Clinic Settings (`/settings`)
- Configurable clinic name, phone, address, operating hours (e.g. `08:00` - `20:00`), and booking search horizon.

---

## 6. Medical Specialties Seeded

1. **General Medicine** (Dr. Robert Harrison, Dr. Maria Santos)
2. **Cardiology** (Dr. Alexander Hayes, Dr. Priya Sharma)
3. **Dermatology** (Dr. Chloe Bennett, Dr. Marcus Vance)
4. **Orthopaedics** (Dr. Jonathan Reed, Dr. Rachel Kim)
5. **Paediatrics** (Dr. Emily Thornton, Dr. Benjamin Brooks)
6. **Neurology** (Dr. Evelyn Foster, Dr. Samuel Green)
7. **Gynaecology** (Dr. Hannah Morris, Dr. Ananya Patel)
8. **ENT** (Dr. Lucas Ward, Dr. Grace Kelly)
9. **Ophthalmology** (Dr. Arthur Mitchell, Dr. Diana Prince)
10. **Psychiatry** (Dr. Thomas Wayne, Dr. Samantha Cole)
11. **Gastroenterology** (Dr. Daniel Rivera, Dr. Olivia Taylor)
12. **Pulmonology** (Dr. Henry Cooper, Dr. Stephanie Lin)
13. **Endocrinology** (Dr. Victor Frank, Dr. Natalie Hughes)
14. **Urology** (Dr. George Alvarez, Dr. Jessica Parker)
15. **General Surgery** (Dr. Edward Norton, Dr. Laura Palmer)

---

## 7. Automated Test Suite

To run all automated feature and unit tests:

```bash
php artisan test
```

### Test Coverage Highlights:
- `Tests\Feature\AuthenticationTest`: Login, logout, session protection, invalid credentials, dashboard redirect.
- `Tests\Feature\PatientRegistrationTest`: Patient registration, code generation (`PAT-YYYY-XXXX`), duplicate phone detection, validation.
- `Tests\Feature\AppointmentSchedulingTest`: Available slot matching, next-available slot forward searching, leave exclusion, break exclusion, cancelled slot release, specialty filtering, concurrency double-booking prevention.
- `Tests\Feature\AppointmentWorkflowTest`: Search by patient ID and phone, auto assignment mode, working hour boundaries, rescheduling logic, dashboard metric updates, printable slip display.

---

## 8. Directory Structure Overview

```
app/
├── Http/Controllers/
│   ├── AppointmentController.php
│   ├── Auth/LoginController.php
│   ├── DashboardController.php
│   ├── DoctorController.php
│   ├── DoctorScheduleController.php
│   ├── PatientController.php
│   ├── ReportController.php
│   └── SettingController.php
├── Models/
│   ├── Appointment.php
│   ├── ClinicSetting.php
│   ├── Doctor.php
│   ├── DoctorBreak.php
│   ├── DoctorSchedule.php
│   ├── DoctorUnavailability.php
│   ├── Patient.php
│   ├── Specialty.php
│   └── User.php
└── Services/
    ├── AppointmentSchedulingService.php
    └── DoctorAvailabilityService.php

resources/views/
├── appointments/
│   ├── confirm_preview.blade.php
│   ├── create.blade.php
│   ├── index.blade.php
│   ├── reschedule.blade.php
│   ├── show.blade.php
│   └── slip.blade.php
├── auth/
│   └── login.blade.php
├── dashboard.blade.php
├── doctors/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── layouts/
│   └── app.blade.php
├── patients/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── reports/
│   ├── daily.blade.php
│   └── index.blade.php
├── schedules/
│   ├── index.blade.php
│   └── manage.blade.php
└── settings/
    ├── index.blade.php
    ├── restricted/
    │   └── patient_deletion.blade.php
    └── symptom_rules/
        └── index.blade.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 2026_10_14_114558_create_clinic_tables.php
│   ├── 2026_10_15_063544_create_patient_deletion_audits_table.php
│   ├── 2026_10_15_063549_create_symptom_rules_table.php
│   └── 2026_10_15_063555_create_appointment_predictions_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── SymptomRuleSeeder.php

tests/Feature/
├── AppointmentSchedulingTest.php
├── AppointmentWorkflowTest.php
├── AuthenticationTest.php
├── ExampleTest.php
├── PatientRegistrationTest.php
├── RestrictedPatientDeletionTest.php
├── SymptomSchedulingIntegrationTest.php
└── SymptomSpecialtyPredictionTest.php
```

---

## 9. Advanced Clinical Features & Restricted Operations

### A. Database-Driven Symptom-to-Specialty Intent Engine
- **Deterministic & Explainable:** Matches patient symptoms against configurable database rules in `symptom_rules` without external AI APIs or hardcoded UI mocks.
- **Scoring & Confidence:** Multi-level scoring based on exact phrase matches, multi-word keywords, individual keywords, and synonyms. Yields confidence ratings (`High`, `Medium`, `Low`, `Unknown`).
- **Explainability:** Generates a human-readable explanation showing matched entities, intent, keywords, and differential alternatives.
- **Graceful Fallback:** Obscure or unknown complaints automatically route to **General Medicine** for primary care triage.

### B. Urgent Symptom & Emergency Safeguards
- Evaluates red-flag symptoms (e.g. severe crushing chest pain with breathlessness, FAST acute stroke signs, severe hemorrhage).
- Triggers prominent red emergency alert banners with clinical guidance instructions.
- Halts routine automated outpatient booking and prompts immediate emergency resuscitation or triage transfer.

### C. Specialty-Enforced Scheduling
- Confirmed specialty constraints are strictly enforced in `AppointmentSchedulingService`.
- Both primary allocation and alternative slot forward searches check only active doctors within the confirmed specialty.
- Staff overrides (manually choosing a different department or doctor) require or record an explicit override rationale.
- Clinical prediction and routing history are permanently preserved in `appointment_predictions`.

### D. Restricted Permanent Patient Deletion
- **No Routine Delete Action:** Permanent deletion is never exposed on the dashboard, patient directory, or profile.
- **Server-Side Authorization:** Protected by `EnsureRestrictedAdminAccess` (`restricted.admin` middleware). Ordinary receptionists (`reception_admin`) are blocked with HTTP `403 Forbidden`. Only authorized administrators (`admin@carepoint.com` or `system_admin`) are permitted.
- **Active Booking Safeguard:** Deletion is strictly blocked if the patient has active/upcoming bookings (`Scheduled` or `Checked In`).
- **Multi-Factor Confirmation:** Requires exact Patient ID code entry, mandatory legal/operational reason (min 10 chars), and irreversible action acknowledgement.
- **Independent Audit Log:** Transactions log immutable records in `patient_deletion_audits` preserving administrative details, IP address, reason, and patient snapshot.

### E. Rule Management & Interactive Simulator
- Dedicated administrative interface at `/settings/symptom-rules`.
- Add, edit, toggle active status, and tune priority weights for all clinical rules.
- Built-in **Live Test Simulator** allows staff to test symptom phrases and preview live scoring, confidence, and urgency flags.

---

## 10. Automated Test Suite

Run the full automated test suite:
```bash
php artisan test
```

Current test results:
```
Tests:    43 passed (154 assertions)
Duration: 6.14s
```
Covering unit tests, patient registration, scheduling engine, concurrency locks, appointment workflow, authentication, restricted deletion authorization, symptom prediction, and scheduling integration.

