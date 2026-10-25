<?php $__env->startSection('title', 'Reception Operations Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <!-- Top Header & Quick Actions -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Reception Desk Workspace</h4>
            <p class="text-muted small mb-0">Overview of patient flow, doctor duty statuses, and real-time appointment scheduling.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?php echo e(route('patients.create')); ?>" class="btn btn-sm btn-cms-primary shadow-sm px-3">
                <i class="bi bi-person-plus me-1"></i> Register New Patient
            </a>
            <a href="<?php echo e(route('appointments.create')); ?>" class="btn btn-sm btn-cms-teal shadow-sm px-3">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </a>
            <a href="<?php echo e(route('roster.index')); ?>" class="btn btn-sm btn-outline-secondary px-3">
                <i class="bi bi-calendar-week me-1"></i> View Today's Schedule
            </a>
            <a href="<?php echo e(route('doctors.index')); ?>" class="btn btn-sm btn-outline-secondary px-3">
                <i class="bi bi-person-lines-fill me-1"></i> Doctor Directory
            </a>
        </div>
    </div>

    <!-- A. Summary Cards (7 Live Metrics from DB) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Registered Patients -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Patients</span>
                    <span class="p-2 bg-primary-subtle text-primary rounded-3"><i class="bi bi-people fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?php echo e(number_format($totalPatients)); ?></h3>
                <small class="text-muted" style="font-size: 0.76rem;">Registered clinic database</small>
            </div>
        </div>

        <!-- 2. Today's Appointments -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Appointments Today</span>
                    <span class="p-2 bg-info-subtle text-info rounded-3"><i class="bi bi-calendar2-day fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?php echo e($todayAppointmentsCount); ?></h3>
                <small class="text-muted" style="font-size: 0.76rem;">Scheduled for <?php echo e(now()->format('M d')); ?></small>
            </div>
        </div>

        <!-- 3. Upcoming Appointments -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Upcoming</span>
                    <span class="p-2 bg-warning-subtle text-warning rounded-3"><i class="bi bi-hourglass-split fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?php echo e($upcomingCount); ?></h3>
                <small class="text-muted" style="font-size: 0.76rem;">Future active slots</small>
            </div>
        </div>

        <!-- 4. Doctors On Duty -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Doctors On Duty</span>
                    <span class="p-2 bg-success-subtle text-success rounded-3"><i class="bi bi-person-badge fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?php echo e($onDutyCount); ?></h3>
                <small class="text-success fw-medium" style="font-size: 0.76rem;">In clinic & active</small>
            </div>
        </div>

        <!-- 5. Doctors Available For New Appointments -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Available Now</span>
                    <span class="p-2 bg-teal-subtle text-success rounded-3"><i class="bi bi-check-circle fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-teal mb-0" style="color: var(--cms-teal);"><?php echo e($availableTodayCount); ?></h3>
                <small class="text-muted" style="font-size: 0.76rem;">Ready for immediate slot</small>
            </div>
        </div>

        <!-- 6. Completed Today -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Completed Today</span>
                    <span class="p-2 bg-success-subtle text-success rounded-3"><i class="bi bi-check-all fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-success mb-0"><?php echo e($completedTodayCount); ?></h3>
                <small class="text-muted" style="font-size: 0.76rem;">Consultations finished</small>
            </div>
        </div>

        <!-- 7. Cancelled Today -->
        <div class="col-6 col-md-4 col-xl">
            <div class="card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Cancelled Today</span>
                    <span class="p-2 bg-danger-subtle text-danger rounded-3"><i class="bi bi-x-circle fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-danger mb-0"><?php echo e($cancelledTodayCount); ?></h3>
                <small class="text-muted" style="font-size: 0.76rem;">Released appointment slots</small>
            </div>
        </div>
    </div>

    <!-- Main Workspace: Today's Appointments & Doctor Availability Panel -->
    <div class="row g-4 mb-4">
        <!-- B. Today's Appointment Table (Left / Center Column) -->
        <div class="col-12 col-xl-8">
            <div class="card-custom">
                <div class="card-header-clean d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-check text-primary fs-5"></i>
                        <span class="fw-bold text-dark">Today's Appointments Queue</span>
                        <span class="badge bg-primary rounded-pill"><?php echo e($todayAppointments->total()); ?></span>
                    </div>

                    <!-- Search & Filter Form for Today -->
                    <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="hidden" name="upcoming_filter" value="<?php echo e($upcomingFilter); ?>">
                        <div class="input-group input-group-sm" style="width: 190px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="today_search" value="<?php echo e(request('today_search')); ?>" class="form-control border-start-0" placeholder="Search patient/ID...">
                        </div>
                        <select name="today_status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($st); ?>" <?php echo e(request('today_status') === $st ? 'selected' : ''); ?>><?php echo e($st); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if(request('today_search') || request('today_status')): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-sm btn-light border" title="Clear filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th>Code & Time</th>
                                    <th>Patient Details</th>
                                    <th>Doctor & Specialty</th>
                                    <th>Primary Complaint</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $todayAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?php echo e($apt->formatted_time); ?></span>
                                            <small class="text-muted font-monospace"><?php echo e($apt->appointment_code); ?></small>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('patients.show', $apt->patient)); ?>" class="fw-bold text-decoration-none text-dark hover-underline">
                                                <?php echo e($apt->patient->name); ?>

                                            </a>
                                            <div class="text-muted" style="font-size: 0.78rem;">
                                                <span><?php echo e($apt->patient->effective_age); ?> yrs</span> • 
                                                <span><?php echo e($apt->patient->sex); ?></span> • 
                                                <span><?php echo e($apt->patient->phone); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark d-block"><?php echo e($apt->doctor->name); ?></span>
                                            <small class="text-muted"><?php echo e($apt->doctor->specialty->name); ?> • <span class="badge bg-light text-secondary border"><?php echo e($apt->doctor->consultation_room); ?></span></small>
                                        </td>
                                        <td>
                                            <div class="text-truncate text-secondary" style="max-width: 180px;" title="<?php echo e($apt->primary_complaint); ?>">
                                                <?php echo e($apt->primary_complaint); ?>

                                            </div>
                                            <?php if($apt->is_alternative_slot): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.7rem;">
                                                    Alternative slot
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="<?php echo e($apt->status_badge_class); ?> py-1 px-2 rounded-pill small">
                                                <?php echo e($apt->status); ?>

                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <!-- Quick Status Dropdown -->
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Change Status">
                                                        Status
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li class="dropdown-header small">Change Status</li>
                                                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($st !== $apt->status): ?>
                                                                <li>
                                                                    <form method="POST" action="<?php echo e(route('appointments.update_status', $apt)); ?>">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('PATCH'); ?>
                                                                        <input type="hidden" name="status" value="<?php echo e($st); ?>">
                                                                        <button type="submit" class="dropdown-item small">
                                                                            Mark as <?php echo e($st); ?>

                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                </div>

                                                <!-- Print Slip Button -->
                                                <a href="<?php echo e(route('appointments.slip', $apt)); ?>" class="btn btn-sm btn-light border py-1 px-2" title="Print Appointment Slip" target="_blank">
                                                    <i class="bi bi-printer text-secondary"></i>
                                                </a>

                                                <!-- Reschedule / Cancel Dropdown -->
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border py-1 px-2" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <a class="dropdown-item small" href="<?php echo e(route('appointments.show', $apt)); ?>">
                                                                <i class="bi bi-eye me-2"></i> View Full Details
                                                            </a>
                                                        </li>
                                                        <?php if($apt->status !== \App\Models\Appointment::STATUS_CANCELLED && $apt->status !== \App\Models\Appointment::STATUS_COMPLETED): ?>
                                                            <li>
                                                                <a class="dropdown-item small" href="<?php echo e(route('appointments.reschedule_form', $apt)); ?>">
                                                                    <i class="bi bi-arrow-repeat me-2 text-primary"></i> Reschedule
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item small text-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo e($apt->id); ?>">
                                                                    <i class="bi bi-x-circle me-2"></i> Cancel Slot
                                                                </button>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>

                                            <!-- Cancel Modal -->
                                            <div class="modal fade" id="cancelModal<?php echo e($apt->id); ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow">
                                                        <form method="POST" action="<?php echo e(route('appointments.cancel', $apt)); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <div class="modal-header">
                                                                <h6 class="modal-title fw-bold text-danger">Cancel Appointment #<?php echo e($apt->appointment_code); ?></h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p class="small text-muted mb-2">This will release the doctor's time slot for new bookings while preserving historical records.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Cancellation Reason (optional):</label>
                                                                    <input type="text" name="cancellation_reason" class="form-control form-control-sm" placeholder="e.g. Patient requested cancellation">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Keep Slot</button>
                                                                <button type="submit" class="btn btn-sm btn-danger">Confirm Cancellation</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary-subtle"></i>
                                            <p class="mb-1 fw-semibold">No appointments scheduled for today matching the filters.</p>
                                            <a href="<?php echo e(route('patients.create')); ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="bi bi-plus-lg me-1"></i> Book New Patient
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if($todayAppointments->hasPages()): ?>
                    <div class="card-footer bg-white border-top py-2 px-3">
                        <?php echo e($todayAppointments->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- C. Doctor Availability Panel (Right Column) -->
        <div class="col-12 col-xl-4">
            <div class="card-custom h-100 d-flex flex-column">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-heart-pulse text-danger fs-5"></i>
                        <span class="fw-bold text-dark">Doctor Availability Panel</span>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary small">Live Status</span>
                </div>

                <div class="card-body p-2 flex-grow-1" style="max-height: 580px; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $doctorStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $doc = $item['doctor'];
                                $st = $item['status'];
                            ?>
                            <div class="list-group-item p-3 border-bottom rounded mb-2 bg-light-subtle">
                                <div class="d-flex align-items-start justify-content-between mb-1">
                                    <div>
                                        <a href="<?php echo e(route('doctors.show', $doc)); ?>" class="fw-bold text-dark text-decoration-none">
                                            <?php echo e($doc->name); ?>

                                        </a>
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            <span><?php echo e($doc->specialty->name); ?></span> • 
                                            <span class="badge bg-white text-dark border"><?php echo e($doc->consultation_room); ?></span>
                                        </div>
                                    </div>
                                    <span class="<?php echo e($st['badge_class']); ?> px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                                        <?php echo e($st['status_label']); ?>

                                    </span>
                                </div>

                                <!-- Current Appointment Info -->
                                <?php if($st['current_appointment']): ?>
                                    <div class="bg-warning-subtle text-dark border border-warning-subtle rounded p-2 my-2" style="font-size: 0.78rem;">
                                        <i class="bi bi-clock-history me-1 text-warning"></i>
                                        <strong>Currently consulting:</strong> <?php echo e($st['current_appointment']->patient->name ?? 'Patient'); ?> 
                                        (<?php echo e(\Carbon\Carbon::parse($st['current_appointment']->appointment_time)->format('h:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($st['current_appointment']->end_time)->format('h:i A')); ?>)
                                    </div>
                                <?php endif; ?>

                                <!-- Next Available Slot & Today Bookings Count -->
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top" style="font-size: 0.76rem;">
                                    <span class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i> Today: <strong><?php echo e($st['appointments_today_count']); ?></strong> booked
                                    </span>

                                    <?php if($st['next_available_slot']): ?>
                                        <span class="text-primary fw-medium">
                                            Next: <?php echo e($st['next_available_slot']['start_formatted']); ?> (<?php echo e($st['next_available_slot']['date_formatted']); ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No free slots today</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4">No active doctors configured.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-2 text-center">
                    <a href="<?php echo e(route('schedules.index')); ?>" class="btn btn-sm btn-link text-decoration-none small">
                        View Full Weekly Duty Roster <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Upcoming Appointments Section -->
    <div class="card-custom mb-4">
        <div class="card-header-clean d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary fs-5"></i>
                <span class="fw-bold text-dark">Upcoming Appointments Pipeline</span>
            </div>

            <!-- Quick Filters (Today, Tomorrow, Next 7 days, Custom date range) -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?php echo e(route('dashboard', ['upcoming_filter' => 'today'])); ?>" class="btn <?php echo e($upcomingFilter === 'today' ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                        Today
                    </a>
                    <a href="<?php echo e(route('dashboard', ['upcoming_filter' => 'tomorrow'])); ?>" class="btn <?php echo e($upcomingFilter === 'tomorrow' ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                        Tomorrow
                    </a>
                    <a href="<?php echo e(route('dashboard', ['upcoming_filter' => 'next_7_days'])); ?>" class="btn <?php echo e($upcomingFilter === 'next_7_days' ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                        Next 7 Days
                    </a>
                </div>

                <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="d-flex align-items-center gap-1">
                    <input type="hidden" name="upcoming_filter" value="custom">
                    <input type="date" name="custom_start" value="<?php echo e(request('custom_start', now()->toDateString())); ?>" class="form-control form-control-sm" style="width: 130px;">
                    <span class="text-muted small">to</span>
                    <input type="date" name="custom_end" value="<?php echo e(request('custom_end', now()->addDays(14)->toDateString())); ?>" class="form-control form-control-sm" style="width: 130px;">
                    <button type="submit" class="btn btn-sm btn-light border" title="Filter range"><i class="bi bi-filter"></i></button>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient Name</th>
                            <th>Contact</th>
                            <th>Doctor & Specialty</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $upcomingAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?php echo e($apt->formatted_date); ?></span>
                                    <small class="text-muted"><?php echo e($apt->formatted_time); ?> - <?php echo e($apt->formatted_end_time); ?></small>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('patients.show', $apt->patient)); ?>" class="fw-semibold text-dark text-decoration-none">
                                        <?php echo e($apt->patient->name); ?>

                                    </a>
                                    <small class="text-muted d-block font-monospace"><?php echo e($apt->patient->patient_code); ?></small>
                                </td>
                                <td>
                                    <span><?php echo e($apt->patient->phone); ?></span>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark"><?php echo e($apt->doctor->name); ?></span>
                                    <small class="text-muted d-block"><?php echo e($apt->doctor->specialty->name); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo e($apt->doctor->consultation_room); ?></span>
                                </td>
                                <td>
                                    <span class="<?php echo e($apt->status_badge_class); ?> py-1 px-2 rounded-pill small">
                                        <?php echo e($apt->status); ?>

                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?php echo e(route('appointments.slip', $apt)); ?>" class="btn btn-sm btn-light border" target="_blank" title="Print Slip">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No upcoming appointments found for this filter period.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-3 text-end">
            <a href="<?php echo e(route('appointments.index')); ?>" class="btn btn-sm btn-link text-decoration-none">
                View All Appointments & Archive <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Daily Operations Report Modal -->
<div class="modal fade" id="downloadReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-1"></i> Generate Clinic Operations Report</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="<?php echo e(route('reports.daily')); ?>" id="reportForm" target="_blank">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Generates a complete report with appointment register, doctor patient counts, clinic department charts, and status outcomes.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Report Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Time Frame</label>
                        <select name="time_frame" id="modalTimeFrame" class="form-select" onchange="toggleCustomHours()">
                            <option value="24h" selected>24 Hours (Full Day Schedule)</option>
                            <option value="custom">Custom Time Window</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3 d-none" id="customHoursContainer">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">From Time</label>
                            <input type="time" name="start_time" class="form-control form-control-sm" value="08:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">To Time</label>
                            <input type="time" name="end_time" class="form-control form-control-sm" value="20:00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="downloadCsv()">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
                    </button>
                    <div>
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i> View & Print Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function toggleCustomHours() {
        const tf = document.getElementById('modalTimeFrame').value;
        const container = document.getElementById('customHoursContainer');
        container.classList.toggle('d-none', tf !== 'custom');
    }
    function downloadCsv() {
        const form = document.getElementById('reportForm');
        const old = form.action;
        form.action = "<?php echo e(route('reports.daily_csv')); ?>";
        form.submit();
        form.action = old;
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\hospital_management\resources\views/dashboard.blade.php ENDPATH**/ ?>