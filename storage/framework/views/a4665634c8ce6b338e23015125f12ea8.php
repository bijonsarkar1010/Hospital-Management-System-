<?php $__env->startSection('title', 'Weekly Duty Roster & Doctor Schedules'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Weekly Doctor Duty Roster</h4>
            <p class="text-muted small mb-0">Master schedule of physician working hours, consultation rooms, and duty coverage across all 7 days.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 fs-6">Today: <?php echo e(now()->format('l, M d')); ?></span>
        </div>
    </div>

    <div class="card-custom">
        <div class="card-header-clean d-flex align-items-center justify-content-between">
            <span class="fw-bold">Physician Schedule Grid</span>
            <small class="text-muted">Total active doctors: <?php echo e($doctors->count()); ?></small>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-3" style="min-width: 200px;">Doctor & Specialty</th>
                            <th style="min-width: 90px;">Room</th>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayNum => $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th style="min-width: 120px;" class="<?php echo e(now()->dayOfWeek === $dayNum ? 'table-primary border-primary text-primary fw-bold' : ''); ?>">
                                    <?php echo e($dayName); ?>

                                    <?php if(now()->dayOfWeek === $dayNum): ?>
                                        <span class="badge bg-primary text-white d-block mt-1" style="font-size: 0.65rem;">TODAY</span>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th style="min-width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $schedByDay = $d->schedules->keyBy('day_of_week');
                                $hasActiveLeave = $d->unavailabilities->isNotEmpty();
                            ?>
                            <tr>
                                <td class="text-start ps-3">
                                    <a href="<?php echo e(route('doctors.show', $d)); ?>" class="fw-bold text-dark text-decoration-none d-block">
                                        <?php echo e($d->name); ?>

                                    </a>
                                    <small class="text-muted"><?php echo e($d->specialty->name); ?> (<?php echo e($d->consultation_duration); ?>m)</small>
                                    <?php if($hasActiveLeave): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-block mt-1" style="font-size: 0.68rem;">
                                            Leave: <?php echo e($d->unavailabilities->first()->reason ?? 'Leave'); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo e($d->consultation_room); ?></span>
                                </td>

                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayNum => $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $sch = $schedByDay->get($dayNum); ?>
                                    <td class="<?php echo e(now()->dayOfWeek === $dayNum ? 'bg-primary-subtle bg-opacity-25' : ''); ?>">
                                        <?php if($sch && $sch->is_available): ?>
                                            <span class="fw-semibold text-dark d-block">
                                                <?php echo e(\Carbon\Carbon::parse($sch->start_time)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($sch->end_time)->format('H:i')); ?>

                                            </span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">On Duty</span>
                                        <?php else: ?>
                                            <span class="text-muted small">--</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <td>
                                    <a href="<?php echo e(route('schedules.manage', $d)); ?>" class="btn btn-sm btn-light border py-1 px-2" title="Manage Schedule">
                                        <i class="bi bi-gear text-secondary"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\hospital_management\resources\views/schedules/index.blade.php ENDPATH**/ ?>