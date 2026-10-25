<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailability;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // -------------------------------------------------------------
        // 1. Seed Reception Admin Accounts
        // -------------------------------------------------------------
        $admins = [
            [
                'name' => 'Eleanor Vance',
                'email' => 'admin@carepoint.com',
                'role' => 'reception_admin',
                'phone' => '+1 (555) 019-2831',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Sarah Jenkins',
                'email' => 'sarah.reception@carepoint.com',
                'role' => 'reception_admin',
                'phone' => '+1 (555) 019-3324',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'David Miller',
                'email' => 'david.reception@carepoint.com',
                'role' => 'reception_admin',
                'phone' => '+1 (555) 019-4482',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Emily Clark',
                'email' => 'emily.reception@carepoint.com',
                'role' => 'reception_admin',
                'phone' => '+1 (555) 019-5519',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($admins as $adminData) {
            User::updateOrCreate(['email' => $adminData['email']], $adminData);
        }

        $defaultAdmin = User::first();

        // -------------------------------------------------------------
        // 2. Clinic Settings
        // -------------------------------------------------------------
        $settings = [
            'clinic_name' => 'CarePoint Outpatient Medical Clinic',
            'clinic_tagline' => 'Advanced Family & Specialty Healthcare',
            'clinic_phone' => '+1 (555) 234-5678',
            'clinic_email' => 'reception@carepointclinic.com',
            'clinic_address' => '742 Evergreen Healthcare Blvd, Suite 300, Medical District',
            'clinic_open_time' => '08:00',
            'clinic_close_time' => '20:00',
            'booking_horizon_days' => '30',
            'default_consultation_duration' => '30',
        ];

        foreach ($settings as $k => $v) {
            ClinicSetting::set($k, $v);
        }

        // -------------------------------------------------------------
        // 3. Specialties (15 Medical Specialties)
        // -------------------------------------------------------------
        $specialtyData = [
            ['name' => 'General Medicine', 'slug' => 'general-medicine', 'description' => 'Comprehensive primary and family healthcare for adult and elderly patients.'],
            ['name' => 'Cardiology', 'slug' => 'cardiology', 'description' => 'Specialized diagnosis and treatment of heart and vascular conditions.'],
            ['name' => 'Dermatology', 'slug' => 'dermatology', 'description' => 'Skin, hair, nail health, skin cancer screenings, and dermatological conditions.'],
            ['name' => 'Orthopaedics', 'slug' => 'orthopaedics', 'description' => 'Bones, joints, ligaments, tendons, and musculoskeletal trauma care.'],
            ['name' => 'Paediatrics', 'slug' => 'paediatrics', 'description' => 'Comprehensive health care for infants, children, and adolescents.'],
            ['name' => 'Neurology', 'slug' => 'neurology', 'description' => 'Evaluation and management of brain, spine, and nervous system disorders.'],
            ['name' => 'Gynaecology', 'slug' => 'gynaecology', 'description' => 'Women reproductive health, prenatal consultations, and wellness checks.'],
            ['name' => 'ENT', 'slug' => 'ent', 'description' => 'Ear, nose, throat, head and neck conditions, and hearing health.'],
            ['name' => 'Ophthalmology', 'slug' => 'ophthalmology', 'description' => 'Comprehensive eye exams, vision care, and ocular diseases.'],
            ['name' => 'Psychiatry', 'slug' => 'psychiatry', 'description' => 'Mental health diagnosis, mood disorders, therapy, and medical support.'],
            ['name' => 'Gastroenterology', 'slug' => 'gastroenterology', 'description' => 'Digestive tract, liver, stomach, and intestinal health management.'],
            ['name' => 'Pulmonology', 'slug' => 'pulmonology', 'description' => 'Respiratory tract diseases, asthma, COPD, and lung function assessments.'],
            ['name' => 'Endocrinology', 'slug' => 'endocrinology', 'description' => 'Hormonal disorders, diabetes management, and thyroid conditions.'],
            ['name' => 'Urology', 'slug' => 'urology', 'description' => 'Urinary system conditions and male reproductive health.'],
            ['name' => 'General Surgery', 'slug' => 'general-surgery', 'description' => 'Pre-operative evaluations, minor outpatient surgical procedures, and post-op care.'],
        ];

        $specialtyMap = [];
        foreach ($specialtyData as $item) {
            $spec = Specialty::updateOrCreate(['slug' => $item['slug']], $item);
            $specialtyMap[$item['name']] = $spec->id;
        }

        // -------------------------------------------------------------
        // 4. 30 Fictional Doctors (2 per specialty)
        // -------------------------------------------------------------
        $doctorsData = [
            // General Medicine
            [
                'name' => 'Dr. Robert Harrison',
                'specialty' => 'General Medicine',
                'qualifications' => 'MD, FACP (Internal Medicine)',
                'experience_years' => 14,
                'consultation_room' => 'Consultation Suite 101',
                'consultation_duration' => 20,
                'email' => 'dr.harrison@carepoint.com',
                'phone' => '+1 (555) 401-1001',
            ],
            [
                'name' => 'Dr. Maria Santos',
                'specialty' => 'General Medicine',
                'qualifications' => 'MBBS, MRCGP (Family Practice)',
                'experience_years' => 9,
                'consultation_room' => 'Consultation Suite 102',
                'consultation_duration' => 20,
                'email' => 'dr.santos@carepoint.com',
                'phone' => '+1 (555) 401-1002',
            ],
            // Cardiology
            [
                'name' => 'Dr. Alexander Hayes',
                'specialty' => 'Cardiology',
                'qualifications' => 'MD, FACC (Interventional Cardiology)',
                'experience_years' => 18,
                'consultation_room' => 'Cardio Care 201',
                'consultation_duration' => 30,
                'email' => 'dr.hayes@carepoint.com',
                'phone' => '+1 (555) 401-1003',
            ],
            [
                'name' => 'Dr. Priya Sharma',
                'specialty' => 'Cardiology',
                'qualifications' => 'MD, DM (Clinical Cardiology)',
                'experience_years' => 11,
                'consultation_room' => 'Cardio Care 202',
                'consultation_duration' => 30,
                'email' => 'dr.sharma@carepoint.com',
                'phone' => '+1 (555) 401-1004',
            ],
            // Dermatology
            [
                'name' => 'Dr. Chloe Bennett',
                'specialty' => 'Dermatology',
                'qualifications' => 'MD, FAAD (Dermatology & Aesthetics)',
                'experience_years' => 10,
                'consultation_room' => 'Derma Clinic 105',
                'consultation_duration' => 20,
                'email' => 'dr.bennett@carepoint.com',
                'phone' => '+1 (555) 401-1005',
            ],
            [
                'name' => 'Dr. Marcus Vance',
                'specialty' => 'Dermatology',
                'qualifications' => 'MBBS, DVD (Medical Dermatology)',
                'experience_years' => 15,
                'consultation_room' => 'Derma Clinic 106',
                'consultation_duration' => 15,
                'email' => 'dr.m.vance@carepoint.com',
                'phone' => '+1 (555) 401-1006',
            ],
            // Orthopaedics
            [
                'name' => 'Dr. Jonathan Reed',
                'specialty' => 'Orthopaedics',
                'qualifications' => 'MS, FRCS (Orthopaedic Surgery)',
                'experience_years' => 16,
                'consultation_room' => 'Ortho Center 301',
                'consultation_duration' => 30,
                'email' => 'dr.reed@carepoint.com',
                'phone' => '+1 (555) 401-1007',
            ],
            [
                'name' => 'Dr. Rachel Kim',
                'specialty' => 'Orthopaedics',
                'qualifications' => 'MD, Sports Medicine Fellowship',
                'experience_years' => 8,
                'consultation_room' => 'Ortho Center 302',
                'consultation_duration' => 30,
                'email' => 'dr.kim@carepoint.com',
                'phone' => '+1 (555) 401-1008',
            ],
            // Paediatrics
            [
                'name' => 'Dr. Emily Thornton',
                'specialty' => 'Paediatrics',
                'qualifications' => 'MD, FAAP (General Paediatrics)',
                'experience_years' => 12,
                'consultation_room' => 'Kids Care 110',
                'consultation_duration' => 20,
                'email' => 'dr.thornton@carepoint.com',
                'phone' => '+1 (555) 401-1009',
            ],
            [
                'name' => 'Dr. Benjamin Brooks',
                'specialty' => 'Paediatrics',
                'qualifications' => 'MBBS, DCH, MRCPCH',
                'experience_years' => 7,
                'consultation_room' => 'Kids Care 111',
                'consultation_duration' => 20,
                'email' => 'dr.brooks@carepoint.com',
                'phone' => '+1 (555) 401-1010',
            ],
            // Neurology
            [
                'name' => 'Dr. Evelyn Foster',
                'specialty' => 'Neurology',
                'qualifications' => 'MD, PhD, FAAN (Clinical Neurology)',
                'experience_years' => 20,
                'consultation_room' => 'Neuro Lab 401',
                'consultation_duration' => 45,
                'email' => 'dr.foster@carepoint.com',
                'phone' => '+1 (555) 401-1011',
            ],
            [
                'name' => 'Dr. Samuel Green',
                'specialty' => 'Neurology',
                'qualifications' => 'MD, Neurology Board Certified',
                'experience_years' => 11,
                'consultation_room' => 'Neuro Lab 402',
                'consultation_duration' => 30,
                'email' => 'dr.green@carepoint.com',
                'phone' => '+1 (555) 401-1012',
            ],
            // Gynaecology
            [
                'name' => 'Dr. Hannah Morris',
                'specialty' => 'Gynaecology',
                'qualifications' => 'MD, FACOG (Obstetrics & Gynaecology)',
                'experience_years' => 14,
                'consultation_room' => 'Womens Clinic 210',
                'consultation_duration' => 30,
                'email' => 'dr.morris@carepoint.com',
                'phone' => '+1 (555) 401-1013',
            ],
            [
                'name' => 'Dr. Ananya Patel',
                'specialty' => 'Gynaecology',
                'qualifications' => 'MBBS, MS, DGO',
                'experience_years' => 13,
                'consultation_room' => 'Womens Clinic 211',
                'consultation_duration' => 30,
                'email' => 'dr.patel@carepoint.com',
                'phone' => '+1 (555) 401-1014',
            ],
            // ENT
            [
                'name' => 'Dr. Lucas Ward',
                'specialty' => 'ENT',
                'qualifications' => 'MS (Otolaryngology), FACS',
                'experience_years' => 15,
                'consultation_room' => 'ENT Wing 115',
                'consultation_duration' => 20,
                'email' => 'dr.ward@carepoint.com',
                'phone' => '+1 (555) 401-1015',
            ],
            [
                'name' => 'Dr. Grace Kelly',
                'specialty' => 'ENT',
                'qualifications' => 'MD, Otolaryngology Specialist',
                'experience_years' => 8,
                'consultation_room' => 'ENT Wing 116',
                'consultation_duration' => 20,
                'email' => 'dr.kelly@carepoint.com',
                'phone' => '+1 (555) 401-1016',
            ],
            // Ophthalmology
            [
                'name' => 'Dr. Arthur Mitchell',
                'specialty' => 'Ophthalmology',
                'qualifications' => 'MD, Ophth Board, Cornea Fellow',
                'experience_years' => 17,
                'consultation_room' => 'Eye Clinic 120',
                'consultation_duration' => 20,
                'email' => 'dr.mitchell@carepoint.com',
                'phone' => '+1 (555) 401-1017',
            ],
            [
                'name' => 'Dr. Diana Prince',
                'specialty' => 'Ophthalmology',
                'qualifications' => 'MBBS, DO, Glaucoma Specialist',
                'experience_years' => 9,
                'consultation_room' => 'Eye Clinic 121',
                'consultation_duration' => 20,
                'email' => 'dr.prince@carepoint.com',
                'phone' => '+1 (555) 401-1018',
            ],
            // Psychiatry
            [
                'name' => 'Dr. Thomas Wayne',
                'specialty' => 'Psychiatry',
                'qualifications' => 'MD, DFAPA (Neuropsychiatry)',
                'experience_years' => 22,
                'consultation_room' => 'Wellness Suite 410',
                'consultation_duration' => 45,
                'email' => 'dr.wayne@carepoint.com',
                'phone' => '+1 (555) 401-1019',
            ],
            [
                'name' => 'Dr. Samantha Cole',
                'specialty' => 'Psychiatry',
                'qualifications' => 'MD, Behavioral Health Specialist',
                'experience_years' => 7,
                'consultation_room' => 'Wellness Suite 411',
                'consultation_duration' => 45,
                'email' => 'dr.cole@carepoint.com',
                'phone' => '+1 (555) 401-1020',
            ],
            // Gastroenterology
            [
                'name' => 'Dr. Daniel Rivera',
                'specialty' => 'Gastroenterology',
                'qualifications' => 'MD, FACG (Gastrointestinal Medicine)',
                'experience_years' => 13,
                'consultation_room' => 'GI Center 220',
                'consultation_duration' => 30,
                'email' => 'dr.rivera@carepoint.com',
                'phone' => '+1 (555) 401-1021',
            ],
            [
                'name' => 'Dr. Olivia Taylor',
                'specialty' => 'Gastroenterology',
                'qualifications' => 'MD, Hepatology Fellowship',
                'experience_years' => 10,
                'consultation_room' => 'GI Center 221',
                'consultation_duration' => 30,
                'email' => 'dr.taylor@carepoint.com',
                'phone' => '+1 (555) 401-1022',
            ],
            // Pulmonology
            [
                'name' => 'Dr. Henry Cooper',
                'specialty' => 'Pulmonology',
                'qualifications' => 'MD, FCCP (Pulmonary & Critical Care)',
                'experience_years' => 19,
                'consultation_room' => 'Respiratory Clinic 230',
                'consultation_duration' => 30,
                'email' => 'dr.cooper@carepoint.com',
                'phone' => '+1 (555) 401-1023',
            ],
            [
                'name' => 'Dr. Stephanie Lin',
                'specialty' => 'Pulmonology',
                'qualifications' => 'MD, Asthma & Allergy Specialist',
                'experience_years' => 8,
                'consultation_room' => 'Respiratory Clinic 231',
                'consultation_duration' => 20,
                'email' => 'dr.lin@carepoint.com',
                'phone' => '+1 (555) 401-1024',
            ],
            // Endocrinology
            [
                'name' => 'Dr. Victor Frank',
                'specialty' => 'Endocrinology',
                'qualifications' => 'MD, FACE (Diabetes & Metabolism)',
                'experience_years' => 14,
                'consultation_room' => 'Metabolic Suite 310',
                'consultation_duration' => 30,
                'email' => 'dr.frank@carepoint.com',
                'phone' => '+1 (555) 401-1025',
            ],
            [
                'name' => 'Dr. Natalie Hughes',
                'specialty' => 'Endocrinology',
                'qualifications' => 'MD, Thyroid & Endocrinology',
                'experience_years' => 9,
                'consultation_room' => 'Metabolic Suite 311',
                'consultation_duration' => 20,
                'email' => 'dr.hughes@carepoint.com',
                'phone' => '+1 (555) 401-1026',
            ],
            // Urology
            [
                'name' => 'Dr. George Alvarez',
                'specialty' => 'Urology',
                'qualifications' => 'MD, FACS (Urological Surgery)',
                'experience_years' => 16,
                'consultation_room' => 'Uro Center 320',
                'consultation_duration' => 30,
                'email' => 'dr.alvarez@carepoint.com',
                'phone' => '+1 (555) 401-1027',
            ],
            [
                'name' => 'Dr. Jessica Parker',
                'specialty' => 'Urology',
                'qualifications' => 'MD, Urologic Oncology Fellow',
                'experience_years' => 11,
                'consultation_room' => 'Uro Center 321',
                'consultation_duration' => 30,
                'email' => 'dr.parker@carepoint.com',
                'phone' => '+1 (555) 401-1028',
            ],
            // General Surgery
            [
                'name' => 'Dr. Edward Norton',
                'specialty' => 'General Surgery',
                'qualifications' => 'MS, FRCS, FACS (General & Laparoscopic)',
                'experience_years' => 21,
                'consultation_room' => 'Surgical Suite 501',
                'consultation_duration' => 30,
                'email' => 'dr.norton@carepoint.com',
                'phone' => '+1 (555) 401-1029',
            ],
            [
                'name' => 'Dr. Laura Palmer',
                'specialty' => 'General Surgery',
                'qualifications' => 'MD, FACS, Minimally Invasive Surgery',
                'experience_years' => 12,
                'consultation_room' => 'Surgical Suite 502',
                'consultation_duration' => 30,
                'email' => 'dr.palmer@carepoint.com',
                'phone' => '+1 (555) 401-1030',
            ],
        ];

        $doctors = [];
        $docIndex = 1;

        foreach ($doctorsData as $d) {
            $code = 'DOC-' . str_pad($docIndex, 3, '0', STR_PAD_LEFT);
            $specialtyId = $specialtyMap[$d['specialty']];

            $doctor = Doctor::updateOrCreate(
                ['email' => $d['email']],
                [
                    'doctor_code' => $code,
                    'specialty_id' => $specialtyId,
                    'name' => $d['name'],
                    'phone' => $d['phone'],
                    'qualifications' => $d['qualifications'],
                    'experience_years' => $d['experience_years'],
                    'consultation_room' => $d['consultation_room'],
                    'consultation_duration' => $d['consultation_duration'],
                    'is_active' => true,
                    'bio' => "Experienced specialist with {$d['experience_years']} years practicing in {$d['specialty']}.",
                ]
            );

            // Configure Weekly Schedule:
            // Vary schedules: some Mon-Fri, some Mon-Sat, some Tue-Sat.
            // Doctor 10 (Dr. Brooks) will be off duty today for demo purposes
            // Doctor 15 (Dr. Ward) will have leave today for demo purposes
            $workingDays = match ($docIndex % 4) {
                0 => [1, 2, 3, 4, 5],       // Mon - Fri
                1 => [1, 2, 3, 4, 5, 6],    // Mon - Sat
                2 => [2, 3, 4, 5, 6],       // Tue - Sat
                3 => [1, 3, 5, 6],          // Mon, Wed, Fri, Sat
            };

            // If docIndex == 10, specifically make them off duty on today's day of week
            if ($docIndex === 10) {
                $workingDays = array_values(array_filter([0, 1, 2, 3, 4, 5, 6], fn($day) => $day !== $today->dayOfWeek));
            }

            foreach ($workingDays as $dayOfWeek) {
                // Vary working hours
                $start = match ($docIndex % 3) {
                    0 => '08:30:00',
                    1 => '09:00:00',
                    2 => '10:00:00',
                };
                $end = match ($docIndex % 3) {
                    0 => '16:30:00',
                    1 => '17:00:00',
                    2 => '18:00:00',
                };

                DoctorSchedule::updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day_of_week' => $dayOfWeek],
                    ['start_time' => $start, 'end_time' => $end, 'is_available' => true]
                );
            }

            // Lunch break: 13:00 to 14:00 (or 12:30 to 13:30)
            $breakStart = ($docIndex % 2 === 0) ? '13:00:00' : '12:30:00';
            $breakEnd = ($docIndex % 2 === 0) ? '14:00:00' : '13:30:00';

            DoctorBreak::updateOrCreate(
                ['doctor_id' => $doctor->id, 'day_of_week' => null, 'start_time' => $breakStart],
                ['end_time' => $breakEnd, 'label' => 'Midday Break']
            );

            $doctors[$docIndex] = $doctor;
            $docIndex++;
        }

        // Doctor 15 (Dr. Lucas Ward) on leave today
        DoctorUnavailability::updateOrCreate(
            ['doctor_id' => $doctors[15]->id, 'start_date' => $today->toDateString()],
            [
                'end_date' => $today->copy()->addDays(2)->toDateString(),
                'type' => 'leave',
                'reason' => 'Annual Medical Conference Attendance',
            ]
        );

        // Doctor 25 (Dr. Victor Frank) has afternoon emergency blocked tomorrow
        DoctorUnavailability::updateOrCreate(
            ['doctor_id' => $doctors[25]->id, 'start_date' => $today->copy()->addDay()->toDateString()],
            [
                'end_date' => $today->copy()->addDay()->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'type' => 'emergency',
                'reason' => 'Hospital Department Review Meeting',
            ]
        );

        // -------------------------------------------------------------
        // 5. 55 Fictional Patients
        // -------------------------------------------------------------
        $patientSamples = [
            ['name' => 'James Wilson', 'age' => 45, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 120, 'dia' => 80, 'complaint' => 'Persistent dry cough and mild chest tightness'],
            ['name' => 'Emma Watson', 'age' => 29, 'sex' => 'Female', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 115, 'dia' => 75, 'complaint' => 'Skin rash with redness and itching on forearm'],
            ['name' => 'Liam Smith', 'age' => 8, 'sex' => 'Male', 'blood' => 'B+', 'diabetic' => 'Non-Diabetic', 'sys' => 100, 'dia' => 65, 'complaint' => 'High fever, sore throat, and ear discomfort'],
            ['name' => 'Olivia Johnson', 'age' => 34, 'sex' => 'Female', 'blood' => 'AB+', 'diabetic' => 'Non-Diabetic', 'sys' => 122, 'dia' => 82, 'complaint' => 'Annual wellness checkup and routine blood work'],
            ['name' => 'Noah Brown', 'age' => 52, 'sex' => 'Male', 'blood' => 'O-', 'diabetic' => 'Diabetic', 'sys' => 135, 'dia' => 88, 'complaint' => 'Chronic lower back pain radiating down left thigh'],
            ['name' => 'Sophia Davis', 'age' => 41, 'sex' => 'Female', 'blood' => 'A-', 'diabetic' => 'Non-Diabetic', 'sys' => 118, 'dia' => 76, 'complaint' => 'Frequent migraines, visual aura, and light sensitivity'],
            ['name' => 'Lucas Miller', 'age' => 19, 'sex' => 'Male', 'blood' => 'B-', 'diabetic' => 'Non-Diabetic', 'sys' => 120, 'dia' => 78, 'complaint' => 'Sprained right ankle during soccer training'],
            ['name' => 'Mia Garcia', 'age' => 26, 'sex' => 'Female', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 110, 'dia' => 70, 'complaint' => 'Prenatal consultation and first trimester checkup'],
            ['name' => 'Ethan Martinez', 'age' => 63, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Diabetic', 'sys' => 142, 'dia' => 92, 'complaint' => 'Shortness of breath on exertion and mild ankle swelling'],
            ['name' => 'Ava Robinson', 'age' => 31, 'sex' => 'Female', 'blood' => 'Unknown', 'diabetic' => 'Unknown', 'sys' => 124, 'dia' => 80, 'complaint' => 'Chronic sinus congestion and post-nasal drip'],
            ['name' => 'Mason Clark', 'age' => 47, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 128, 'dia' => 84, 'complaint' => 'Heartburn, acid reflux, and epigastric discomfort after meals'],
            ['name' => 'Isabella Lewis', 'age' => 38, 'sex' => 'Female', 'blood' => 'B+', 'diabetic' => 'Diabetic', 'sys' => 130, 'dia' => 85, 'complaint' => 'Fatigue, unexplained weight fluctuation, and cold intolerance'],
            ['name' => 'Logan Lee', 'age' => 56, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 138, 'dia' => 86, 'complaint' => 'Blurred distance vision, eye strain, and headache while reading'],
            ['name' => 'Charlotte Walker', 'age' => 24, 'sex' => 'Female', 'blood' => 'O-', 'diabetic' => 'Non-Diabetic', 'sys' => 112, 'dia' => 72, 'complaint' => 'Severe acne breakout and eczema flare-up on cheeks'],
            ['name' => 'Alexander Hall', 'age' => 67, 'sex' => 'Male', 'blood' => 'AB-', 'diabetic' => 'Diabetic', 'sys' => 140, 'dia' => 90, 'complaint' => 'Frequent nighttime urination and weak urinary stream'],
            ['name' => 'Harper Allen', 'age' => 35, 'sex' => 'Female', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 120, 'dia' => 78, 'complaint' => 'Anxiety, insomnia, and stress management evaluation'],
            ['name' => 'Daniel Young', 'age' => 42, 'sex' => 'Male', 'blood' => 'B+', 'diabetic' => 'Non-Diabetic', 'sys' => 126, 'dia' => 82, 'complaint' => 'Recurrent knee stiffness after marathon running'],
            ['name' => 'Amelia Hernandez', 'age' => 16, 'sex' => 'Female', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 114, 'dia' => 74, 'complaint' => 'Seasonal allergies, watery eyes, and sneezing'],
            ['name' => 'Henry King', 'age' => 71, 'sex' => 'Male', 'blood' => 'A-', 'diabetic' => 'Diabetic', 'sys' => 148, 'dia' => 94, 'complaint' => 'Tremor in right hand, mild stiffness when walking'],
            ['name' => 'Evelyn Wright', 'age' => 50, 'sex' => 'Female', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 132, 'dia' => 85, 'complaint' => 'Abdominal bloating, suspected gallstones or indigestion'],
            ['name' => 'Sebastian Scott', 'age' => 39, 'sex' => 'Male', 'blood' => 'B-', 'diabetic' => 'Non-Diabetic', 'sys' => 122, 'dia' => 80, 'complaint' => 'Consultation for minor outpatient sebaceous cyst removal'],
            ['name' => 'Abigail Green', 'age' => 28, 'sex' => 'Female', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 116, 'dia' => 76, 'complaint' => 'Irregular menstrual cycle and hormonal evaluation'],
            ['name' => 'Jack Adams', 'age' => 11, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 105, 'dia' => 68, 'complaint' => 'Childhood asthma review and inhaler prescription renewal'],
            ['name' => 'Emily Baker', 'age' => 33, 'sex' => 'Female', 'blood' => 'AB+', 'diabetic' => 'Non-Diabetic', 'sys' => 118, 'dia' => 75, 'complaint' => 'Severe throbbing headache in left temple with nausea'],
            ['name' => 'Samuel Gonzalez', 'age' => 48, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 125, 'dia' => 80, 'complaint' => 'Sharp pain in shoulder joint upon lifting heavy objects'],
            ['name' => 'Ella Nelson', 'age' => 22, 'sex' => 'Female', 'blood' => 'O-', 'diabetic' => 'Non-Diabetic', 'sys' => 108, 'dia' => 70, 'complaint' => 'Flu symptoms, chills, dry cough, and fatigue for 3 days'],
            ['name' => 'David Carter', 'age' => 60, 'sex' => 'Male', 'blood' => 'B+', 'diabetic' => 'Diabetic', 'sys' => 136, 'dia' => 88, 'complaint' => 'Diabetic foot check and neuropathy screening'],
            ['name' => 'Avery Mitchell', 'age' => 44, 'sex' => 'Female', 'blood' => 'Unknown', 'diabetic' => 'Unknown', 'sys' => 120, 'dia' => 80, 'complaint' => 'Persistent hoarseness and throat tickle for two weeks'],
            ['name' => 'Carter Perez', 'age' => 17, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 115, 'dia' => 72, 'complaint' => 'School sports clearance physical and ECG check'],
            ['name' => 'Scarlett Roberts', 'age' => 37, 'sex' => 'Female', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 124, 'dia' => 78, 'complaint' => 'Palpitations and rapid heartbeat during stressful days'],
            ['name' => 'Wyatt Turner', 'age' => 54, 'sex' => 'Male', 'blood' => 'B-', 'diabetic' => 'Non-Diabetic', 'sys' => 130, 'dia' => 84, 'complaint' => 'Chronic neck stiffness from desk ergonomics'],
            ['name' => 'Grace Phillips', 'age' => 65, 'sex' => 'Female', 'blood' => 'A-', 'diabetic' => 'Non-Diabetic', 'sys' => 134, 'dia' => 82, 'complaint' => 'Osteoarthritis follow-up in both knees'],
            ['name' => 'Jayden Campbell', 'age' => 7, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 98, 'dia' => 64, 'complaint' => 'Recurring ear infections and hearing check'],
            ['name' => 'Chloe Parker', 'age' => 30, 'sex' => 'Female', 'blood' => 'AB-', 'diabetic' => 'Non-Diabetic', 'sys' => 114, 'dia' => 74, 'complaint' => 'Skin check for changing mole on shoulder'],
            ['name' => 'Gabriel Evans', 'age' => 49, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Diabetic', 'sys' => 144, 'dia' => 92, 'complaint' => 'Hypertension management and prescription adjustment'],
            ['name' => 'Penelope Edwards', 'age' => 27, 'sex' => 'Female', 'blood' => 'B+', 'diabetic' => 'Non-Diabetic', 'sys' => 112, 'dia' => 70, 'complaint' => 'Digestive upset and suspected lactose intolerance'],
            ['name' => 'Julian Collins', 'age' => 36, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 122, 'dia' => 80, 'complaint' => 'Inguinal hernia consultation for surgical repair'],
            ['name' => 'Layla Stewart', 'age' => 58, 'sex' => 'Female', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 128, 'dia' => 82, 'complaint' => 'Thyroid medication review and TSH lab check'],
            ['name' => 'Owen Sanchez', 'age' => 43, 'sex' => 'Male', 'blood' => 'O-', 'diabetic' => 'Non-Diabetic', 'sys' => 126, 'dia' => 80, 'complaint' => 'Wheezing and shortness of breath in humid weather'],
            ['name' => 'Riley Morris', 'age' => 32, 'sex' => 'Female', 'blood' => 'B-', 'diabetic' => 'Non-Diabetic', 'sys' => 116, 'dia' => 74, 'complaint' => 'Chronic pelvic discomfort and ultrasound review'],
            ['name' => 'Luke Rogers', 'age' => 68, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Diabetic', 'sys' => 146, 'dia' => 90, 'complaint' => 'Kidney stone history and follow-up renal ultrasound'],
            ['name' => 'Zoey Reed', 'age' => 21, 'sex' => 'Female', 'blood' => 'Unknown', 'diabetic' => 'Non-Diabetic', 'sys' => 110, 'dia' => 70, 'complaint' => 'College depression and anxiety screening'],
            ['name' => 'Caleb Cook', 'age' => 51, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 132, 'dia' => 86, 'complaint' => 'Carpal tunnel syndrome numbness in right wrist'],
            ['name' => 'Nora Morgan', 'age' => 40, 'sex' => 'Female', 'blood' => 'AB+', 'diabetic' => 'Non-Diabetic', 'sys' => 120, 'dia' => 78, 'complaint' => 'Routine eye pressure check and glaucoma screening'],
            ['name' => 'Ryan Bell', 'age' => 35, 'sex' => 'Male', 'blood' => 'A-', 'diabetic' => 'Non-Diabetic', 'sys' => 124, 'dia' => 80, 'complaint' => 'Swollen lymph nodes in neck and persistent fatigue'],
            ['name' => 'Lillian Murphy', 'age' => 62, 'sex' => 'Female', 'blood' => 'B+', 'diabetic' => 'Diabetic', 'sys' => 138, 'dia' => 86, 'complaint' => 'Type 2 Diabetes HbA1c review and insulin titration'],
            ['name' => 'Nathan Bailey', 'age' => 25, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 118, 'dia' => 76, 'complaint' => 'Food allergy reaction after eating shellfish'],
            ['name' => 'Hannah Rivera', 'age' => 46, 'sex' => 'Female', 'blood' => 'A+', 'diabetic' => 'Non-Diabetic', 'sys' => 122, 'dia' => 80, 'complaint' => 'Chronic constipation and gastrointestinal evaluation'],
            ['name' => 'Isaac Cooper', 'age' => 55, 'sex' => 'Male', 'blood' => 'O-', 'diabetic' => 'Non-Diabetic', 'sys' => 130, 'dia' => 84, 'complaint' => 'Post-operative wound inspection and suture removal'],
            ['name' => 'Stella Richardson', 'age' => 14, 'sex' => 'Female', 'blood' => 'B-', 'diabetic' => 'Non-Diabetic', 'sys' => 104, 'dia' => 68, 'complaint' => 'Adolescent acne evaluation and skincare plan'],
            ['name' => 'Andrew Cox', 'age' => 73, 'sex' => 'Male', 'blood' => 'A+', 'diabetic' => 'Diabetic', 'sys' => 150, 'dia' => 92, 'complaint' => 'Coronary artery disease checkup and ECG'],
            ['name' => 'Ellie Howard', 'age' => 38, 'sex' => 'Female', 'blood' => 'AB-', 'diabetic' => 'Non-Diabetic', 'sys' => 118, 'dia' => 76, 'complaint' => 'Chronic vertigo and inner ear balance evaluation'],
            ['name' => 'Christian Ward', 'age' => 47, 'sex' => 'Male', 'blood' => 'O+', 'diabetic' => 'Non-Diabetic', 'sys' => 126, 'dia' => 82, 'complaint' => 'Tennis elbow pain on lateral right elbow'],
            ['name' => 'Victoria Torres', 'age' => 29, 'sex' => 'Female', 'blood' => 'A-', 'diabetic' => 'Non-Diabetic', 'sys' => 112, 'dia' => 72, 'complaint' => 'Gynaecological consultation and Pap smear test'],
            ['name' => 'Hunter Peterson', 'age' => 59, 'sex' => 'Male', 'blood' => 'B+', 'diabetic' => 'Diabetic', 'sys' => 135, 'dia' => 88, 'complaint' => 'Chronic cough and emphysema monitoring'],
        ];

        $patients = [];
        $patIndex = 1;

        foreach ($patientSamples as $p) {
            $code = 'PAT-' . $today->format('Y') . '-' . str_pad($patIndex, 4, '0', STR_PAD_LEFT);
            $birthYear = (int)$today->format('Y') - $p['age'];
            $dob = Carbon::createFromDate($birthYear, ($patIndex % 12) + 1, ($patIndex % 25) + 1);
            $phone = '+1 (555) ' . str_pad(100 + $patIndex, 3, '0', STR_PAD_LEFT) . '-' . str_pad(2000 + $patIndex, 4, '0', STR_PAD_LEFT);
            $email = Str::slug($p['name']) . '@patient.carepoint.com';

            $patient = Patient::updateOrCreate(
                ['patient_code' => $code],
                [
                    'name' => $p['name'],
                    'date_of_birth' => $dob->toDateString(),
                    'age' => $p['age'],
                    'sex' => $p['sex'],
                    'phone' => $phone,
                    'email' => $email,
                    'address' => (100 + $patIndex * 3) . ' Maplewood St, Apartment ' . ($patIndex % 20 + 1) . ', Cityville',
                    'blood_group' => $p['blood'],
                    'diabetes_status' => $p['diabetic'],
                    'blood_pressure_systolic' => $p['sys'],
                    'blood_pressure_diastolic' => $p['dia'],
                    'allergies' => ($patIndex % 3 === 0) ? 'Penicillin, Dust mites' : (($patIndex % 5 === 0) ? 'Sulfa drugs' : 'None reported'),
                    'existing_conditions' => ($p['diabetic'] === 'Diabetic') ? 'Type 2 Diabetes Mellitus, Essential Hypertension' : 'None significant',
                    'current_medications' => ($p['diabetic'] === 'Diabetic') ? 'Metformin 500mg BID, Lisinopril 10mg daily' : 'Multivitamins',
                    'emergency_contact_name' => 'Next of Kin (' . $p['name'] . ')',
                    'emergency_contact_phone' => '+1 (555) 999-' . str_pad($patIndex, 4, '0', STR_PAD_LEFT),
                    'clinical_notes' => 'Patient registered at CarePoint Clinic reception.',
                ]
            );

            $patients[$patIndex] = $patient;
            $patIndex++;
        }

        // -------------------------------------------------------------
        // 6. Realistic Appointments (Past, Today, and Upcoming)
        // -------------------------------------------------------------
        $aptCounter = 1;

        // Helper closure to create appointment safely
        $createApt = function ($doctor, $patient, $date, $start, $end, $complaint, $status, $mode = 'auto', $alt = false) use (&$aptCounter, $defaultAdmin) {
            $code = 'APT-' . Carbon::parse($date)->format('Ymd') . '-' . str_pad($aptCounter, 4, '0', STR_PAD_LEFT);
            $aptCounter++;

            return Appointment::updateOrCreate(
                ['appointment_code' => $code],
                [
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'requested_date' => $date,
                    'requested_time' => $start,
                    'appointment_date' => $date,
                    'appointment_time' => $start,
                    'end_time' => $end,
                    'primary_complaint' => $complaint,
                    'status' => $status,
                    'booking_mode' => $mode,
                    'is_alternative_slot' => $alt,
                    'created_by' => $defaultAdmin->id,
                ]
            );
        };

        // A. Past Appointments (Yesterday and 2 days ago) - Completed / No Show / Cancelled
        $pastDate1 = $today->copy()->subDays(2)->toDateString();
        $pastDate2 = $today->copy()->subDay()->toDateString();

        $createApt($doctors[1], $patients[1], $pastDate1, '09:00:00', '09:20:00', 'Persistent dry cough', Appointment::STATUS_COMPLETED, 'auto');
        $createApt($doctors[1], $patients[2], $pastDate1, '09:20:00', '09:40:00', 'Routine physical examination', Appointment::STATUS_COMPLETED, 'doctor');
        $createApt($doctors[3], $patients[5], $pastDate1, '10:00:00', '10:30:00', 'Chest pain evaluation', Appointment::STATUS_COMPLETED, 'specialty');
        $createApt($doctors[4], $patients[9], $pastDate1, '11:00:00', '11:30:00', 'Shortness of breath on exertion', Appointment::STATUS_NO_SHOW, 'specialty');
        $createApt($doctors[7], $patients[7], $pastDate1, '14:00:00', '14:30:00', 'Knee ligament sprain follow-up', Appointment::STATUS_CANCELLED, 'doctor');

        $createApt($doctors[2], $patients[3], $pastDate2, '09:00:00', '09:20:00', 'Fever and throat irritation', Appointment::STATUS_COMPLETED, 'auto');
        $createApt($doctors[5], $patients[4], $pastDate2, '10:00:00', '10:20:00', 'Eczema flare-up on hands', Appointment::STATUS_COMPLETED, 'doctor');
        $createApt($doctors[8], $patients[6], $pastDate2, '11:00:00', '11:30:00', 'Lower back pain rehabilitation', Appointment::STATUS_COMPLETED, 'specialty');
        $createApt($doctors[11], $patients[8], $pastDate2, '14:00:00', '14:45:00', 'Numbness in facial nerve', Appointment::STATUS_COMPLETED, 'doctor');
        $createApt($doctors[13], $patients[10], $pastDate2, '15:00:00', '15:30:00', 'Gynaecological pelvic ultrasound review', Appointment::STATUS_CANCELLED, 'auto');

        // B. Today's Appointments (Demonstration scenarios)
        $todayStr = $today->toDateString();

        // SCENARIO 1: Doctor 1 (Dr. Robert Harrison) has realistic mix today
        // 09:00 - 09:20 (Completed)
        $createApt($doctors[1], $patients[11], $todayStr, '09:00:00', '09:20:00', 'Severe acid reflux and stomach cramps', Appointment::STATUS_COMPLETED, 'auto');
        // 09:20 - 09:40 (Completed)
        $createApt($doctors[1], $patients[12], $todayStr, '09:20:00', '09:40:00', 'Thyroid medication prescription review', Appointment::STATUS_COMPLETED, 'doctor');
        // 10:00 - 10:20 (In Consultation or Checked In)
        $createApt($doctors[1], $patients[13], $todayStr, '10:00:00', '10:20:00', 'High fever and viral chills', Appointment::STATUS_IN_CONSULTATION, 'auto');
        // 10:20 - 10:40 (Checked In - waiting in reception)
        $createApt($doctors[1], $patients[14], $todayStr, '10:20:00', '10:40:00', 'Blood pressure spike and dizziness', Appointment::STATUS_CHECKED_IN, 'specialty');
        // 11:00 - 11:20 (Scheduled - upcoming)
        $createApt($doctors[1], $patients[15], $todayStr, '11:00:00', '11:20:00', 'Urinary tract discomfort', Appointment::STATUS_SCHEDULED, 'auto');
        // 14:00 - 14:20 (Scheduled - afternoon)
        $createApt($doctors[1], $patients[16], $todayStr, '14:00:00', '14:20:00', 'Knee stiffness and joint swelling', Appointment::STATUS_SCHEDULED, 'doctor');

        // SCENARIO 2: Doctor 2 (Dr. Maria Santos) - FULLY BOOKED TODAY
        // Schedules from 09:00 to 17:00 with lunch 12:30-13:30, all slots filled!
        $santosSlots = [
            ['09:00:00', '09:20:00', 17, Appointment::STATUS_COMPLETED],
            ['09:20:00', '09:40:00', 18, Appointment::STATUS_COMPLETED],
            ['09:40:00', '10:00:00', 19, Appointment::STATUS_IN_CONSULTATION],
            ['10:00:00', '10:20:00', 20, Appointment::STATUS_CHECKED_IN],
            ['10:20:00', '10:40:00', 21, Appointment::STATUS_SCHEDULED],
            ['10:40:00', '11:00:00', 22, Appointment::STATUS_SCHEDULED],
            ['11:00:00', '11:20:00', 23, Appointment::STATUS_SCHEDULED],
            ['11:20:00', '11:40:00', 24, Appointment::STATUS_SCHEDULED],
            ['11:40:00', '12:00:00', 25, Appointment::STATUS_SCHEDULED],
            ['12:00:00', '12:20:00', 26, Appointment::STATUS_SCHEDULED],
            ['13:40:00', '14:00:00', 27, Appointment::STATUS_SCHEDULED],
            ['14:00:00', '14:20:00', 28, Appointment::STATUS_SCHEDULED],
            ['14:20:00', '14:40:00', 29, Appointment::STATUS_SCHEDULED],
            ['14:40:00', '15:00:00', 30, Appointment::STATUS_SCHEDULED],
            ['15:00:00', '15:20:00', 31, Appointment::STATUS_SCHEDULED],
            ['15:20:00', '15:40:00', 32, Appointment::STATUS_SCHEDULED],
            ['15:40:00', '16:00:00', 33, Appointment::STATUS_SCHEDULED],
            ['16:00:00', '16:20:00', 34, Appointment::STATUS_SCHEDULED],
            ['16:20:00', '16:40:00', 35, Appointment::STATUS_SCHEDULED],
            ['16:40:00', '17:00:00', 36, Appointment::STATUS_SCHEDULED],
        ];

        foreach ($santosSlots as $sl) {
            $createApt(
                $doctors[2],
                $patients[$sl[2]],
                $todayStr,
                $sl[0],
                $sl[1],
                $patients[$sl[2]]->clinical_notes ?? 'Clinical consultation',
                $sl[3],
                'doctor'
            );
        }

        // SCENARIO 3: Cardiology (Dr. Alexander Hayes)
        $createApt($doctors[3], $patients[37], $todayStr, '10:00:00', '10:30:00', 'Heart palpitations and chest tightness', Appointment::STATUS_CHECKED_IN, 'specialty');
        $createApt($doctors[3], $patients[38], $todayStr, '11:00:00', '11:30:00', 'Echocardiogram follow-up', Appointment::STATUS_SCHEDULED, 'doctor');
        $createApt($doctors[3], $patients[39], $todayStr, '15:00:00', '15:30:00', 'Post-stent cardiac evaluation', Appointment::STATUS_SCHEDULED, 'specialty');

        // SCENARIO 4: Dermatology (Dr. Chloe Bennett) - Has slots available
        $createApt($doctors[5], $patients[40], $todayStr, '11:00:00', '11:20:00', 'Severe skin rash and itching', Appointment::STATUS_SCHEDULED, 'specialty');
        $createApt($doctors[5], $patients[41], $todayStr, '14:20:00', '14:40:00', 'Suspicious mole inspection', Appointment::STATUS_CANCELLED, 'doctor');

        // SCENARIO 5: Paediatrics (Dr. Emily Thornton)
        $createApt($doctors[9], $patients[42], $todayStr, '10:00:00', '10:20:00', 'Childhood rash and fever', Appointment::STATUS_CHECKED_IN, 'specialty');
        $createApt($doctors[9], $patients[43], $todayStr, '11:20:00', '11:40:00', 'Routine paediatric wellness vaccine', Appointment::STATUS_SCHEDULED, 'auto');

        // C. Upcoming Appointments (Tomorrow, Next 3 days, Next 7 days)
        for ($day = 1; $day <= 7; $day++) {
            $upDate = $today->copy()->addDays($day)->toDateString();

            // Doctor 1
            $createApt($doctors[1], $patients[44], $upDate, '09:20:00', '09:40:00', 'General health follow-up', Appointment::STATUS_SCHEDULED, 'auto');
            $createApt($doctors[1], $patients[45], $upDate, '11:00:00', '11:20:00', 'Blood test results consultation', Appointment::STATUS_SCHEDULED, 'doctor');

            // Doctor 3 (Cardiology)
            $createApt($doctors[3], $patients[46], $upDate, '10:30:00', '11:00:00', 'Hypertension follow-up', Appointment::STATUS_SCHEDULED, 'specialty');

            // Doctor 7 (Orthopaedics)
            $createApt($doctors[7], $patients[47], $upDate, '14:00:00', '14:30:00', 'Post-fracture rehabilitation check', Appointment::STATUS_SCHEDULED, 'doctor');

            // Doctor 11 (Neurology)
            $createApt($doctors[11], $patients[48], $upDate, '11:00:00', '11:45:00', 'Chronic migraine treatment plan', Appointment::STATUS_SCHEDULED, 'specialty');

            // Doctor 21 (Gastroenterology)
            if ($day % 2 === 0) {
                $createApt($doctors[21], $patients[49], $upDate, '10:00:00', '10:30:00', 'Endoscopy preparation consultation', Appointment::STATUS_SCHEDULED, 'auto');
            }

            // Doctor 27 (Urology)
            if ($day % 3 === 0) {
                $createApt($doctors[27], $patients[50], $upDate, '14:30:00', '15:00:00', 'Kidney health check', Appointment::STATUS_SCHEDULED, 'specialty');
            }
        }

        // -------------------------------------------------------------
        // 7. Seed Symptom-to-Specialty Intent Rules & Urgent Safeguards
        // -------------------------------------------------------------
        $this->call(SymptomRuleSeeder::class);

        // -------------------------------------------------------------
        // 8. Configure Round-Robin 24/7 Doctor Roster (2 on duty per specialty at all times)
        // -------------------------------------------------------------
        $this->call(RoundRobinDoctorRosterSeeder::class);
    }
}
