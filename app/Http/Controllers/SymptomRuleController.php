<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Models\SymptomRule;
use App\Services\SymptomSpecialtyPredictionService;
use Illuminate\Http\Request;

class SymptomRuleController extends Controller
{
    public function __construct(
        protected SymptomSpecialtyPredictionService $predictionService
    ) {}

    /**
     * Display list of symptom rules with filtering and search.
     */
    public function index(Request $request)
    {
        $query = SymptomRule::with('specialty');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('symptom_entity', 'like', "%{$search}%")
                  ->orWhere('intent', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%")
                  ->orWhere('synonyms', 'like', "%{$search}%");
            });
        }

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->input('specialty_id'));
        }

        if ($request->filled('urgency')) {
            if ($request->input('urgency') === 'urgent') {
                $query->where('is_urgent', true);
            } elseif ($request->input('urgency') === 'routine') {
                $query->where('is_urgent', false);
            }
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $rules = $query->byPriority()->paginate(15)->withQueryString();
        $specialties = Specialty::orderBy('name')->get();

        return view('settings.symptom_rules.index', compact('rules', 'specialties'));
    }

    /**
     * Real-time AJAX endpoint to predict specialty and evaluate urgency from symptom text.
     */
    public function predict(Request $request)
    {
        $complaint = trim($request->input('complaint', ''));

        if (mb_strlen($complaint) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least 3 characters of symptom details.',
            ]);
        }

        $result = $this->predictionService->predict($complaint);

        return response()->json($result);
    }

    /**
     * Store new symptom rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'symptom_entity' => ['required', 'string', 'max:150'],
            'intent' => ['required', 'string', 'max:150'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'keywords_input' => ['required', 'string'],
            'synonyms_input' => ['nullable', 'string'],
            'priority' => ['required', 'integer', 'min:1', 'max:100'],
            'is_urgent' => ['nullable', 'boolean'],
            'urgent_instructions' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $keywords = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['keywords_input']))));
        $synonyms = !empty($validated['synonyms_input'])
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['synonyms_input']))))
            : [];

        $isUrgent = (bool)($validated['is_urgent'] ?? false);

        SymptomRule::create([
            'symptom_entity' => $validated['symptom_entity'],
            'intent' => $validated['intent'],
            'specialty_id' => $validated['specialty_id'],
            'keywords' => $keywords,
            'synonyms' => $synonyms,
            'priority' => $validated['priority'],
            'is_urgent' => $isUrgent,
            'urgent_instructions' => $isUrgent ? ($validated['urgent_instructions'] ?? 'Clinical emergency triage guidance.') : null,
            'is_active' => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()->route('settings.symptom_rules.index')
            ->with('success', "Symptom rule '{$validated['symptom_entity']}' created successfully.");
    }

    /**
     * Update an existing symptom rule.
     */
    public function update(Request $request, SymptomRule $rule)
    {
        $validated = $request->validate([
            'symptom_entity' => ['required', 'string', 'max:150'],
            'intent' => ['required', 'string', 'max:150'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'keywords_input' => ['required', 'string'],
            'synonyms_input' => ['nullable', 'string'],
            'priority' => ['required', 'integer', 'min:1', 'max:100'],
            'is_urgent' => ['nullable', 'boolean'],
            'urgent_instructions' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $keywords = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['keywords_input']))));
        $synonyms = !empty($validated['synonyms_input'])
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['synonyms_input']))))
            : [];

        $isUrgent = (bool)($validated['is_urgent'] ?? false);

        $rule->update([
            'symptom_entity' => $validated['symptom_entity'],
            'intent' => $validated['intent'],
            'specialty_id' => $validated['specialty_id'],
            'keywords' => $keywords,
            'synonyms' => $synonyms,
            'priority' => $validated['priority'],
            'is_urgent' => $isUrgent,
            'urgent_instructions' => $isUrgent ? ($validated['urgent_instructions'] ?? $rule->urgent_instructions) : null,
            'is_active' => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()->route('settings.symptom_rules.index')
            ->with('success', "Symptom rule '{$rule->symptom_entity}' updated successfully.");
    }

    /**
     * Quick toggle rule active status.
     */
    public function toggle(SymptomRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);

        return redirect()->back()
            ->with('success', "Rule '{$rule->symptom_entity}' status changed to " . ($rule->is_active ? 'Active' : 'Inactive') . '.');
    }

    /**
     * Delete rule.
     */
    public function destroy(SymptomRule $rule)
    {
        $entity = $rule->symptom_entity;
        $rule->delete();

        return redirect()->route('settings.symptom_rules.index')
            ->with('success', "Symptom rule '{$entity}' deleted successfully.");
    }

    /**
     * Live test simulator endpoint for testing custom symptom phrases.
     */
    public function testSimulator(Request $request)
    {
        $phrase = trim($request->input('phrase', ''));

        if (empty($phrase)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a test symptom phrase.',
            ]);
        }

        $result = $this->predictionService->predict($phrase);

        return response()->json($result);
    }
}
