<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\SymptomRule;
use Illuminate\Database\Seeder;

class SymptomRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = Specialty::all()->keyBy('name');

        $rules = [
            // ---------------------------------------------------------
            // EMERGENCY / URGENT SAFEGUARDS (Highest Priority, Red-Flag)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Severe chest pain with breathlessness',
                'intent' => 'Emergency Acute Coronary / Respiratory Failure',
                'specialty' => 'Cardiology',
                'keywords' => [
                    'severe chest pain',
                    'chest pain with breathlessness',
                    'crushing chest pain',
                    'chest tightness and difficulty breathing',
                    'chest pain radiating to arm',
                    'chest pain radiating to jaw',
                    'acute chest pressure',
                ],
                'synonyms' => ['heart attack symptoms', 'myocardial infarction', 'angina pectoris'],
                'priority' => 100,
                'is_urgent' => true,
                'urgent_instructions' => 'CRITICAL EMERGENCY WARNING: Potential Acute Coronary Syndrome. Do not process as a routine outpatient booking. Direct patient immediately to the Emergency Department or Call Emergency Medical Services (911/112).',
            ],
            [
                'symptom_entity' => 'Acute stroke symptoms (FAST)',
                'intent' => 'Emergency Acute Cerebrovascular Event',
                'specialty' => 'Neurology',
                'keywords' => [
                    'facial droop',
                    'sudden arm weakness',
                    'slurred speech',
                    'sudden numbness on one side',
                    'loss of speech',
                    'sudden inability to walk',
                    'sudden confusion',
                ],
                'synonyms' => ['stroke', 'brain attack', 'transient ischemic attack', 'tia', 'hemiplegia'],
                'priority' => 100,
                'is_urgent' => true,
                'urgent_instructions' => 'CRITICAL EMERGENCY WARNING: Potential Acute Stroke. Immediate thrombolysis window required. Direct patient immediately to the nearest Stroke / Emergency Medical Center.',
            ],
            [
                'symptom_entity' => 'Severe uncontrolled bleeding',
                'intent' => 'Emergency Trauma & Hemorrhage',
                'specialty' => 'General Surgery',
                'keywords' => [
                    'severe bleeding',
                    'arterial bleeding',
                    'uncontrolled bleeding',
                    'heavy continuous bleeding',
                    'deep laceration bleeding profusely',
                    'coughing large amounts of blood',
                ],
                'synonyms' => ['hemorrhage', 'active bleeding', 'exsanguination'],
                'priority' => 95,
                'is_urgent' => true,
                'urgent_instructions' => 'CRITICAL EMERGENCY WARNING: Active severe hemorrhage. Apply direct pressure with sterile dressing and initiate immediate emergency trauma transfer.',
            ],

            // ---------------------------------------------------------
            // DERMATOLOGY (Skin Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Skin rash',
                'intent' => 'Skin complaint',
                'specialty' => 'Dermatology',
                'keywords' => ['skin rash', 'rash', 'hives', 'urticaria', 'red skin patches', 'skin lesion', 'blisters', 'measles-like rash'],
                'synonyms' => ['erythema', 'dermatitis', 'exanthem'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Itchy skin',
                'intent' => 'Skin complaint',
                'specialty' => 'Dermatology',
                'keywords' => ['itchy skin', 'itching', 'severe itch', 'pruritus', 'scabies', 'dry itchy skin', 'itching on arms', 'itching all over'],
                'synonyms' => ['prurigo', 'scabietic rash'],
                'priority' => 18,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Acne & Skin breakouts',
                'intent' => 'Skin complaint',
                'specialty' => 'Dermatology',
                'keywords' => ['acne', 'pimples', 'breakout', 'blackheads', 'cystic acne', 'pustules', 'facial acne', 'skin blemishes'],
                'synonyms' => ['acne vulgaris', 'comedones'],
                'priority' => 18,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Suspicious mole & skin growths',
                'intent' => 'Skin complaint',
                'specialty' => 'Dermatology',
                'keywords' => ['changing mole', 'skin growth', 'pigmented lesion', 'wart', 'dark spot', 'irregular mole'],
                'synonyms' => ['nevus check', 'melanoma screen', 'skin tag'],
                'priority' => 22,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // CARDIOLOGY (Heart-Related Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Palpitations',
                'intent' => 'Heart-related complaint',
                'specialty' => 'Cardiology',
                'keywords' => ['palpitations', 'rapid heartbeat', 'irregular heartbeat', 'fluttering heart', 'racing pulse', 'heart skipping beats'],
                'synonyms' => ['arrhythmia', 'tachycardia', 'extrasystoles'],
                'priority' => 25,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Mild chest discomfort & Hypertension review',
                'intent' => 'Heart-related complaint',
                'specialty' => 'Cardiology',
                'keywords' => ['high blood pressure', 'hypertension', 'elevated bp', 'mild chest tightness', 'angina check', 'ecg check', 'cardiac checkup'],
                'synonyms' => ['essential hypertension', 'coronary artery disease check'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // ORTHOPAEDICS (Musculoskeletal Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Joint pain & Arthritis',
                'intent' => 'Musculoskeletal complaint',
                'specialty' => 'Orthopaedics',
                'keywords' => ['joint pain', 'knee pain', 'hip pain', 'shoulder pain', 'arthritis', 'osteoarthritis', 'joint stiffness', 'swollen joints'],
                'synonyms' => ['arthralgia', 'synovitis'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Back pain & Spine discomfort',
                'intent' => 'Musculoskeletal complaint',
                'specialty' => 'Orthopaedics',
                'keywords' => ['back pain', 'lower back pain', 'sciatica', 'spine pain', 'lumbar pain', 'slipped disc', 'stiff back'],
                'synonyms' => ['lumbago', 'radiculopathy'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Sprain, Fracture & Sports Injury',
                'intent' => 'Musculoskeletal complaint',
                'specialty' => 'Orthopaedics',
                'keywords' => ['sprained ankle', 'wrist sprain', 'twisted knee', 'ligament tear', 'bone fracture', 'sports injury', 'tennis elbow', 'tendonitis'],
                'synonyms' => ['fracture check', 'ligament strain'],
                'priority' => 22,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // PULMONOLOGY / RESPIRATORY
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Persistent cough & Wheezing',
                'intent' => 'Respiratory complaint',
                'specialty' => 'Pulmonology',
                'keywords' => ['persistent cough', 'chronic cough', 'coughing for weeks', 'wheezing', 'asthma attack', 'bronchitis', 'shortness of breath in humid weather'],
                'synonyms' => ['copd', 'bronchial asthma', 'dyspnea'],
                'priority' => 22,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // ENT (Ear, Nose & Throat Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Ear pain & Hearing difficulty',
                'intent' => 'Ear complaint',
                'specialty' => 'ENT',
                'keywords' => ['ear pain', 'earache', 'blocked ear', 'hearing loss', 'ringing in ear', 'tinnitus', 'ear infection', 'ear discharge'],
                'synonyms' => ['otitis media', 'otitis externa'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Sinus congestion & Throat discomfort',
                'intent' => 'Nose and Throat complaint',
                'specialty' => 'ENT',
                'keywords' => ['sinus congestion', 'sinusitis', 'nasal blockage', 'post-nasal drip', 'sore throat', 'hoarseness', 'tonsillitis', 'loss of voice'],
                'synonyms' => ['pharyngitis', 'laryngitis', 'rhinosinusitis'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // OPHTHALMOLOGY (Eye Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Blurred vision & Eye strain',
                'intent' => 'Eye complaint',
                'specialty' => 'Ophthalmology',
                'keywords' => ['blurred vision', 'diminished vision', 'eye strain', 'difficulty reading', 'double vision', 'cataract', 'glaucoma check', 'eye pressure check'],
                'synonyms' => ['visual disturbance', 'refractive error', 'presbyopia'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Red, irritated or watery eyes',
                'intent' => 'Eye complaint',
                'specialty' => 'Ophthalmology',
                'keywords' => ['red eye', 'watery eyes', 'conjunctivitis', 'pink eye', 'burning eyes', 'itchy eyes', 'dry eyes'],
                'synonyms' => ['blepharitis', 'ocular irritation'],
                'priority' => 18,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // PAEDIATRICS (Child & Infant Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => "Child's fever & Paediatric illness",
                'intent' => 'Paediatric complaint',
                'specialty' => 'Paediatrics',
                'keywords' => ["child's fever", 'child fever', 'baby fever', 'infant sickness', 'pediatric checkup', 'toddler vomiting', 'childhood asthma', 'school vaccination'],
                'synonyms' => ['pediatric viral infection', 'childhood illness'],
                'priority' => 25,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // NEUROLOGY (Brain & Nerve Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Migraine & Chronic Headaches',
                'intent' => 'Neurological complaint',
                'specialty' => 'Neurology',
                'keywords' => ['migraine', 'throbbing headache', 'visual aura', 'light sensitivity headache', 'cluster headache', 'frequent headaches', 'tension headache'],
                'synonyms' => ['cephalalgia', 'hemicrania'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Tremor, Numbness & Vertigo',
                'intent' => 'Neurological complaint',
                'specialty' => 'Neurology',
                'keywords' => ['hand tremor', 'facial numbness', 'carpal tunnel', 'tingling fingers', 'vertigo', 'loss of balance', 'nerve pain'],
                'synonyms' => ['neuropathy', 'ataxia', 'parkinsonism'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // GASTROENTEROLOGY (Digestive Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Acid reflux & Epigastric distress',
                'intent' => 'Gastrointestinal complaint',
                'specialty' => 'Gastroenterology',
                'keywords' => ['acid reflux', 'heartburn', 'gerd', 'stomach cramps', 'epigastric discomfort', 'indigestion', 'gastritis', 'bloating after meals'],
                'synonyms' => ['dyspepsia', 'gastroesophageal reflux'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // ENDOCRINOLOGY (Metabolic & Hormonal Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Diabetes & Thyroid disorders',
                'intent' => 'Endocrine & Metabolic complaint',
                'specialty' => 'Endocrinology',
                'keywords' => ['diabetes check', 'high blood sugar', 'insulin review', 'hba1c check', 'thyroid review', 'tsh check', 'cold intolerance and fatigue', 'hormonal evaluation'],
                'synonyms' => ['hypothyroidism', 'hyperthyroidism', 'diabetes mellitus'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // UROLOGY (Urinary System Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Urinary tract symptoms & Kidney stones',
                'intent' => 'Urological complaint',
                'specialty' => 'Urology',
                'keywords' => ['burning urination', 'painful urination', 'uti', 'kidney stone pain', 'weak urinary stream', 'frequent nighttime urination', 'blood in urine'],
                'synonyms' => ['dysuria', 'nephrolithiasis', 'hematuria', 'nocturia'],
                'priority' => 22,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // GYNAECOLOGY (Women\'s Health Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Menstrual irregularity & Pelvic pain',
                'intent' => 'Gynaecological complaint',
                'specialty' => 'Gynaecology',
                'keywords' => ['irregular menstrual cycle', 'pelvic pain', 'severe menstrual cramps', 'prenatal consultation', 'first trimester checkup', 'pap smear', 'pregnancy test'],
                'synonyms' => ['dysmenorrhea', 'antenatal check', 'amenorrhea'],
                'priority' => 22,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // PSYCHIATRY (Mental Health Complaints)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Anxiety, Panic & Insomnia',
                'intent' => 'Mental health complaint',
                'specialty' => 'Psychiatry',
                'keywords' => ['anxiety', 'panic attacks', 'insomnia', 'cannot sleep', 'stress management', 'depression', 'chronic worry', 'mood swings'],
                'synonyms' => ['generalized anxiety disorder', 'major depressive disorder', 'sleep disorder'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // GENERAL SURGERY (Minor Procedures & Surgical Consults)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Cyst, Hernia & Wound Care',
                'intent' => 'Surgical consult complaint',
                'specialty' => 'General Surgery',
                'keywords' => ['sebaceous cyst', 'hernia consultation', 'inguinal hernia', 'lump removal', 'suture removal', 'post-operative wound check', 'wound dressing'],
                'synonyms' => ['minor surgical excision', 'herniorrhaphy consult'],
                'priority' => 20,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],

            // ---------------------------------------------------------
            // GENERAL MEDICINE (Primary Care & Triage Fallback)
            // ---------------------------------------------------------
            [
                'symptom_entity' => 'Viral fever, Chills & Flu',
                'intent' => 'General viral / infection complaint',
                'specialty' => 'General Medicine',
                'keywords' => ['viral fever', 'high fever', 'chills and body aches', 'flu symptoms', 'influenza', 'general malaise', 'runny nose and fever'],
                'synonyms' => ['pyrexia', 'viral syndrome', 'upper respiratory infection'],
                'priority' => 15,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Annual wellness checkup & Preventive health',
                'intent' => 'Routine preventive healthcare',
                'specialty' => 'General Medicine',
                'keywords' => ['annual wellness checkup', 'routine physical', 'general checkup', 'routine blood work', 'medical clearance', 'health screening'],
                'synonyms' => ['preventive health', 'executive health check'],
                'priority' => 15,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
            [
                'symptom_entity' => 'Toothache & Oral complaint',
                'intent' => 'Dental / Oral complaint',
                'specialty' => 'General Medicine',
                'keywords' => ['toothache', 'dental pain', 'swollen gum', 'tooth cavity pain'],
                'synonyms' => ['odontalgia', 'gingivitis'],
                'priority' => 15,
                'is_urgent' => false,
                'urgent_instructions' => null,
            ],
        ];

        foreach ($rules as $r) {
            $specialty = $specialties[$r['specialty']] ?? Specialty::where('name', $r['specialty'])->first();
            if (!$specialty) {
                continue;
            }

            SymptomRule::updateOrCreate(
                ['symptom_entity' => $r['symptom_entity']],
                [
                    'intent' => $r['intent'],
                    'specialty_id' => $specialty->id,
                    'keywords' => $r['keywords'],
                    'synonyms' => $r['synonyms'],
                    'priority' => $r['priority'],
                    'is_urgent' => $r['is_urgent'],
                    'urgent_instructions' => $r['urgent_instructions'],
                    'is_active' => true,
                ]
            );
        }
    }
}
