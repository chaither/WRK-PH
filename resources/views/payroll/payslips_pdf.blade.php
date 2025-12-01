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
        }

        .container {
            margin: 5px auto; /* Reduced margin */
            padding: 10px; /* Reduced padding */
            width: 98%; /* Increased width */
            max-width: 1100px;
            background: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            
        }

        .header {
            display: flex;
            justify-content: center; 
            align-items: center;
            margin-bottom: 15px; /* Reduced margin */
            padding-bottom: 10px; /* Reduced padding */
            border-bottom: 2px solid #eee;
        }

        .header .logo {
            text-align: center; 
            margin-right: 20px; 
        }

        .header .logo img {
            max-width: 60px; /* Reduced logo size */
            height: auto;
        }

        .header .report-info {
            text-align: center; 
        }

        .header h1 {
            font-size: 18px; /* Reduced heading size */
            color: #2c3e50;
            margin: 0;
        }

        .header p {
            font-size: 10px; /* Reduced paragraph size */
            margin: 2px 0;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #e0e0e0;
            padding: 8px; /* Reduced padding */
            text-align: left;
            font-size: 10px; /* Reduced font size */
            word-wrap: break-word;
        }

        th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #333;
        }

        .total-section {
            text-align: right;
            margin-top: 20px; /* Reduced margin */
            padding-top: 15px; /* Reduced padding */
            border-top: 1px solid #eee;
        }

        .total-section p {
            font-size: 16px; 
            font-weight: bold;
            margin: 5px 0;
            color: #2c3e50;
        }

        .text-center {
            text-align: center;
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
            <div style="margin-bottom: 15px; @if(!$loop->first) page-break-before: always; @endif"> 
                <div class="header">
                    <div class="logo">
                        <img src="{{ public_path('limehills.png') }}" alt="Company Logo">
                    </div>
                    <div class="report-info">
                        <h1>Payroll Report</h1>
                        <p>For Period: {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</p>
                    </div>
                </div>
                <h2 style="font-size: 14px; margin-bottom: 10px;">Department: {{ $departmentName }}</h2> 
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Basic Pay</th>
                            <th>Overtime Pay</th>
                            <th>Late Deductions</th>
                            <th>SSS</th>
                            <th>Pag-IBIG</th>
                            <th>PhilHealth</th>
                            <th>Total Deductions</th>
                            <th>Net Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departmentPayslips as $payslip)
                        <tr>
                            <td style="font-weight: bold;">{{ $payslip->user->name }}</td>
                            <td>₱{{ number_format((($payslip->gross_pay ?? 0) - ($payslip->overtime_pay ?? 0)), 2) }}</td>
                            <td>₱{{ number_format($payslip->overtime_pay ?? 0, 2) }}</td>
                            <td>₱{{ number_format($payslip->late_deductions ?? 0, 2) }}</td>
                            @php
                                $details = is_array($payslip->details) ? $payslip->details : (json_decode($payslip->details, true) ?? []);
                                $sss = $details['sss_deduction'] ?? $details['sss'] ?? 0;
                                $phil = $details['philhealth_deduction'] ?? $details['philhealth'] ?? 0;
                                $pagibig = $details['pagibig_deduction'] ?? $details['pagibig'] ?? 0;
                                // $otherDetails = $details['other_deductions'] ?? $details['other_deduction'] ?? 0;

                                // If the total deductions were manually updated (and stored in payslip->deductions),
                                // show the remainder as "Other Deductions" so the PDF reflects what was actually deducted.
                                // $componentsSum = $sss + $phil + $pagibig + $otherDetails;
                                $totalDeductions = $payslip->deductions ?? 0;
                                // $manualRemainder = max(0, $totalDeductions - $componentsSum);
                                // $otherShown = $otherDetails + $manualRemainder;
                            @endphp

                            <td>₱{{ number_format($sss, 2) }}</td>
                            <td>₱{{ number_format($pagibig, 2) }}</td>
                            <td>₱{{ number_format($phil, 2) }}</td>
                            <td>₱{{ number_format($totalDeductions, 2) }}</td>
                            <td>₱{{ number_format($payslip->net_pay ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No payroll records found for this department.</td>
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
                        <th>Basic Pay</th>
                        <th>Overtime Pay</th>
                        <th>Late Deductions</th>
                        <th>SSS</th>
                        <th>Pag-IBIG</th>
                        <th>PhilHealth</th>
                        <th>Total Deductions</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="9" class="text-center">No payroll records found for the selected period.</td>
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
