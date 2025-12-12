<!DOCTYPE html>
<html>
<head>
    <title>Payroll Report - {{ $payPeriod->start_date->format('M d, Y') }} to {{ $payPeriod->end_date->format('M d, Y') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9px; /* Base font size for the PDF */
        }

        .container {
            margin: 5px auto; /* Reduced margin */
            padding: 8px; /* Further reduced padding */
            width: 99%; /* Maximize width */
            max-width: 1200px; /* Allow wider content */
            background: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); /* Lighter shadow */
            border-radius: 6px;
            
        }

        .header {
            display: flex;
            justify-content: center; 
            align-items: center;
            margin-bottom: 12px; /* Reduced margin */
            padding-bottom: 8px; /* Reduced padding */
            border-bottom: 1px solid #eee; /* Lighter border */
        }

        .header .logo {
            text-align: center; 
            margin-right: 15px; /* Reduced margin */
        }

        .header .logo img {
            max-width: 50px; /* Further reduced logo size */
            height: auto;
        }

        .header .report-info {
            text-align: center; 
        }

        .header h1 {
            font-size: 16px; /* Further reduced heading size */
            color: #2c3e50;
            margin: 0;
        }

        .header p {
            font-size: 9px; /* Further reduced paragraph size */
            margin: 1px 0;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px; /* Reduced margin */
            table-layout: fixed; /* Ensures columns respect width */
        }

        th, td {
            border: 1px solid #e0e0e0;
            padding: 6px; /* Further reduced padding */
            text-align: left;
            font-size: 9px; /* Further reduced font size */
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2; /* Lighter background */
            font-weight: bold;
            color: #444;
        }

        td div {
            font-size: 8.5px; /* Smaller font for deduction details */
        }

        .total-section {
            text-align: right;
            margin-top: 15px; /* Reduced margin */
            padding-top: 10px; /* Reduced padding */
            border-top: 1px solid #eee;
        }

        .total-section p {
            font-size: 14px; /* Reduced font size */
            font-weight: bold;
            margin: 3px 0;
            color: #2c3e50;
        }

        .text-center {
            text-align: center;
        }

        h2 {
            font-size: 13px !important; /* Adjusted for department heading */
            margin-bottom: 8px !important;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Moved header block to individual department sections --}}
        {{-- <div class="header">
            <div class="logo">
                <img src="{{ public_path('limehills.png') }}" alt="Company Logo">
            </div>
            <div class="report-info">
                <h1>Payroll Report</h1>
                <p>For Period: {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</p>
            </div>
        </div> --}}

        @forelse($groupedPayrolls as $departmentName => $departmentPayslips)
            <div @if(!$loop->first) style="page-break-before: always;" @endif style="margin-bottom: 12px;"> 
                <div class="header">
                    <div class="logo">
                        <img src="{{ public_path('limehills.png') }}" alt="Company Logo">
                    </div>
                    <div class="report-info">
                        <h1>Payroll Report</h1>
                        <p>For Period: {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</p>
                    </div>
                </div>
                <h2 style="font-size: 13px; margin-bottom: 8px;">Department: {{ $departmentName }}</h2> 
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Work Days</th>
                            <th>Regular Work Hours</th>
                            <th>Overtime Hours</th>
                            <th>Total Work Hours</th>
                            <th>Total Late Minutes/Hours</th>
                            <th>Rate/Hour</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departmentPayslips as $payslip)
                        <tr>
                            <td style="font-weight: bold;">{{ $payslip->user->name }}</td>
                            @php
                                $details = is_array($payslip->details) ? $payslip->details : (json_decode($payslip->details, true) ?? []);
                                $sss = $details['sss_deduction'] ?? $details['sss'] ?? 0;
                                $phil = $details['philhealth_deduction'] ?? $details['philhealth'] ?? 0;
                                $pagibig = $details['pagibig_deduction'] ?? $details['pagibig'] ?? 0;
                                $other = $details['other_deductions'] ?? $details['other_deduction'] ?? 0;
                                $lateDeductionAmount = $details['late_deduction_amount'] ?? 0;
                                $hmoDeductions = $details['hmo_deductions'] ?? [];
                                $hmoTotal = 0;
                                if (is_array($hmoDeductions)) {
                                    $hmoTotal = array_sum(array_column($hmoDeductions, 'amount'));
                                }
                                $componentsTotal = $sss + $phil + $pagibig + $other + $lateDeductionAmount + $hmoTotal;
                            @endphp
                            <td>{{ $details['present_days'] ?? 0 }} / {{ $details['expected_working_days_in_period'] ?? 0 }}</td>
                            <td>{{ round($details['regular_work_hours'] ?? 0, 0) }}</td>
                            <td>{{ round($payslip->overtime_hours ?? 0, 2) }}</td>
                            <td>{{ round($payslip->total_hours_worked ?? 0, 0) }}</td>
                            <td>{{ floor(($payslip->late_minutes ?? 0) / 60) }} hrs ({{ floor($payslip->late_minutes ?? 0) }} mins)</td>
                            <td>₱{{ number_format($details['hourly_rate_computed'] ?? 0, 2) }}</td>
                            <td>₱{{ number_format($payslip->gross_pay, 2) }}</td>
                            <td>
                                @if($componentsTotal > 0)
                                    @if($sss > 0)
                                        <div>SSS: ₱{{ number_format($sss, 2) }}</div>
                                    @endif
                                    @if($phil > 0)
                                        <div>PhilHealth: ₱{{ number_format($phil, 2) }}</div>
                                    @endif
                                    @if($pagibig > 0)
                                        <div>Pag-IBIG: ₱{{ number_format($pagibig, 2) }}</div>
                                    @endif
                                    @if(is_array($hmoDeductions) && count($hmoDeductions) > 0)
                                        @foreach($hmoDeductions as $hmoDeduction)
                                            @if(isset($hmoDeduction['amount']) && $hmoDeduction['amount'] > 0)
                                                <div>HMO ({{ $hmoDeduction['name'] ?? 'N/A' }}): ₱{{ number_format($hmoDeduction['amount'], 2) }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                    @if($lateDeductionAmount > 0)
                                        <div>Late: ₱{{ number_format($lateDeductionAmount, 2) }}</div>
                                    @endif
                                    @if($other > 0)
                                        <div>Other: ₱{{ number_format($other, 2) }}</div>
                                    @endif
                                    <div>Total: ₱{{ number_format($payslip->deductions, 2) }}</div>
                                @else
                                    ₱{{ number_format($payslip->deductions, 2) }}
                                @endif
                            </td>
                            <td>₱{{ number_format($payslip->net_pay, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No payroll records found for this department.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="total-section">
                    <p>Overall Total Payroll for {{ $departmentName }}: ₱{{ number_format($departmentPayslips->sum('net_pay'), 2) }}</p>
                </div>
            </div>
            {{-- Removed explicit page-break-after to allow content to flow naturally --}}
            {{-- <div style="page-break-after: always;"></div> --}}
        @empty
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Work Days</th>
                        <th>Regular Work Hours</th>
                        <th>Overtime Hours</th>
                        <th>Total Work Hours</th>
                        <th>Total Late Minutes/Hours</th>
                        <th>Rate/Hour</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="10" class="text-center">No payroll records found for the selected period.</td>
                    </tr>
                </tbody>
            </table>
        @endforelse

        {{-- Removed the overall total payroll section --}}
        {{-- <div class="total-section">
            <p>Overall Total Payroll: ₱{{ number_format($payrolls->sum('net_pay'), 2) }}</p>
        </div> --}}

        {{-- Removed as per user request to move to a separate page --}}
        {{-- <div class="signature-section" style="margin-top: 50px; text-align: left;">
            <p style="margin-bottom: 5px; font-size: 14px;">Employee Signature:</p>
            <div style="border-bottom: 1px solid #000; width: 300px; padding-top: 20px;"></div>
            <p style="font-size: 12px; margin-top: 5px;">Printed Name / Date</p>
        </div> --}}
    </div>
    {{-- <div style="page-break-after: always;"></div> Removed to prevent empty page issues --}}
</body>
</html>
