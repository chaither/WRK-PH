<!DOCTYPE html>
<html>
<head>
    <title>Payroll Signatures - {{ $payPeriod->start_date->format('M d, Y') }} to {{ $payPeriod->end_date->format('M d, Y') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            page-break-before: always;
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
        h1 {
            font-size: 18px; /* Reduced heading size */
            color: #2c3e50;
            margin: 0;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table th, .signature-table td {
            border: 1px solid #e0e0e0;
            padding: 8px; /* Reduced padding */
            text-align: left;
            font-size: 10px; /* Reduced font size */
        }
        .signature-table th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #333;
        }
        .employee-name-cell {
            width: 25%; /* Adjust width for full name */
            text-align: left;
            font-size: 10px;
        }
        .signature-cell {
            width: 35%; 
            height: 20px; /* Reduced height */
            vertical-align: bottom;
            border-bottom: 1px solid #000;
            padding-left: 0;
            padding-right: 0;
        }
        .date-cell {
            width: 25%;
            vertical-align: bottom;
            border-bottom: 1px solid #000;
            padding-left: 0;
            padding-right: 0;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Removed the single grand header --}}
        {{-- <h1>Payroll Acknowledgment for Period: {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</h1> --}}

        @foreach($groupedPayrolls as $departmentName => $departmentPayslips)
            <div style="margin-top: 10px; @if(!$loop->first) page-break-before: always; @endif">
                <h1>Payroll Acknowledgment for {{ $departmentName }} <br> Period: {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</h1>

                <table class="signature-table">
                    <thead>
                        <tr>
                            <th class="employee-name-cell">Employee Name</th>
                            <th class="signature-cell">Signature</th>
                            <th class="date-cell">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentPayslips as $payslip)
                            <tr>
                                <td class="employee-name-cell">{{ $payslip->user->first_name }} {{ $payslip->user->last_name }}</td>
                                <td class="signature-cell" style="padding-bottom: 5px;"></td> {{-- Space for signature --}}
                                <td class="date-cell" style="padding-bottom: 5px;"></td> {{-- Space for date --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</body>
</html>
