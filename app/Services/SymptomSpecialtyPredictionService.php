<?php

namespace App\Services;

use App\Models\Specialty;
use App\Models\SymptomRule;

class SymptomSpecialtyPredictionService
{
    /**
     * Normalize the patient symptom input string:
     * converts to lowercase, replaces punctuation with spaces, and collapses whitespace.
     */
    public function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        // Replace non-alphanumeric characters with spaces
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        // Collapse multiple spaces
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Analyze a patient complaint and predict the recommended medical specialty,
     * evaluate clinical urgency (red-flag symptoms), and generate a human-readable explanation.
     *
     * @param string $complaint
     * @return array
     */
    public function predict(string $complaint): array
    {
        $normalized = $this->normalizeText($complaint);

        // Fetch all active rules ordered by priority descending
        $rules = SymptomRule::with('specialty')->active()->byPriority()->get();

        // -----------------------------------------------------------------
        // Step 1: Urgent Safeguard & Red-Flag Clinical Evaluation
        // -----------------------------------------------------------------
        $isUrgent = false;
        $urgentInstructions = null;
        $urgentRuleMatched = null;

        $urgentRules = $rules->filter(fn($r) => $r->is_urgent);

        foreach ($urgentRules as $uRule) {
            $matched = false;

            // Check urgent symptom entity
            $normEntity = $this->normalizeText($uRule->symptom_entity);
            if (!empty($normEntity) && str_contains($normalized, $normEntity)) {
                $matched = true;
            }

            // Check urgent keywords
            if (!$matched && !empty($uRule->keywords)) {
                foreach ($uRule->keywords as $kw) {
                    $normKw = $this->normalizeText($kw);
                    if (!empty($normKw) && str_contains($normalized, $normKw)) {
                        $matched = true;
                        break;
                    }
                }
            }

            // Check urgent synonyms
            if (!$matched && !empty($uRule->synonyms)) {
                foreach ($uRule->synonyms as $syn) {
                    $normSyn = $this->normalizeText($syn);
                    if (!empty($normSyn) && str_contains($normalized, $normSyn)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $isUrgent = true;
                $urgentInstructions = $uRule->urgent_instructions;
                $urgentRuleMatched = $uRule;
                break; // Stop on first urgent red-flag match
            }
        }

        // -----------------------------------------------------------------
        // Step 2: Scoring Engine across Specialties
        // -----------------------------------------------------------------
        $specialtyScores = [];

        foreach ($rules as $rule) {
            $specialtyId = $rule->specialty_id;
            $ruleScore = 0;
            $matchedKeywordsList = [];

            // A. Exact Symptom Entity Match (High weight)
            $normEntity = $this->normalizeText($rule->symptom_entity);
            if (!empty($normEntity) && str_contains($normalized, $normEntity)) {
                $ruleScore += 100 + ($rule->priority * 2);
                $matchedKeywordsList[] = "entity: '{$rule->symptom_entity}'";
            }

            // B. Keywords Matching
            if (!empty($rule->keywords) && is_array($rule->keywords)) {
                foreach ($rule->keywords as $kw) {
                    $normKw = $this->normalizeText($kw);
                    if (empty($normKw)) {
                        continue;
                    }

                    // Check if multi-word phrase
                    if (str_contains($normKw, ' ')) {
                        if (str_contains($normalized, $normKw)) {
                            $ruleScore += 60 + $rule->priority;
                            $matchedKeywordsList[] = "'{$kw}'";
                        }
                    } else {
                        // Single word: use word boundary match to avoid partial false positives
                        if (preg_match('/\b' . preg_quote($normKw, '/') . '\b/i', $normalized)) {
                            $bonus = (mb_strlen($normKw) >= 5) ? 10 : 0;
                            $ruleScore += 30 + $bonus + $rule->priority;
                            $matchedKeywordsList[] = "'{$kw}'";
                        }
                    }
                }
            }

            // C. Synonyms Matching
            if (!empty($rule->synonyms) && is_array($rule->synonyms)) {
                foreach ($rule->synonyms as $syn) {
                    $normSyn = $this->normalizeText($syn);
                    if (!empty($normSyn) && str_contains($normalized, $normSyn)) {
                        $ruleScore += 45 + $rule->priority;
                        $matchedKeywordsList[] = "synonym: '{$syn}'";
                    }
                }
            }

            // D. Intent Match
            $normIntent = $this->normalizeText($rule->intent);
            if (!empty($normIntent) && str_contains($normalized, $normIntent)) {
                $ruleScore += 25;
            }

            if ($ruleScore > 0) {
                if (!isset($specialtyScores[$specialtyId])) {
                    $specialtyScores[$specialtyId] = [
                        'specialty' => $rule->specialty,
                        'total_score' => 0,
                        'rules_matched' => [],
                        'matched_keywords' => [],
                        'highest_rule' => $rule,
                        'highest_rule_score' => 0,
                    ];
                }

                $specialtyScores[$specialtyId]['total_score'] += $ruleScore;
                $specialtyScores[$specialtyId]['rules_matched'][] = $rule;
                $specialtyScores[$specialtyId]['matched_keywords'] = array_unique(array_merge(
                    $specialtyScores[$specialtyId]['matched_keywords'],
                    $matchedKeywordsList
                ));

                if ($ruleScore > $specialtyScores[$specialtyId]['highest_rule_score']) {
                    $specialtyScores[$specialtyId]['highest_rule_score'] = $ruleScore;
                    $specialtyScores[$specialtyId]['highest_rule'] = $rule;
                }
            }
        }

        // -----------------------------------------------------------------
        // Step 3: Ranking, Confidence Assessment & Fallback Logic
        // -----------------------------------------------------------------
        // Default / Fallback Specialty: General Medicine
        $defaultSpecialty = Specialty::where('slug', 'general-medicine')->first() 
            ?? Specialty::first();

        if (empty($specialtyScores)) {
            // No matching rules found
            return [
                'success' => true,
                'normalized_text' => $normalized,
                'suggested_specialty_id' => $defaultSpecialty?->id,
                'suggested_specialty_name' => $defaultSpecialty?->name ?? 'General Medicine',
                'confidence' => 'Unknown',
                'confidence_badge' => 'secondary',
                'score' => 0,
                'explanation' => 'No specific medical specialty rule matched the entered symptom description. Routed to General Medicine for comprehensive initial primary care triage.',
                'is_urgent' => $isUrgent,
                'urgency_level' => $isUrgent ? 'CRITICAL' : 'ROUTINE',
                'urgent_instructions' => $urgentInstructions,
                'matched_rule_id' => $urgentRuleMatched?->id,
                'matched_rules_summary' => [],
                'alternative_specialties' => [],
            ];
        }

        // Sort scored specialties by total_score descending
        uasort($specialtyScores, fn($a, $b) => $b['total_score'] <=> $a['total_score']);

        $sortedSpecialties = array_values($specialtyScores);
        $top = $sortedSpecialties[0];
        $runnerUp = $sortedSpecialties[1] ?? null;

        $topScore = $top['total_score'];
        $confidence = 'Medium';
        $confidenceBadge = 'info';

        // Assess confidence rating
        if ($topScore >= 120) {
            $confidence = 'High';
            $confidenceBadge = 'success';
        } elseif ($runnerUp && ($topScore - $runnerUp['total_score'] <= 15)) {
            $confidence = 'Low';
            $confidenceBadge = 'warning';
        } elseif ($topScore < 45) {
            $confidence = 'Low';
            $confidenceBadge = 'warning';
        } else {
            $confidence = 'Medium';
            $confidenceBadge = 'info';
        }

        // Construct human-readable explanation
        $primaryRule = $top['highest_rule'];
        $keywordsString = !empty($top['matched_keywords']) 
            ? implode(', ', array_slice($top['matched_keywords'], 0, 4))
            : 'keyword match';

        $explanation = "Matched {$top['specialty']->name} via rule '{$primaryRule->symptom_entity}' [{$primaryRule->intent}] based on: {$keywordsString}.";

        if ($runnerUp && ($topScore - $runnerUp['total_score'] <= 25)) {
            $explanation .= " Alternative consideration: {$runnerUp['specialty']->name} (differential score: {$runnerUp['total_score']}).";
        }

        if ($isUrgent && $urgentRuleMatched) {
            $explanation = "⚠️ URGENT SAFEGUARD TRIGGERED: '{$urgentRuleMatched->symptom_entity}'. " . $explanation;
        }

        $alternatives = array_slice($sortedSpecialties, 1, 3);
        $alternativesSummary = array_map(fn($item) => [
            'specialty_id' => $item['specialty']->id,
            'specialty_name' => $item['specialty']->name,
            'score' => $item['total_score'],
        ], $alternatives);

        return [
            'success' => true,
            'normalized_text' => $normalized,
            'suggested_specialty_id' => $top['specialty']->id,
            'suggested_specialty_name' => $top['specialty']->name,
            'confidence' => $confidence,
            'confidence_badge' => $confidenceBadge,
            'score' => $topScore,
            'explanation' => $explanation,
            'is_urgent' => $isUrgent,
            'urgency_level' => $isUrgent ? 'CRITICAL' : 'ROUTINE',
            'urgent_instructions' => $urgentInstructions,
            'matched_rule_id' => $primaryRule->id,
            'matched_rules_summary' => array_map(fn($r) => [
                'id' => $r->id,
                'entity' => $r->symptom_entity,
                'intent' => $r->intent,
            ], $top['rules_matched']),
            'alternative_specialties' => $alternativesSummary,
        ];
    }
}
