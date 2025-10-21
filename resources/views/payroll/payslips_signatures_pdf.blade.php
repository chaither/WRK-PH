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
            margin: 10px auto; 
            padding: 15px; 
            width: 95%;
            max-width: 1100px;
            background: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 22px; 
            margin-bottom: 40px; 
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .signature-table th, .signature-table td {
            border: 1px solid #e0e0e0;
            padding: 12px 10px; 
            text-align: left;
            font-size: 12px; 
        }
        .signature-table th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #333;
        }
        .employee-name-cell {
            width: 40%; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .signature-cell {
            width: 35%; 
            height: 40px; 
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
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payroll Acknowledgment for Period: {{ $payPeriod->start_date->format('M d, Y') }} - {{ $payPeriod->end_date->format('M d, Y') }}</h1>

        <table class="signature-table">
            <thead>
                <tr>
                    <th class="employee-name-cell">Employee Name</th>
                    <th class="signature-cell">Signature</th>
                    <th class="date-cell">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrolls as $payslip)
                    <tr>
                        <td class="employee-name-cell">{{ $payslip->user->name }}</td>
                        <td class="signature-cell" style="padding-bottom: 5px;"></td> {{-- Space for signature --}}
                        <td class="date-cell" style="padding-bottom: 5px;"></td> {{-- Space for date --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
