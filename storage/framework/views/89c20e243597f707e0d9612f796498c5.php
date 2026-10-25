<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - <?php echo e(\App\Models\ClinicSetting::get('clinic_name', 'CarePoint Clinic')); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --cms-primary: #0f2942;
            --cms-primary-dark: #0a1b2d;
            --cms-primary-light: #1e3d5f;
            --cms-accent: #0284c7;
            --cms-teal: #0d9488;
            --cms-bg: #f8fafc;
            --cms-card-bg: #ffffff;
            --cms-border: #e2e8f0;
            --cms-text: #1e293b;
            --cms-muted: #64748b;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--cms-bg);
            color: var(--cms-text);
            min-height: 100vh;
        }

        /* Top Navigation Bar */
        .topbar {
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid var(--cms-border);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid var(--cms-border);
            min-height: calc(100vh - 64px);
            position: sticky;
            top: 64px;
            display: flex;
            flex-direction: column;
            transition: all 0.2s ease-in-out;
        }

        .sidebar-brand {
            padding: 18px 20px;
            border-bottom: 1px solid var(--cms-border);
            background: #ffffff;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            color: var(--cms-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            border-radius: 8px;
            margin: 3px 12px;
            transition: all 0.15s ease-in-out;
        }

        .nav-link-custom:hover {
            color: var(--cms-primary);
            background-color: #f1f5f9;
        }

        .nav-link-custom.active {
            color: #ffffff;
            background-color: var(--cms-primary);
            box-shadow: 0 2px 4px rgba(15, 41, 66, 0.18);
        }

        .nav-link-custom i {
            font-size: 1.15rem;
            width: 22px;
            text-align: center;
        }

        /* Cards & Content */
        .card-custom {
            background: var(--cms-card-bg);
            border: 1px solid var(--cms-border);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: box-shadow 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
        }

        .card-header-clean {
            background: transparent;
            border-bottom: 1px solid var(--cms-border);
            padding: 14px 18px;
            font-weight: 600;
            color: var(--cms-primary);
        }

        /* Status Badges */
        .badge-scheduled { background-color: #e0f2fe; color: #0369a1; }
        .badge-checkedin { background-color: #fef3c7; color: #b45309; }
        .badge-inconsultation { background-color: #fce7f3; color: #be185d; }
        .badge-completed { background-color: #dcfce7; color: #15803d; }
        .badge-cancelled { background-color: #fee2e2; color: #b91c1c; }
        .badge-noshow { background-color: #f1f5f9; color: #475569; }

        .btn-cms-primary {
            background-color: var(--cms-primary);
            color: #ffffff;
            border: none;
        }
        .btn-cms-primary:hover {
            background-color: var(--cms-primary-light);
            color: #ffffff;
        }

        .btn-cms-teal {
            background-color: var(--cms-teal);
            color: #ffffff;
            border: none;
        }
        .btn-cms-teal:hover {
            background-color: #0f766e;
            color: #ffffff;
        }

        /* Responsive Mobile Layout */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -260px;
                top: 64px;
                bottom: 0;
                z-index: 1030;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }
            .sidebar.show {
                left: 0;
            }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="topbar px-3 px-lg-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-lg-none border" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary text-white rounded-3 p-1 px-2 d-flex align-items-center justify-content-center" style="background-color: var(--cms-primary) !important;">
                    <i class="bi bi-hospital fs-5"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><?php echo e(\App\Models\ClinicSetting::get('clinic_name', 'CarePoint Outpatient Clinic')); ?></h6>
                    <small class="text-muted d-none d-md-inline"><?php echo e(\App\Models\ClinicSetting::get('clinic_tagline', 'Reception Administration & Scheduling')); ?></small>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Current Date & Time Live Display -->
            <div class="d-none d-md-flex align-items-center gap-2 text-muted px-3 py-1 bg-light rounded-pill border small">
                <i class="bi bi-calendar3 text-primary"></i>
                <span id="liveClock"><?php echo e(now()->format('l, M d, Y - h:i A')); ?></span>
            </div>

            <!-- Quick Appointment Action Button -->
            <a href="<?php echo e(route('patients.create')); ?>" class="btn btn-sm btn-cms-primary d-flex align-items-center gap-1 shadow-sm px-3">
                <i class="bi bi-person-plus-fill"></i>
                <span>Register & Book</span>
            </a>

            <!-- Admin Profile Menu -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-badge text-primary"></i>
                    <span class="fw-semibold small"><?php echo e(Auth::user()->name ?? 'Reception Staff'); ?></span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li class="dropdown-header text-uppercase small fw-bold">Signed in as</li>
                    <li class="px-3 py-1 small text-dark fw-bold"><?php echo e(Auth::user()->email ?? ''); ?></li>
                    <li><span class="dropdown-item-text text-muted small"><span class="badge bg-primary">Reception Admin</span></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item small" href="<?php echo e(route('settings.index')); ?>">
                            <i class="bi bi-gear me-2"></i> Clinic Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item text-danger small">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="d-flex">
        <!-- Sidebar Navigation (10 Specified Items) -->
        <aside class="sidebar py-3" id="sidebarNav">
            <div class="px-3 mb-2">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05rem;">Reception Desk</small>
            </div>
            
            <nav class="nav flex-column mb-auto">
                <!-- 1. Dashboard -->
                <a href="<?php echo e(route('dashboard')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                <!-- 2. Register Patient -->
                <a href="<?php echo e(route('patients.create')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('patients.create') ? 'active' : ''); ?>">
                    <i class="bi bi-person-plus"></i>
                    <span>Register Patient</span>
                </a>

                <!-- 3. All Patients -->
                <a href="<?php echo e(route('patients.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('patients.index') || (request()->routeIs('patients.*') && !request()->routeIs('patients.create')) ? 'active' : ''); ?>">
                    <i class="bi bi-people"></i>
                    <span>All Patients</span>
                </a>

                <!-- 4. Appointments -->
                <a href="<?php echo e(route('appointments.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('appointments.*') ? 'active' : ''); ?>">
                    <i class="bi bi-calendar-check"></i>
                    <span>Appointments</span>
                </a>

                <!-- 5. Doctors -->
                <a href="<?php echo e(route('doctors.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('doctors.*') ? 'active' : ''); ?>">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>Doctors</span>
                </a>

                <!-- 6. Doctor Schedules -->
                <a href="<?php echo e(route('schedules.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('schedules.*') ? 'active' : ''); ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Doctor Schedules</span>
                </a>

                <!-- 7. Duty Roster -->
                <a href="<?php echo e(route('roster.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('roster.*') ? 'active' : ''); ?>">
                    <i class="bi bi-calendar-week"></i>
                    <span>Duty Roster</span>
                </a>

                <!-- 8. Reports -->
                <a href="<?php echo e(route('reports.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reports</span>
                </a>

                <!-- 9. Settings -->
                <a href="<?php echo e(route('settings.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="px-3 pt-3 border-top mt-3">
                <!-- 10. Logout -->
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="nav-link-custom text-danger w-100 border-0 bg-transparent text-start">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow-1 p-3 p-lg-4" style="min-width: 0;">
            <!-- System Flash Messages -->
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div><?php echo e(session('success')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    <div><?php echo e(session('error')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('info')): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-info-circle-fill text-info fs-5"></i>
                    <div><?php echo e(session('info')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Please correct the following issues:</div>
                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Mobile Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarNav = document.getElementById('sidebarNav');
        if (sidebarToggle && sidebarNav) {
            sidebarToggle.addEventListener('click', () => {
                sidebarNav.classList.toggle('show');
            });
        }

        // Live Clock Tick
        setInterval(() => {
            const clockEl = document.getElementById('liveClock');
            if (clockEl) {
                const now = new Date();
                const options = { weekday: 'long', month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
                clockEl.textContent = now.toLocaleDateString('en-US', options);
            }
        }, 1000);
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\hospital_management\resources\views/layouts/app.blade.php ENDPATH**/ ?>