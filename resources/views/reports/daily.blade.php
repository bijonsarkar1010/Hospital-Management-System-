<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Operations Report - {{ \Carbon\Carbon::parse($reportDate)->format('M d, Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f8fafc; color: #1e293b; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 24px; }
        .report-sheet { max-width: 1150px; margin: 0 auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .chart-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
        @media print { body { background: #fff; padding: 0; } .report-sheet { border: none; box-shadow: none; padding: 0; max-width: 100%; } .no-print { display: none !important; } .chart-box { break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="no-print d-flex align-items-center justify-content-between mx-auto mb-3" style="max-width: 1150px;">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.daily_csv', request()->all()) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV</a>
            <button onclick="window.print()" class="btn btn-sm btn-primary"><i class="bi bi-printer me-1"></i> Print / Save as PDF</button>
        </div>
    </div>
    <div class="report-sheet">
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">{{ $clinicName }}</h3>
                <h5 class="text-secondary fw-semibold mb-0">Daily Operations & Appointment Report</h5>
            </div>
            <div class="text-end small">
                <div><strong>Report Target Date:</strong> {{ \Carbon\Carbon::parse($reportDate)->format('l, F j, Y') }}</div>
                <div><strong>Time Frame:</strong> {{ $timeFrame === '24h' ? 'Full 24 Hours' : "$startTime to $endTime" }}</div>
                <div class="text-muted"><strong>Generated At:</strong> {{ $generatedAt->format('Y-m-d h:i:s A') }}</div>
            </div>
        </div>
        <div class="row g-2 mb-4 text-center">
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">Total Booked</small><h4 class="fw-bold mb-0 text-dark">{{ $totalAppointments }}</h4></div></div>
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">Completed</small><h4 class="fw-bold mb-0 text-success">{{ $completedCount }}</h4></div></div>
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">In Consult</small><h4 class="fw-bold mb-0 text-warning">{{ $inConsultationCount }}</h4></div></div>
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">Checked In</small><h4 class="fw-bold mb-0 text-info">{{ $checkedInCount }}</h4></div></div>
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">Scheduled</small><h4 class="fw-bold mb-0 text-primary">{{ $scheduledCount }}</h4></div></div>
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">Cancelled</small><h4 class="fw-bold mb-0 text-danger">{{ $cancelledCount }}</h4></div></div>
            <div class="col"><div class="p-2 border rounded bg-light"><small class="text-muted d-block fw-semibold">No Show</small><h4 class="fw-bold mb-0 text-secondary">{{ $noShowCount }}</h4></div></div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-8">
                <div class="chart-box h-100">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-bar-chart-fill text-primary me-1"></i> Department / Clinic-Wise Volume Comparison</h6>
                    <div style="height: 240px; position: relative;"><canvas id="specialtyChart"></canvas></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="chart-box h-100">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-pie-chart-fill text-info me-1"></i> Status Distribution</h6>
                    <div style="height: 240px; position: relative;"><canvas id="statusChart"></canvas></div>
                </div>
            </div>
            <div class="col-12">
                <div class="chart-box">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-people-fill text-success me-1"></i> Physician Patient Volume (Total Assigned vs Completed)</h6>
                    <div style="height: 220px; position: relative;"><canvas id="doctorChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="card mb-4 border">
            <div class="card-header bg-light py-2 fw-bold text-dark"><i class="bi bi-person-lines-fill text-primary me-1"></i> Physician Patient Workload Breakdown</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 small">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-3">Doctor Name</th>
                            <th>Specialty</th>
                            <th>Room</th>
                            <th class="text-center">Total Assigned</th>
                            <th class="text-center text-success">Completed</th>
                            <th class="text-center text-warning">In Consult</th>
                            <th class="text-center text-info">Checked In</th>
                            <th class="text-center text-primary">Scheduled</th>
                            <th class="text-center text-danger">Cancelled</th>
                            <th class="text-center text-secondary">No Show</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctorWorkloads as $dw)
                            @if($dw['total_assigned'] > 0)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">{{ $dw['doctor']->name }}</td>
                                    <td>{{ $dw['doctor']->specialty->name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $dw['doctor']->consultation_room }}</span></td>
                                    <td class="text-center fw-bold fs-6">{{ $dw['total_assigned'] }}</td>
                                    <td class="text-center text-success fw-bold">{{ $dw['completed'] }}</td>
                                    <td class="text-center text-warning">{{ $dw['in_consultation'] }}</td>
                                    <td class="text-center text-info">{{ $dw['checked_in'] }}</td>
                                    <td class="text-center text-primary">{{ $dw['scheduled'] }}</td>
                                    <td class="text-center text-danger">{{ $dw['cancelled'] }}</td>
                                    <td class="text-center text-secondary">{{ $dw['no_show'] }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="10" class="text-center py-3 text-muted">No physician appointments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card border">
            <div class="card-header bg-light py-2 fw-bold text-dark"><i class="bi bi-card-checklist text-primary me-1"></i> Complete Appointment Register ({{ $appointments->count() }} records)</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0 small" style="font-size: 0.8rem;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="ps-3">Code & Time</th>
                            <th>Patient Name & ID</th>
                            <th>Contact</th>
                            <th>Doctor & Specialty</th>
                            <th>Room</th>
                            <th>Complaint</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $apt)
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold">{{ $apt->formatted_time }}</span>
                                    <small class="text-muted d-block font-monospace">{{ $apt->appointment_code }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $apt->patient->name }}</span>
                                    <small class="text-muted d-block">{{ $apt->patient->patient_code }} • {{ $apt->patient->effective_age }}y {{ $apt->patient->sex }}</small>
                                </td>
                                <td>{{ $apt->patient->phone }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $apt->doctor->name }}</span>
                                    <small class="text-muted d-block">{{ $apt->doctor->specialty->name }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $apt->doctor->consultation_room }}</span></td>
                                <td><div class="text-truncate" style="max-width: 160px;">{{ $apt->primary_complaint }}</div></td>
                                <td><span class="{{ $apt->status_badge_class }} px-2 py-1 rounded-pill small">{{ $apt->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No appointments found for this date and timeframe.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4 pt-3 border-top text-center text-muted small">
            End of Official Operations Report • Generated on {{ $generatedAt->format('M d, Y h:i A') }}
        </div>
    </div>
    <script>
        const specialtyLabels = @json(array_column($specialtyComparison, 'name'));
        const specialtyTotals = @json(array_column($specialtyComparison, 'total'));

        new Chart(document.getElementById('specialtyChart'), {
            type: 'bar',
            data: {
                labels: specialtyLabels.length ? specialtyLabels : ['No Data'],
                datasets: [{ label: 'Appointments', data: specialtyTotals.length ? specialtyTotals : [0], backgroundColor: '#0284c7', borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, animation: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Consult', 'Checked In', 'Scheduled', 'Cancelled', 'No Show'],
                datasets: [{
                    data: [{{ $completedCount }}, {{ $inConsultationCount }}, {{ $checkedInCount }}, {{ $scheduledCount }}, {{ $cancelledCount }}, {{ $noShowCount }}],
                    backgroundColor: ['#16a34a', '#eab308', '#06b6d4', '#2563eb', '#dc2626', '#64748b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, animation: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
        });

        @php
            $activeDocs = array_filter($doctorWorkloads, fn($dw) => $dw['total_assigned'] > 0);
            $topDocs = array_slice($activeDocs, 0, 8);
            $docNames = array_map(fn($dw) => $dw['doctor']->name, $topDocs);
            $docAssigned = array_map(fn($dw) => $dw['total_assigned'], $topDocs);
            $docCompleted = array_map(fn($dw) => $dw['completed'], $topDocs);
        @endphp

        new Chart(document.getElementById('doctorChart'), {
            type: 'bar',
            data: {
                labels: @json($docNames),
                datasets: [
                    { label: 'Assigned Patients', data: @json($docAssigned), backgroundColor: '#3b82f6', borderRadius: 4 },
                    { label: 'Completed Consultations', data: @json($docCompleted), backgroundColor: '#10b981', borderRadius: 4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, animation: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
    </script>
</body>
</html>
