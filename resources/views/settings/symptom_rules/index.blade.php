@extends('layouts.app')

@section('title', 'Symptom Rules & Clinical Intent Engine')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Symptom Rules Engine</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3-fill text-primary"></i>
                <span>Symptom Entity & Clinical Intent Rules Engine</span>
            </h4>
            <p class="text-muted small mb-0">Manage database-driven rules mapping patient complaints to medical specialties with priority ranking and emergency safeguards.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Clinic Settings
            </a>
            <button class="btn btn-sm btn-cms-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createRuleModal">
                <i class="bi bi-plus-circle me-1"></i> Create Symptom Rule
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Form Errors Detected:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Interactive Test Simulator Widget -->
    <div class="card-custom mb-4 border-primary-subtle">
        <div class="card-header-clean bg-light d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-cpu text-primary fs-5"></i>
                <span class="fw-bold text-dark">Live Symptom Prediction & Routing Simulator</span>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary">Interactive Diagnostic Tool</span>
        </div>
        <div class="card-body p-4">
            <p class="small text-muted mb-2">Test how the clinical triage engine resolves complaints, detects red flags, scores specialties, and explains allocations in real time.</p>
            <div class="row g-2 align-items-center mb-3">
                <div class="col-12 col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-chat-heart text-muted"></i></span>
                        <input type="text" id="simInput" class="form-control" placeholder="e.g. 'Patient has severe skin rash with itchy red patches' or 'Sudden slurred speech'..." value="Skin rash and persistent itching on forearm">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <button type="button" id="simBtn" class="btn btn-cms-primary w-100 fw-bold">
                        <i class="bi bi-play-circle me-1"></i> Run Prediction Test
                    </button>
                </div>
            </div>

            <!-- Simulator Result Box -->
            <div id="simResultBox" class="p-3 bg-white rounded border d-none">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-semibold text-uppercase">Recommended Specialty:</span>
                        <span id="simSpecialty" class="fw-bold fs-6 text-primary">--</span>
                        <span id="simConfidence" class="badge bg-success">High Confidence</span>
                    </div>
                    <div class="small text-muted">
                        Composite Score: <strong id="simScore" class="text-dark">--</strong>
                    </div>
                </div>

                <div id="simUrgentBanner" class="alert alert-danger py-2 px-3 small d-none mb-2">
                    <div class="fw-bold d-flex align-items-center gap-1">
                        <i class="bi bi-exclamation-octagon-fill"></i> CRITICAL EMERGENCY SAFEGUARD TRIGGERED
                    </div>
                    <div id="simUrgentText" class="mt-1"></div>
                </div>

                <div class="small text-dark mb-2">
                    <strong class="text-muted">Explanation:</strong> <span id="simExplanation">--</span>
                </div>

                <div id="simAlternativesRow" class="small text-muted d-none">
                    <strong>Alternative Specialties Scored:</strong> <span id="simAlternatives">--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule Management Table -->
    <div class="card-custom">
        <!-- Filter Bar -->
        <div class="card-header-clean bg-white border-bottom p-3">
            <form method="GET" action="{{ route('settings.symptom_rules.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search entity, intent, keywords..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="specialty_id" class="form-select form-select-sm">
                        <option value="">All Specialties</option>
                        @foreach($specialties as $spec)
                            <option value="{{ $spec->id }}" {{ request('specialty_id') == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="urgency" class="form-select form-select-sm">
                        <option value="">All Urgencies</option>
                        <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Emergency / Urgent</option>
                        <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>

                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->anyFilled(['search', 'specialty_id', 'urgency', 'status']))
                        <a href="{{ route('settings.symptom_rules.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">Weight</th>
                        <th>Symptom Entity</th>
                        <th>Clinical Intent</th>
                        <th>Suggested Specialty</th>
                        <th>Keywords / Synonyms</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr class="{{ !$rule->is_active ? 'opacity-50 bg-light' : '' }}">
                            <td class="ps-3">
                                <span class="badge bg-secondary font-monospace">{{ $rule->priority }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $rule->symptom_entity }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $rule->intent }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">
                                    {{ $rule->specialty->name }}
                                </span>
                            </td>
                            <td style="max-width: 280px;">
                                <div class="text-truncate">
                                    @foreach(array_slice($rule->keywords ?? [], 0, 3) as $kw)
                                        <span class="badge bg-light text-dark border me-1">{{ $kw }}</span>
                                    @endforeach
                                    @if(count($rule->keywords ?? []) > 3)
                                        <small class="text-muted">+{{ count($rule->keywords) - 3 }} more</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($rule->is_urgent)
                                    <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Red Flag
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border">Routine</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('settings.symptom_rules.toggle', $rule) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Click to toggle status">
                                        @if($rule->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 edit-rule-btn"
                                    data-id="{{ $rule->id }}"
                                    data-entity="{{ $rule->symptom_entity }}"
                                    data-intent="{{ $rule->intent }}"
                                    data-specialty="{{ $rule->specialty_id }}"
                                    data-keywords="{{ implode(', ', $rule->keywords ?? []) }}"
                                    data-synonyms="{{ implode(', ', $rule->synonyms ?? []) }}"
                                    data-priority="{{ $rule->priority }}"
                                    data-urgent="{{ $rule->is_urgent ? '1' : '0' }}"
                                    data-instructions="{{ $rule->urgent_instructions }}"
                                    data-active="{{ $rule->is_active ? '1' : '0' }}"
                                    data-update-url="{{ route('settings.symptom_rules.update', $rule) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <form method="POST" action="{{ route('settings.symptom_rules.destroy', $rule) }}" class="d-inline" onsubmit="return confirm('Delete rule \'{{ $rule->symptom_entity }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 ms-1">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No symptom rules found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rules->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">Showing {{ $rules->firstItem() }}-{{ $rules->lastItem() }} of {{ $rules->total() }} rules</span>
                {{ $rules->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Create Rule -->
<div class="modal fade" id="createRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('settings.symptom_rules.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-plus-circle text-primary me-2"></i>Create New Symptom Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Symptom Entity Name <span class="text-danger">*</span></label>
                            <input type="text" name="symptom_entity" class="form-control" placeholder="e.g. Skin rash, Palpitations" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Intent / Category <span class="text-danger">*</span></label>
                            <input type="text" name="intent" class="form-control" placeholder="e.g. Skin complaint, Musculoskeletal" required>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Target Clinical Specialty <span class="text-danger">*</span></label>
                            <select name="specialty_id" class="form-select" required>
                                <option value="">-- Select Medical Specialty --</option>
                                @foreach($specialties as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Matching Weight / Priority (1-100) <span class="text-danger">*</span></label>
                            <input type="number" name="priority" class="form-control" value="20" min="1" max="100" required>
                            <div class="form-text small">Higher weight takes precedence.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Keywords & Matching Phrases <span class="text-danger">*</span></label>
                            <textarea name="keywords_input" rows="2" class="form-control" placeholder="Comma-separated keywords, e.g.: rash, itching, eczema, hives, red patches" required></textarea>
                            <div class="form-text small">Separate with commas. Multi-word phrases are supported.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Medical or Colloquial Synonyms (Optional)</label>
                            <textarea name="synonyms_input" rows="2" class="form-control" placeholder="e.g.: dermatitis, erythema, pruritus"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_urgent" value="1" id="isUrgentToggle">
                                <label class="form-check-label fw-bold text-danger" for="isUrgentToggle">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Flag as Urgent Clinical Red-Flag (Emergency Safeguard)
                                </label>
                                <div class="form-text small">Triggers prominent clinical emergency warning and prevents routine auto-allocation.</div>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="urgentInstructionsGroup">
                            <label class="form-label small fw-semibold text-danger">Emergency Clinical Guidance Instructions</label>
                            <textarea name="urgent_instructions" rows="2" class="form-control" placeholder="Specific emergency advice displayed to receptionist (e.g. Call emergency response immediately)..."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" checked>
                                <label class="form-check-label small fw-semibold" for="isActiveCheck">Rule is Active for Scheduling Engine</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cms-primary fw-bold">Save Symptom Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Rule -->
<div class="modal fade" id="editRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="editRuleForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil text-primary me-2"></i>Edit Symptom Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Symptom Entity Name <span class="text-danger">*</span></label>
                            <input type="text" name="symptom_entity" id="editEntity" class="form-control" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Intent / Category <span class="text-danger">*</span></label>
                            <input type="text" name="intent" id="editIntent" class="form-control" required>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Target Clinical Specialty <span class="text-danger">*</span></label>
                            <select name="specialty_id" id="editSpecialty" class="form-select" required>
                                @foreach($specialties as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Matching Weight (1-100) <span class="text-danger">*</span></label>
                            <input type="number" name="priority" id="editPriority" class="form-control" min="1" max="100" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Keywords & Matching Phrases <span class="text-danger">*</span></label>
                            <textarea name="keywords_input" id="editKeywords" rows="2" class="form-control" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Synonyms</label>
                            <textarea name="synonyms_input" id="editSynonyms" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_urgent" value="1" id="editUrgentToggle">
                                <label class="form-check-label fw-bold text-danger" for="editUrgentToggle">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Flag as Urgent Clinical Red-Flag (Emergency Safeguard)
                                </label>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="editUrgentInstructionsGroup">
                            <label class="form-label small fw-semibold text-danger">Emergency Clinical Guidance Instructions</label>
                            <textarea name="urgent_instructions" id="editInstructions" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActiveCheck">
                                <label class="form-check-label small fw-semibold" for="editActiveCheck">Rule is Active for Scheduling Engine</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cms-primary fw-bold">Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Simulator Handling
    const simInput = document.getElementById('simInput');
    const simBtn = document.getElementById('simBtn');
    const simResultBox = document.getElementById('simResultBox');

    function runSimulator() {
        const phrase = simInput.value.trim();
        if (!phrase) return;

        simBtn.disabled = true;
        simBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Analyzing...';

        fetch(`{{ route('settings.symptom_rules.test_simulator') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ phrase: phrase })
        })
        .then(res => res.json())
        .then(data => {
            simBtn.disabled = false;
            simBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Run Prediction Test';

            if (data.success) {
                document.getElementById('simSpecialty').textContent = data.suggested_specialty_name;
                
                const confBadge = document.getElementById('simConfidence');
                confBadge.textContent = `${data.confidence} Confidence`;
                confBadge.className = `badge bg-${data.confidence_badge || 'secondary'}`;

                document.getElementById('simScore').textContent = data.score;
                document.getElementById('simExplanation').textContent = data.explanation;

                // Urgent Banner
                const urgentBanner = document.getElementById('simUrgentBanner');
                if (data.is_urgent) {
                    document.getElementById('simUrgentText').textContent = data.urgent_instructions || 'Emergency protocol triggered.';
                    urgentBanner.classList.remove('d-none');
                } else {
                    urgentBanner.classList.add('d-none');
                }

                // Alternatives
                const altRow = document.getElementById('simAlternativesRow');
                if (data.alternative_specialties && data.alternative_specialties.length > 0) {
                    const altText = data.alternative_specialties.map(a => `${a.specialty_name} (Score: ${a.score})`).join(' • ');
                    document.getElementById('simAlternatives').textContent = altText;
                    altRow.classList.remove('d-none');
                } else {
                    altRow.classList.add('d-none');
                }

                simResultBox.classList.remove('d-none');
            }
        })
        .catch(err => {
            simBtn.disabled = false;
            simBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Run Prediction Test';
            console.error('Simulator error:', err);
        });
    }

    if (simBtn) {
        simBtn.addEventListener('click', runSimulator);
        simInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runSimulator();
            }
        });
    }

    // 2. Urgent Toggle in Create Form
    const isUrgentToggle = document.getElementById('isUrgentToggle');
    const urgentInstructionsGroup = document.getElementById('urgentInstructionsGroup');
    if (isUrgentToggle) {
        isUrgentToggle.addEventListener('change', function() {
            if (this.checked) {
                urgentInstructionsGroup.classList.remove('d-none');
            } else {
                urgentInstructionsGroup.classList.add('d-none');
            }
        });
    }

    // 3. Edit Rule Modal Population
    const editModalEl = document.getElementById('editRuleModal');
    const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
    const editUrgentToggle = document.getElementById('editUrgentToggle');
    const editUrgentInstructionsGroup = document.getElementById('editUrgentInstructionsGroup');

    if (editUrgentToggle) {
        editUrgentToggle.addEventListener('change', function() {
            if (this.checked) {
                editUrgentInstructionsGroup.classList.remove('d-none');
            } else {
                editUrgentInstructionsGroup.classList.add('d-none');
            }
        });
    }

    document.querySelectorAll('.edit-rule-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = document.getElementById('editRuleForm');
            form.action = this.dataset.updateUrl;

            document.getElementById('editEntity').value = this.dataset.entity;
            document.getElementById('editIntent').value = this.dataset.intent;
            document.getElementById('editSpecialty').value = this.dataset.specialty;
            document.getElementById('editPriority').value = this.dataset.priority;
            document.getElementById('editKeywords').value = this.dataset.keywords;
            document.getElementById('editSynonyms').value = this.dataset.synonyms;

            const isUrgent = this.dataset.urgent === '1';
            editUrgentToggle.checked = isUrgent;
            document.getElementById('editInstructions').value = this.dataset.instructions || '';
            if (isUrgent) {
                editUrgentInstructionsGroup.classList.remove('d-none');
            } else {
                editUrgentInstructionsGroup.classList.add('d-none');
            }

            document.getElementById('editActiveCheck').checked = (this.dataset.active === '1');

            editModal.show();
        });
    });
});
</script>
@endpush
@endsection
