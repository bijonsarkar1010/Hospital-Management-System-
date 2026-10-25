<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailability;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoundRobinDoctorRosterSeeder extends Seeder
{
    /**
     * Run the round-robin doctor roster seeder.
     * Ensures every specialty has at least 4 doctors, and exactly 2 doctors
     * from each specialty are scheduled on duty at ANY given hour of any day.
     */
    public function run(): void
    {
        $today = Carbon::today();

        // 1. Configure Clinic to 24/7 Operations
        ClinicSetting::set('clinic_open_time', '00:00');
        ClinicSetting::set('clinic_close_time', '23:59:59');

        // 2. Specialty & Doctor Data (4 Doctors per Specialty x 15 Specialties = 60 Doctors)
        $rosterData = [
            'General Medicine' => [
                [
                    'name' => 'Dr. Robert Harrison',
                    'email' => 'dr.harrison@carepoint.com',
                    'phone' => '+1 (555) 401-1001',
                    'qualifications' => 'MD, FACP (Internal Medicine)',
                    'experience_years' => 14,
                    'consultation_room' => 'Consultation Suite 101',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Maria Santos',
                    'email' => 'dr.santos@carepoint.com',
                    'phone' => '+1 (555) 401-1002',
                    'qualifications' => 'MBBS, MRCGP (Family Practice)',
                    'experience_years' => 9,
                    'consultation_room' => 'Consultation Suite 102',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Arthur Bennett',
                    'email' => 'dr.bennett.gm@carepoint.com',
                    'phone' => '+1 (555) 401-1101',
                    'qualifications' => 'MD, Primary Healthcare Specialist',
                    'experience_years' => 12,
                    'consultation_room' => 'Consultation Suite 103',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Elena Rostova',
                    'email' => 'dr.rostova@carepoint.com',
                    'phone' => '+1 (555) 401-1102',
                    'qualifications' => 'MBBS, Family Medicine Certified',
                    'experience_years' => 8,
                    'consultation_room' => 'Consultation Suite 104',
                    'consultation_duration' => 20,
                ],
            ],

            'Cardiology' => [
                [
                    'name' => 'Dr. Alexander Hayes',
                    'email' => 'dr.hayes@carepoint.com',
                    'phone' => '+1 (555) 401-1003',
                    'qualifications' => 'MD, FACC (Interventional Cardiology)',
                    'experience_years' => 18,
                    'consultation_room' => 'Cardio Care 201',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Priya Sharma',
                    'email' => 'dr.sharma@carepoint.com',
                    'phone' => '+1 (555) 401-1004',
                    'qualifications' => 'MD, DM (Clinical Cardiology)',
                    'experience_years' => 11,
                    'consultation_room' => 'Cardio Care 202',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Marcus Sterling',
                    'email' => 'dr.sterling@carepoint.com',
                    'phone' => '+1 (555) 401-1103',
                    'qualifications' => 'MD, FACC, Electrophysiology',
                    'experience_years' => 15,
                    'consultation_room' => 'Cardio Care 203',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Clara Oswald',
                    'email' => 'dr.oswald@carepoint.com',
                    'phone' => '+1 (555) 401-1104',
                    'qualifications' => 'MBBS, MRCP (Cardiology)',
                    'experience_years' => 7,
                    'consultation_room' => 'Cardio Care 204',
                    'consultation_duration' => 30,
                ],
            ],

            'Dermatology' => [
                [
                    'name' => 'Dr. Chloe Bennett',
                    'email' => 'dr.bennett@carepoint.com',
                    'phone' => '+1 (555) 401-1005',
                    'qualifications' => 'MD, FAAD (Dermatology & Aesthetics)',
                    'experience_years' => 10,
                    'consultation_room' => 'Derma Clinic 105',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Marcus Vance',
                    'email' => 'dr.m.vance@carepoint.com',
                    'phone' => '+1 (555) 401-1006',
                    'qualifications' => 'MBBS, DVD (Medical Dermatology)',
                    'experience_years' => 15,
                    'consultation_room' => 'Derma Clinic 106',
                    'consultation_duration' => 15,
                ],
                [
                    'name' => 'Dr. Sofia Al-Mansoor',
                    'email' => 'dr.almansoor@carepoint.com',
                    'phone' => '+1 (555) 401-1105',
                    'qualifications' => 'MD, Pediatric & Clinical Dermatology',
                    'experience_years' => 11,
                    'consultation_room' => 'Derma Clinic 107',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Julian Thorne',
                    'email' => 'dr.thorne@carepoint.com',
                    'phone' => '+1 (555) 401-1106',
                    'qualifications' => 'MD, Dermatopathology',
                    'experience_years' => 13,
                    'consultation_room' => 'Derma Clinic 108',
                    'consultation_duration' => 20,
                ],
            ],

            'Orthopaedics' => [
                [
                    'name' => 'Dr. Jonathan Reed',
                    'email' => 'dr.reed@carepoint.com',
                    'phone' => '+1 (555) 401-1007',
                    'qualifications' => 'MS, FRCS (Orthopaedic Surgery)',
                    'experience_years' => 16,
                    'consultation_room' => 'Ortho Center 301',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Rachel Kim',
                    'email' => 'dr.kim@carepoint.com',
                    'phone' => '+1 (555) 401-1008',
                    'qualifications' => 'MD, Sports Medicine Fellowship',
                    'experience_years' => 8,
                    'consultation_room' => 'Ortho Center 302',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Liam Gallagher',
                    'email' => 'dr.gallagher.ortho@carepoint.com',
                    'phone' => '+1 (555) 401-1107',
                    'qualifications' => 'MS, Joint Replacement Specialist',
                    'experience_years' => 14,
                    'consultation_room' => 'Ortho Center 303',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Maya Lin',
                    'email' => 'dr.lin.ortho@carepoint.com',
                    'phone' => '+1 (555) 401-1108',
                    'qualifications' => 'MD, Spine & Musculoskeletal Specialist',
                    'experience_years' => 10,
                    'consultation_room' => 'Ortho Center 304',
                    'consultation_duration' => 30,
                ],
            ],

            'Paediatrics' => [
                [
                    'name' => 'Dr. Sarah Lin',
                    'email' => 'dr.s.lin@carepoint.com',
                    'phone' => '+1 (555) 401-1009',
                    'qualifications' => 'MD, FAAP (General Paediatrics)',
                    'experience_years' => 12,
                    'consultation_room' => 'Pediatric Wing 110',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. David Chen',
                    'email' => 'dr.chen@carepoint.com',
                    'phone' => '+1 (555) 401-1010',
                    'qualifications' => 'MBBS, DCH, Neonatal Care Fellow',
                    'experience_years' => 6,
                    'consultation_room' => 'Pediatric Wing 111',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Benjamin Foster',
                    'email' => 'dr.foster@carepoint.com',
                    'phone' => '+1 (555) 401-1109',
                    'qualifications' => 'MD, Adolescent Medicine',
                    'experience_years' => 15,
                    'consultation_room' => 'Pediatric Wing 112',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Zoe Saldana',
                    'email' => 'dr.saldana@carepoint.com',
                    'phone' => '+1 (555) 401-1110',
                    'qualifications' => 'MBBS, MD (Pediatrics)',
                    'experience_years' => 9,
                    'consultation_room' => 'Pediatric Wing 113',
                    'consultation_duration' => 20,
                ],
            ],

            'Neurology' => [
                [
                    'name' => 'Dr. James Wilson',
                    'email' => 'dr.wilson@carepoint.com',
                    'phone' => '+1 (555) 401-1011',
                    'qualifications' => 'MD, PhD, FAAN (Clinical Neurology)',
                    'experience_years' => 20,
                    'consultation_room' => 'Neuro Lab 401',
                    'consultation_duration' => 45,
                ],
                [
                    'name' => 'Dr. Emily Green',
                    'email' => 'dr.green@carepoint.com',
                    'phone' => '+1 (555) 401-1012',
                    'qualifications' => 'MBBS, DM (Neurology)',
                    'experience_years' => 10,
                    'consultation_room' => 'Neuro Lab 402',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Sanjay Gupta',
                    'email' => 'dr.gupta@carepoint.com',
                    'phone' => '+1 (555) 401-1111',
                    'qualifications' => 'MD, Cerebrovascular & Stroke Fellow',
                    'experience_years' => 17,
                    'consultation_room' => 'Neuro Lab 403',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Beatrice Vane',
                    'email' => 'dr.vane@carepoint.com',
                    'phone' => '+1 (555) 401-1112',
                    'qualifications' => 'MBBS, MRCP (Neurology)',
                    'experience_years' => 8,
                    'consultation_room' => 'Neuro Lab 404',
                    'consultation_duration' => 30,
                ],
            ],

            'Gynaecology' => [
                [
                    'name' => 'Dr. Hannah Morris',
                    'email' => 'dr.morris@carepoint.com',
                    'phone' => '+1 (555) 401-1013',
                    'qualifications' => 'MD, FACOG (Obstetrics & Gynaecology)',
                    'experience_years' => 14,
                    'consultation_room' => 'Womens Clinic 210',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Ananya Patel',
                    'email' => 'dr.patel@carepoint.com',
                    'phone' => '+1 (555) 401-1014',
                    'qualifications' => 'MBBS, MS, DGO',
                    'experience_years' => 13,
                    'consultation_room' => 'Womens Clinic 211',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Catherine Bell',
                    'email' => 'dr.bell@carepoint.com',
                    'phone' => '+1 (555) 401-1113',
                    'qualifications' => 'MD, Maternal-Fetal Medicine',
                    'experience_years' => 16,
                    'consultation_room' => 'Womens Clinic 212',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Fiona Gallagher',
                    'email' => 'dr.gallagher.gyn@carepoint.com',
                    'phone' => '+1 (555) 401-1114',
                    'qualifications' => 'MBBS, Reproductive Endocrinology',
                    'experience_years' => 9,
                    'consultation_room' => 'Womens Clinic 213',
                    'consultation_duration' => 30,
                ],
            ],

            'ENT' => [
                [
                    'name' => 'Dr. Lucas Ward',
                    'email' => 'dr.ward@carepoint.com',
                    'phone' => '+1 (555) 401-1015',
                    'qualifications' => 'MS (Otolaryngology), FACS',
                    'experience_years' => 15,
                    'consultation_room' => 'ENT Wing 115',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Grace Kelly',
                    'email' => 'dr.kelly@carepoint.com',
                    'phone' => '+1 (555) 401-1016',
                    'qualifications' => 'MD, Otolaryngology Specialist',
                    'experience_years' => 8,
                    'consultation_room' => 'ENT Wing 116',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Oscar Isaac',
                    'email' => 'dr.isaac@carepoint.com',
                    'phone' => '+1 (555) 401-1115',
                    'qualifications' => 'MS (ENT), Head & Neck Surgery',
                    'experience_years' => 12,
                    'consultation_room' => 'ENT Wing 117',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Tara Maclay',
                    'email' => 'dr.maclay@carepoint.com',
                    'phone' => '+1 (555) 401-1116',
                    'qualifications' => 'MD, Pediatric Otolaryngology',
                    'experience_years' => 7,
                    'consultation_room' => 'ENT Wing 118',
                    'consultation_duration' => 20,
                ],
            ],

            'Ophthalmology' => [
                [
                    'name' => 'Dr. Arthur Mitchell',
                    'email' => 'dr.mitchell@carepoint.com',
                    'phone' => '+1 (555) 401-1017',
                    'qualifications' => 'MD, Ophth Board, Cornea Fellow',
                    'experience_years' => 17,
                    'consultation_room' => 'Eye Clinic 120',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Diana Prince',
                    'email' => 'dr.prince@carepoint.com',
                    'phone' => '+1 (555) 401-1018',
                    'qualifications' => 'MBBS, DO, Glaucoma Specialist',
                    'experience_years' => 9,
                    'consultation_room' => 'Eye Clinic 121',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Iris West',
                    'email' => 'dr.west@carepoint.com',
                    'phone' => '+1 (555) 401-1117',
                    'qualifications' => 'MD, Retinal Surgery Fellow',
                    'experience_years' => 14,
                    'consultation_room' => 'Eye Clinic 122',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Clark Kent',
                    'email' => 'dr.kent@carepoint.com',
                    'phone' => '+1 (555) 401-1118',
                    'qualifications' => 'MBBS, Comprehensive Ophthalmology',
                    'experience_years' => 8,
                    'consultation_room' => 'Eye Clinic 123',
                    'consultation_duration' => 20,
                ],
            ],

            'Psychiatry' => [
                [
                    'name' => 'Dr. Thomas Wayne',
                    'email' => 'dr.wayne@carepoint.com',
                    'phone' => '+1 (555) 401-1019',
                    'qualifications' => 'MD, DFAPA (Neuropsychiatry)',
                    'experience_years' => 22,
                    'consultation_room' => 'Wellness Suite 410',
                    'consultation_duration' => 45,
                ],
                [
                    'name' => 'Dr. Samantha Cole',
                    'email' => 'dr.cole@carepoint.com',
                    'phone' => '+1 (555) 401-1020',
                    'qualifications' => 'MD, Behavioral Health Specialist',
                    'experience_years' => 7,
                    'consultation_room' => 'Wellness Suite 411',
                    'consultation_duration' => 45,
                ],
                [
                    'name' => 'Dr. Jonathan Crane',
                    'email' => 'dr.crane@carepoint.com',
                    'phone' => '+1 (555) 401-1119',
                    'qualifications' => 'MD, Anxiety & Mood Disorders',
                    'experience_years' => 16,
                    'consultation_room' => 'Wellness Suite 412',
                    'consultation_duration' => 45,
                ],
                [
                    'name' => 'Dr. Harleen Quinzel',
                    'email' => 'dr.quinzel@carepoint.com',
                    'phone' => '+1 (555) 401-1120',
                    'qualifications' => 'PhD, MD, Clinical Psychology & Psychiatry',
                    'experience_years' => 10,
                    'consultation_room' => 'Wellness Suite 413',
                    'consultation_duration' => 45,
                ],
            ],

            'Gastroenterology' => [
                [
                    'name' => 'Dr. Daniel Rivera',
                    'email' => 'dr.rivera@carepoint.com',
                    'phone' => '+1 (555) 401-1021',
                    'qualifications' => 'MD, FACG (Gastrointestinal Medicine)',
                    'experience_years' => 13,
                    'consultation_room' => 'GI Center 220',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Olivia Taylor',
                    'email' => 'dr.taylor@carepoint.com',
                    'phone' => '+1 (555) 401-1022',
                    'qualifications' => 'MD, Hepatology Fellowship',
                    'experience_years' => 10,
                    'consultation_room' => 'GI Center 221',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Gregory House',
                    'email' => 'dr.house@carepoint.com',
                    'phone' => '+1 (555) 401-1121',
                    'qualifications' => 'MD, Internal & Diagnostic Gastroenterology',
                    'experience_years' => 20,
                    'consultation_room' => 'GI Center 222',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Allison Cameron',
                    'email' => 'dr.cameron@carepoint.com',
                    'phone' => '+1 (555) 401-1122',
                    'qualifications' => 'MD, Immunology & GI Disorders',
                    'experience_years' => 8,
                    'consultation_room' => 'GI Center 223',
                    'consultation_duration' => 30,
                ],
            ],

            'Pulmonology' => [
                [
                    'name' => 'Dr. Henry Cooper',
                    'email' => 'dr.cooper@carepoint.com',
                    'phone' => '+1 (555) 401-1023',
                    'qualifications' => 'MD, FCCP (Pulmonary & Critical Care)',
                    'experience_years' => 19,
                    'consultation_room' => 'Respiratory Clinic 230',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Stephanie Lin',
                    'email' => 'dr.lin@carepoint.com',
                    'phone' => '+1 (555) 401-1024',
                    'qualifications' => 'MD, Asthma & Allergy Specialist',
                    'experience_years' => 8,
                    'consultation_room' => 'Respiratory Clinic 231',
                    'consultation_duration' => 20,
                ],
                [
                    'name' => 'Dr. Eric Foreman',
                    'email' => 'dr.foreman@carepoint.com',
                    'phone' => '+1 (555) 401-1123',
                    'qualifications' => 'MD, Pulmonary Function & Critical Care',
                    'experience_years' => 12,
                    'consultation_room' => 'Respiratory Clinic 232',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Lisa Cuddy',
                    'email' => 'dr.cuddy@carepoint.com',
                    'phone' => '+1 (555) 401-1124',
                    'qualifications' => 'MD, Respiratory Medicine & Endocrinology',
                    'experience_years' => 16,
                    'consultation_room' => 'Respiratory Clinic 233',
                    'consultation_duration' => 30,
                ],
            ],

            'Endocrinology' => [
                [
                    'name' => 'Dr. Victor Frank',
                    'email' => 'dr.frank@carepoint.com',
                    'phone' => '+1 (555) 401-1025',
                    'qualifications' => 'MD, FACE (Diabetes & Metabolism)',
                    'experience_years' => 14,
                    'consultation_room' => 'Metabolic Suite 310',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Natalie Brooks',
                    'email' => 'dr.brooks@carepoint.com',
                    'phone' => '+1 (555) 401-1026',
                    'qualifications' => 'MBBS, MRCP (Endocrinology & Thyroid)',
                    'experience_years' => 11,
                    'consultation_room' => 'Metabolic Suite 311',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Robert Chase',
                    'email' => 'dr.chase@carepoint.com',
                    'phone' => '+1 (555) 401-1125',
                    'qualifications' => 'MD, Intensive Metabolic Management',
                    'experience_years' => 11,
                    'consultation_room' => 'Metabolic Suite 312',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Remy Hadley',
                    'email' => 'dr.hadley@carepoint.com',
                    'phone' => '+1 (555) 401-1126',
                    'qualifications' => 'MD, Endocrine Oncology Fellowship',
                    'experience_years' => 9,
                    'consultation_room' => 'Metabolic Suite 313',
                    'consultation_duration' => 30,
                ],
            ],

            'Urology' => [
                [
                    'name' => 'Dr. Samuel Jackson',
                    'email' => 'dr.jackson@carepoint.com',
                    'phone' => '+1 (555) 401-1027',
                    'qualifications' => 'MD, FACS (Urological Surgery)',
                    'experience_years' => 18,
                    'consultation_room' => 'Urology Clinic 320',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Rebecca White',
                    'email' => 'dr.white@carepoint.com',
                    'phone' => '+1 (555) 401-1028',
                    'qualifications' => 'MBBS, MS (Urology), Endourology Fellow',
                    'experience_years' => 9,
                    'consultation_room' => 'Urology Clinic 321',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Chris Turk',
                    'email' => 'dr.turk@carepoint.com',
                    'phone' => '+1 (555) 401-1127',
                    'qualifications' => 'MD, Minimally Invasive Urology Surgery',
                    'experience_years' => 13,
                    'consultation_room' => 'Urology Clinic 322',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. John Dorian',
                    'email' => 'dr.dorian@carepoint.com',
                    'phone' => '+1 (555) 401-1128',
                    'qualifications' => 'MD, Renal & Urinary Healthcare',
                    'experience_years' => 10,
                    'consultation_room' => 'Urology Clinic 323',
                    'consultation_duration' => 30,
                ],
            ],

            'General Surgery' => [
                [
                    'name' => 'Dr. Edward Norton',
                    'email' => 'dr.norton@carepoint.com',
                    'phone' => '+1 (555) 401-1029',
                    'qualifications' => 'MS, FRCS, FACS (General & Laparoscopic)',
                    'experience_years' => 21,
                    'consultation_room' => 'Surgical Suite 501',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Laura Palmer',
                    'email' => 'dr.palmer@carepoint.com',
                    'phone' => '+1 (555) 401-1030',
                    'qualifications' => 'MD, FACS, Minimally Invasive Surgery',
                    'experience_years' => 12,
                    'consultation_room' => 'Surgical Suite 502',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Meredith Grey',
                    'email' => 'dr.grey@carepoint.com',
                    'phone' => '+1 (555) 401-1129',
                    'qualifications' => 'MD, FACS, Advanced Laparoscopy & General Surgery',
                    'experience_years' => 15,
                    'consultation_room' => 'Surgical Suite 503',
                    'consultation_duration' => 30,
                ],
                [
                    'name' => 'Dr. Derek Shepherd',
                    'email' => 'dr.shepherd@carepoint.com',
                    'phone' => '+1 (555) 401-1130',
                    'qualifications' => 'MD, FACS, Senior Surgical Specialist',
                    'experience_years' => 18,
                    'consultation_room' => 'Surgical Suite 504',
                    'consultation_duration' => 30,
                ],
            ],
        ];

        // 3. Clear any obsolete unavailabilities for today so on-duty doctors aren't blocked
        DoctorUnavailability::whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->delete();

        $docGlobalIndex = 1;

        foreach ($rosterData as $specialtyName => $doctorsInSpec) {
            $specialty = Specialty::where('name', $specialtyName)->firstOrFail();
            $specialtyDoctors = [];

            // Create or update 4 doctors per specialty
            foreach ($doctorsInSpec as $d) {
                $existingDoc = Doctor::where('email', $d['email'])->first();
                if ($existingDoc) {
                    $code = $existingDoc->doctor_code;
                } else {
                    $maxNum = Doctor::count() + 1;
                    $code = 'DOC-' . str_pad($maxNum, 3, '0', STR_PAD_LEFT);
                    while (Doctor::where('doctor_code', $code)->exists()) {
                        $maxNum++;
                        $code = 'DOC-' . str_pad($maxNum, 3, '0', STR_PAD_LEFT);
                    }
                }

                $doctor = Doctor::updateOrCreate(
                    ['email' => $d['email']],
                    [
                        'doctor_code' => $code,
                        'specialty_id' => $specialty->id,
                        'name' => $d['name'],
                        'phone' => $d['phone'],
                        'qualifications' => $d['qualifications'],
                        'experience_years' => $d['experience_years'],
                        'consultation_room' => $d['consultation_room'],
                        'consultation_duration' => $d['consultation_duration'],
                        'is_active' => true,
                        'bio' => "Distinguished specialist with {$d['experience_years']} years clinical practice in {$specialtyName}.",
                    ]
                );

                $specialtyDoctors[] = $doctor;
                $docGlobalIndex++;
            }

            // 4. Assign Round-Robin Weekly Schedules (7 Days x 2 Shifts x at least 2 Doctors)
            // Shift 1: 00:00:00 to 12:30:00 (covers midnight, early morning, morning)
            // Shift 2: 11:30:00 to 23:59:59 (covers noon, afternoon, evening, night)
            // There is a 1-hour overlap (11:30 - 12:30) for smooth shift handover.
            // At ANY second of the day, AT LEAST 2 doctors from this specialty are ON DUTY.
            $allActiveDoctorsInSpec = $specialty->doctors()->where('is_active', true)->get();
            $numDoctors = $allActiveDoctorsInSpec->count();

            // Clear old schedules for all doctors in this specialty
            DoctorSchedule::whereIn('doctor_id', $allActiveDoctorsInSpec->pluck('id'))->delete();
            DoctorBreak::whereIn('doctor_id', $allActiveDoctorsInSpec->pluck('id'))->delete();

            for ($dayOfWeek = 0; $dayOfWeek <= 6; $dayOfWeek++) {
                // Round robin indices for this day
                $shift1DocIndex1 = ($dayOfWeek * 2 + 0) % $numDoctors;
                $shift1DocIndex2 = ($dayOfWeek * 2 + 1) % $numDoctors;
                $shift2DocIndex1 = ($dayOfWeek * 2 + 2) % $numDoctors;
                $shift2DocIndex2 = ($dayOfWeek * 2 + 3) % $numDoctors;

                $shift1Doc1 = $allActiveDoctorsInSpec[$shift1DocIndex1];
                $shift1Doc2 = $allActiveDoctorsInSpec[$shift1DocIndex2];
                $shift2Doc1 = $allActiveDoctorsInSpec[$shift2DocIndex1];
                $shift2Doc2 = $allActiveDoctorsInSpec[$shift2DocIndex2];

                // Schedule Shift 1 doctors
                DoctorSchedule::create([
                    'doctor_id' => $shift1Doc1->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '00:00:00',
                    'end_time' => '12:30:00',
                    'is_available' => true,
                ]);

                DoctorSchedule::create([
                    'doctor_id' => $shift1Doc2->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '00:00:00',
                    'end_time' => '12:30:00',
                    'is_available' => true,
                ]);

                // Schedule Shift 2 doctors
                DoctorSchedule::create([
                    'doctor_id' => $shift2Doc1->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '11:30:00',
                    'end_time' => '23:59:59',
                    'is_available' => true,
                ]);

                DoctorSchedule::create([
                    'doctor_id' => $shift2Doc2->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '11:30:00',
                    'end_time' => '23:59:59',
                    'is_available' => true,
                ]);

                // Any other doctors in this specialty on this day are on off-duty rest
                $assignedIds = [$shift1Doc1->id, $shift1Doc2->id, $shift2Doc1->id, $shift2Doc2->id];
                foreach ($allActiveDoctorsInSpec as $otherDoc) {
                    if (!in_array($otherDoc->id, $assignedIds)) {
                        DoctorSchedule::create([
                            'doctor_id' => $otherDoc->id,
                            'day_of_week' => $dayOfWeek,
                            'start_time' => '09:00:00',
                            'end_time' => '17:00:00',
                            'is_available' => false,
                        ]);
                    }
                }
            }

            // 5. Staggered Breaks (Staggered so doctors in the same specialty never overlap)
            foreach ($allActiveDoctorsInSpec as $idx => $doc) {
                $startHour = ($idx % 2 === 0) ? 4 : 5; // 04:00 or 05:00
                if ($idx >= 2) {
                    $startHour = ($idx % 2 === 0) ? 15 : 17; // 15:00 or 17:00
                }
                $startH = str_pad($startHour, 2, '0', STR_PAD_LEFT);
                DoctorBreak::create([
                    'doctor_id' => $doc->id,
                    'day_of_week' => null,
                    'start_time' => "{$startH}:00:00",
                    'end_time' => "{$startH}:45:00",
                    'label' => 'Rest Break',
                ]);
            }
        }
    }
}
