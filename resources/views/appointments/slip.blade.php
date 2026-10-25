<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Slip - {{ $appointment->appointment_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1e293b;
            padding: 24px;
        }

        .slip-container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            padding: 28px;
        }

        .slip-header {
            text-align: center;
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .slip-footer {
            border-top: 2px dashed #e2e8f0;
            padding-top: 14px;
            margin-top: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .slip-info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .slip-info-label {
            color: #64748b;
            font-weight: 500;
        }

        .slip-info-value {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .slip-container {
                border: 1px solid #000;
                box-shadow: none;
                max-width: 100%;
                border-radius: 0;
                padding: 15px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            <i class="bi bi-printer-fill me-1"></i> Print Appointment Slip
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-3 ms-2">
            Return to Dashboard
        </a>
    </div>

    <div class="slip-container">
        <!-- Clinic Header -->
        <div class="slip-header">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                <i class="bi bi-hospital fs-4 text-primary"></i>
                <h4 class="fw-bold mb-0 text-dark">{{ $clinicName }}</h4>
            </div>
            <p class="mb-0 text-muted small">{{ $clinicAddress }} • Tel: {{ $clinicPhone }}</p>
            <div class="badge bg-light text-dark border mt-2 px-3 py-1 font-monospace fs-6">
                {{ $appointment->appointment_code }}
            </div>
        </div>

        <!-- Appointment Key Details -->
        <div class="mb-3">
            <div class="slip-info-row">
                <span class="slip-info-label">Patient Name:</span>
                <span class="slip-info-value">{{ $appointment->patient->name }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Patient ID:</span>
                <span class="slip-info-value font-monospace">{{ $appointment->patient->patient_code }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Patient Phone:</span>
                <span class="slip-info-value">{{ $appointment->patient->phone }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Assigned Doctor:</span>
                <span class="slip-info-value text-primary">{{ $appointment->doctor->name }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Specialty:</span>
                <span class="slip-info-value">{{ $appointment->doctor->specialty->name }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Consultation Room:</span>
                <span class="slip-info-value badge bg-dark text-white">{{ $appointment->doctor->consultation_room }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Appointment Date:</span>
                <span class="slip-info-value">{{ $appointment->formatted_date }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Scheduled Time Window:</span>
                <span class="slip-info-value">{{ $appointment->formatted_time }} - {{ $appointment->formatted_end_time }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Appointment Status:</span>
                <span class="slip-info-value">{{ $appointment->status }}</span>
            </div>
            <div class="slip-info-row">
                <span class="slip-info-label">Primary Complaint:</span>
                <span class="slip-info-value">{{ $appointment->primary_complaint }}</span>
            </div>
        </div>

        <!-- Instructions to Patient -->
        <div class="bg-light p-3 rounded-2 border my-3">
            <div class="fw-bold small text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Patient Instructions:</div>
            <ul class="mb-0 small text-secondary ps-3" style="font-size: 0.82rem;">
                <li>Please arrive at the clinic reception <strong>10 to 15 minutes prior</strong> to your scheduled consultation time.</li>
                <li>Present this appointment slip or your Patient ID code (<strong>{{ $appointment->patient->patient_code }}</strong>) at the reception desk to check in.</li>
                <li>If you need to reschedule or cancel your visit, please contact our reception desk at least 2 hours in advance.</li>
            </ul>
        </div>

        <!-- Barcode / Reference Placeholder & Timestamp -->
        <div class="slip-footer">
            <div>Issued by Reception Staff • {{ now()->format('Y-m-d H:i:s') }}</div>
            <div class="text-uppercase fw-bold mt-1" style="letter-spacing: 0.1rem; font-size: 0.72rem;">CarePoint Outpatient Clinic Management System</div>
        </div>
    </div>
</body>
</html>
