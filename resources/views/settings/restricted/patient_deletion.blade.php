@extends('layouts.app')

@section('title', 'Restricted Administrative Actions - Permanent Patient Deletion')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Restricted Administrative Actions</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-shield-slash"></i>
                <span>Restricted Administrative Action: Permanent Patient Deletion</span>
            </h4>
            <p class="text-muted small mb-0">Authorized Administrator Access Only. Deletions are irreversible, audited, and strictly validated against active clinical bookings.</p>
        </div>
        <div>
            <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Return to Settings
            </a>
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
            <div class="fw-bold d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill"></i> Deletion Request Rejected
            </div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Deletion Form Card -->
        <div class="col-12 col-lg-7">
            <div class="card-custom border-danger-subtle">
                <div class="card-header-clean bg-danger bg-opacity-10 d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-2 text-danger fw-bold">
                        <i class="bi bi-person-x fs-5"></i>
                        <span>Permanent Record Purge Workflow</span>
                    </div>
                    <span class="badge bg-danger">High Privilege Required</span>
                </div>

                <div class="card-body p-4">
                    <!-- Step 1: Patient Search / Lookup -->
                    <label class="form-label fw-semibold small text-uppercase text-muted">Step 1: Search & Select Patient</label>
                    <div class="position-relative mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="patientSearchInput" class="form-control" placeholder="Search by patient name, phone number, or code (e.g. PAT-2026-0001)..." autocomplete="off">
                        </div>
                        <div id="searchResultsDropdown" class="list-group position-absolute w-100 shadow-sm mt-1 d-none" style="z-index: 1050; max-height: 240px; overflow-y: auto;"></div>
                    </div>

                    @if($selectedPatient)
                        <!-- Step 2: Selected Patient Dossier -->
                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">{{ $selectedPatient->name }}</h6>
                                    <span class="badge bg-secondary me-2">{{ $selectedPatient->patient_code }}</span>
                                    <span class="small text-muted">{{ $selectedPatient->effective_age }} yrs • {{ $selectedPatient->sex }} • {{ $selectedPatient->phone }}</span>
                                </div>
                                <a href="{{ route('settings.restricted.patient_deletion') }}" class="btn btn-sm btn-outline-secondary py-0">Change</a>
                            </div>

                            <!-- Appointment Dependencies Safeguard Banner -->
                            <div class="mt-3 pt-2 border-top">
                                @if($activeAppointmentsCount > 0)
                                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-0 py-2 small">
                                        <i class="bi bi-slash-circle-fill fs-5"></i>
                                        <div>
                                            <strong>Deletion Blocked:</strong> Patient has <strong>{{ $activeAppointmentsCount }}</strong> active or upcoming appointment(s). 
                                            The system prohibits deleting patients with scheduled or checked-in appointments to protect operational integrity.
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-success d-flex align-items-center gap-2 mb-0 py-2 small">
                                        <i class="bi bi-check-circle-fill fs-5"></i>
                                        <div>
                                            <strong>Integrity Check Passed:</strong> Patient has <strong>0</strong> active or upcoming appointments. ({{ $pastAppointmentsCount }} completed/cancelled historical records will be cleanly archived in the audit trail).
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Step 3: Confirmation and Execution Form -->
                        <form method="POST" action="{{ route('settings.restricted.patient_deletion.destroy') }}">
                            @csrf
                            <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-danger">
                                    Step 2: Confirm Patient ID <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-1">To prevent accidental deletion, type the exact Patient ID: <code class="fw-bold text-dark">{{ $selectedPatient->patient_code }}</code></p>
                                <input type="text" name="confirm_patient_code" class="form-control font-monospace" placeholder="Type {{ $selectedPatient->patient_code }}" required autocomplete="off">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    Step 3: Administrative Deletion Reason <span class="text-danger">*</span>
                                </label>
                                <textarea name="reason" rows="3" class="form-control" placeholder="Provide an explicit operational, compliance, or legal reason for this deletion (min 10 characters)..." required>{{ old('reason') }}</textarea>
                                <div class="form-text small">This rationale is permanently recorded in the immutable audit log with your Administrator ID and IP address.</div>
                            </div>

                            <div class="form-check mb-4 p-3 bg-danger bg-opacity-10 rounded border border-danger-subtle">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="acknowledge_irreversible" id="ackCheck" value="1" required>
                                <label class="form-check-label small fw-bold text-danger" for="ackCheck">
                                    I acknowledge that this action is permanent and completely irreversible. All patient demographic, contact, and non-active clinical records will be permanently expunged from the active database.
                                </label>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('settings.restricted.patient_deletion') }}" class="btn btn-light border me-2">Cancel</a>
                                <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm" {{ $activeAppointmentsCount > 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-trash3-fill me-1"></i> Permanently Delete Patient Record
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-5 border rounded-3 bg-light">
                            <i class="bi bi-person-bounding-box text-muted fs-1 mb-2"></i>
                            <h6 class="fw-semibold text-dark">No Patient Selected</h6>
                            <p class="text-muted small mb-0">Use the search box above to locate and explicitly select a patient record for permanent removal.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Audit Trail Card -->
        <div class="col-12 col-lg-5">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-primary fs-5"></i>
                        <span class="fw-bold">Independent Deletion Audit Log</span>
                    </div>
                    <span class="badge bg-secondary">{{ $recentAudits->count() }} Recorded</span>
                </div>

                <div class="card-body p-0">
                    @if($recentAudits->isEmpty())
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-check2-all fs-3 d-block mb-1 text-success"></i>
                            No patient deletions have been recorded. All records are intact.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Date / Time</th>
                                        <th>Patient Code</th>
                                        <th>Admin</th>
                                        <th>Outcome</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAudits as $audit)
                                        <tr>
                                            <td class="ps-3 text-nowrap">
                                                <div>{{ $audit->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $audit->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold font-monospace">{{ $audit->patient_code }}</div>
                                                <small class="text-muted text-truncate d-inline-block" style="max-width: 130px;">{{ $audit->patient_name }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $audit->admin_name }}</div>
                                                <small class="text-muted font-monospace">{{ $audit->ip_address }}</small>
                                            </td>
                                            <td>
                                                @if($audit->outcome === 'success')
                                                    <span class="badge bg-success">Deleted</span>
                                                @else
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                                <div class="text-truncate small text-muted" style="max-width: 140px;" title="{{ $audit->reason }}">
                                                    {{ $audit->reason }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patientSearchInput');
    const dropdown = document.getElementById('searchResultsDropdown');
    let debounceTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                dropdown.innerHTML = '';
                dropdown.classList.add('d-none');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('settings.restricted.patient_deletion.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.length === 0) {
                            dropdown.innerHTML = '<div class="list-group-item text-muted small p-2">No matching patients found.</div>';
                            dropdown.classList.remove('d-none');
                            return;
                        }

                        data.forEach(patient => {
                            const item = document.createElement('a');
                            item.href = `{{ route('settings.restricted.patient_deletion') }}?patient_id=${patient.id}`;
                            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2';
                            
                            let warningBadge = patient.active_appointments_count > 0 
                                ? `<span class="badge bg-danger">${patient.active_appointments_count} Active Bookings</span>` 
                                : `<span class="badge bg-success">0 Active</span>`;

                            item.innerHTML = `
                                <div>
                                    <div class="fw-bold small text-dark">${patient.name} <span class="badge bg-light text-dark font-monospace">${patient.patient_code}</span></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Phone: ${patient.phone} • ${patient.age}y (${patient.sex})</div>
                                </div>
                                ${warningBadge}
                            `;
                            dropdown.appendChild(item);
                        });

                        dropdown.classList.remove('d-none');
                    })
                    .catch(err => console.error('Patient search error:', err));
            }, 300);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('d-none');
            }
        });
    }
});
</script>
@endpush
@endsection
