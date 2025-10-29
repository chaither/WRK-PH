<!DOCTYPE html>
<html>
<head>
    <title>Leave Request Reason - #{{ $leaveRequest->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #444;
            text-align: center;
            margin-bottom: 20px;
        }
        .details p {
            margin-bottom: 10px;
        }
        .reason {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .reason h2 {
            color: #555;
            margin-bottom: 15px;
        }
        .reason pre {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Leave Request Details</h1>
        <div class="details">
            <p><strong>Request ID:</strong> #{{ $leaveRequest->id }}</p>
            <p><strong>Employee Name:</strong> {{ $leaveRequest->user->name }}</p>
            <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('F d, Y') }}</p>
            <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('F d, Y') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($leaveRequest->status) }}</p>
            <p><strong>Requested On:</strong> {{ \Carbon\Carbon::parse($leaveRequest->created_at)->format('F d, Y h:i A') }}</p>
        </div>

        <div class="reason">
            <h2>View Letter:</h2>
            <pre>{{ $leaveRequest->reason }}</pre>
        </div>
    </div>
</body>
</html>
