<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reception Admin Login - CarePoint Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
        }
        .login-card {
            max-width: 460px;
            width: 100%;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .login-header {
            background-color: #0f2942;
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .btn-cms-primary {
            background-color: #0f2942;
            color: #ffffff;
            font-weight: 600;
            border: none;
            padding: 10px;
        }
        .btn-cms-primary:hover {
            background-color: #1e3d5f;
            color: #ffffff;
        }
        .demo-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex p-3 rounded-circle bg-white text-dark mb-2 shadow-sm">
                <i class="bi bi-hospital fs-3 text-primary" style="color: #0f2942 !important;"></i>
            </div>
            <h4 class="fw-bold mb-1">CarePoint Clinic</h4>
            <p class="mb-0 text-white-50 small">Reception Administration & Scheduling</p>
        </div>

        <div class="p-4 p-md-4">
            <h5 class="fw-bold text-dark mb-1">Reception Desk Sign In</h5>
            <p class="text-muted small mb-4">Enter your staff credentials to access patient appointments.</p>

            @if(session('info'))
                <div class="alert alert-info py-2 small mb-3">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-secondary">Work Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', 'admin@carepoint.com') }}" required autofocus placeholder="name@carepoint.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                               id="password" name="password" required value="password" placeholder="••••••••">
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small text-muted" for="remember">
                            Remember me
                        </label>
                    </div>
                    <span class="small text-muted"><i class="bi bi-shield-check text-success"></i> Secure Session</span>
                </div>

                <button type="submit" class="btn btn-cms-primary w-100 rounded-3 shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Dashboard
                </button>
            </form>

            <!-- Demo Accounts Reference Box -->
            <div class="demo-box mt-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark"><i class="bi bi-info-circle text-primary me-1"></i> Demo Reception Accounts:</span>
                    <span class="badge bg-secondary-subtle text-secondary">Default password: <code>password</code></span>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-2 border rounded bg-white" style="cursor: pointer;" onclick="document.getElementById('email').value='admin@carepoint.com'">
                            <div class="fw-bold small text-truncate">Eleanor Vance</div>
                            <div class="text-muted text-truncate" style="font-size: 0.76rem;">admin@carepoint.com</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-white" style="cursor: pointer;" onclick="document.getElementById('email').value='sarah.reception@carepoint.com'">
                            <div class="fw-bold small text-truncate">Sarah Jenkins</div>
                            <div class="text-muted text-truncate" style="font-size: 0.76rem;">sarah.reception@carepoint.com</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-white" style="cursor: pointer;" onclick="document.getElementById('email').value='david.reception@carepoint.com'">
                            <div class="fw-bold small text-truncate">David Miller</div>
                            <div class="text-muted text-truncate" style="font-size: 0.76rem;">david.reception@carepoint.com</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-white" style="cursor: pointer;" onclick="document.getElementById('email').value='emily.reception@carepoint.com'">
                            <div class="fw-bold small text-truncate">Emily Clark</div>
                            <div class="text-muted text-truncate" style="font-size: 0.76rem;">emily.reception@carepoint.com</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
