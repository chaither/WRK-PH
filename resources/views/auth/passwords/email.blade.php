<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRK Services PH HRIS - Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>

        /* HRIS Theme: Deep Ocean Blue Gradient - Professional & Calming */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #051534 0%, #0b2059 55%, #0e3280 100%);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
            color: white;
        }

        /* Login Card: Clean, Elevated, and Inviting */
        .login-card {
            background: #FFFFFF;
            border-radius: 1.5rem; /* Slightly reduced radius for more compactness */
            box-shadow: 0 20px 60px rgba(0,0,0,0.09); /* Softer shadow */
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1.05);
            color: #1F2937;
            border: none;
        }

        .login-card:hover {
            transform: translateY(-5px); /* Reduced lift on hover */
            box-shadow: 0 25px 75px rgba(0,0,0,0.12);
        }

        /* Input Fields: Clear, Accessible, and Subtle */
        .input-field {
            background: #F8FAFC;
            color: #1A202C;
            border: 1px solid #E2E8F0;
            border-radius: 0.75rem;
            padding: 0.85rem 1.15rem; /* Further reduced padding for inputs */
            font-size: 0.9rem; /* Slightly smaller input text */
            transition: all 0.3s ease-in-out;
        }

        .input-field::placeholder {
            color: #94A3B8;
        }

        .input-field:focus {
            outline: none;
            border-color: #4299E1;
            box-shadow: 0 0 0 4px rgba(66, 153, 225, 0.2);
            background: white;
        }

        /* Button: Strong, Trustworthy, and Engaging */
        .btn-gradient {
            background: linear-gradient(90deg, #3182CE 0%, #4299E1 100%);
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12); /* Slightly softer shadow */
            font-size: 1.05rem; /* Further smaller button text */
            padding: 0.85rem 1.5rem; /* Reduced button padding */
            border-radius: 0.9rem;
            letter-spacing: 0.01em; /* Reduced letter spacing */
        }

        .btn-gradient:hover {
            transform: translateY(-3px) scale(1.005); /* Reduced lift */
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
        }

        /* Specific Text Adjustments for HRIS Clarity */
        .limehills-heading {
            font-size: 2.1rem; /* Further reduced heading for logo section */
            letter-spacing: 0.02em;
            margin-bottom: 0.6rem;
            font-weight: 900;
            color: #1A202C;
        }
        .hris-system-text {
            font-size: 1rem; /* Further reduced HRIS system identifier */
            color: #718096;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                overflow-y: auto; /* Allow scrolling on smaller screens */
            }
            .login-card {
                width: 90%; /* Take up more width on small screens */
                max-width: 350px; /* Smaller max-width for mobile */
                padding: 2rem; /* Reduced padding for mobile */
                border-radius: 1.25rem;
                margin: 15px auto; /* Center card on mobile with less vertical margin */
            }
            .limehills-heading {
                font-size: 1.8rem;
                margin-bottom: 0.4rem;
            }
            .hris-system-text {
                font-size: 0.85rem;
            }
            .input-field {
                padding: 0.7rem 0.9rem;
                font-size: 0.85rem;
            }
            .btn-gradient {
                font-size: 0.95rem;
                padding: 0.75rem 1.2rem;
            }
        }

    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center relative">

    <div class="login-card p-10 w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <img src="{{ asset('logo.png') }}" alt="WRK Services PH Logo" class="mx-auto mb-4" style="height:80px;width:auto;object-fit:contain;filter:drop-shadow(0 4px 12px rgba(26,86,196,0.2));">
            <h2 style="font-size:1rem;font-weight:800;color:#1a56c4;letter-spacing:0.01em;margin-bottom:0.2rem;">WRK SERVICES PH HRIS</h2>
            <p class="hris-system-text text-gray-500">Reset Password</p>
        </div>

        @if (session('status'))
            <div class="bg-green-100 border border-green-300 text-green-700 text-sm px-5 py-3.5 rounded-xl mb-6 shadow-sm">
                <ul class="list-disc list-inside space-y-1.5">
                    <li>{{ session('status') }}</li>
                </ul>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 text-sm px-5 py-3.5 rounded-xl mb-6 shadow-sm">
                <ul class="list-disc list-inside space-y-1.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="form-label block mb-2">Email</label>
                <input type="email" name="email" id="email" required autocomplete="email"
                    class="input-field w-full"
                    placeholder="Enter your email address"
                    value="{{ old('email') }}">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-gradient w-full text-white font-bold shadow-lg mt-5">
                Send Password Reset Link
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                Back to Login
            </a>
        </div>
    </div>

</body>
</html>