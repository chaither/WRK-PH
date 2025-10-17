<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* NEW: Simple and Attractive Light Blue-Gray Background */
        body {
            /* A very soft, subtle gradient for depth */
            background: linear-gradient(135deg,rgb(51, 91, 131) 0%,rgb(42, 59, 80) 100%); 
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }

        /* Card Gradient and Shadow (Kept the original light card gradient to contrast with the new background) */
        .login-card {
            background: linear-gradient(180deg, #FFFFFF 0%, #F9FAFB 100%); /* Slightly brighter white card */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); /* Reduced shadow opacity for a lighter feel */
            transition: all 0.3s ease-in-out;
        }
        
        /* Card Hover Effect: Lifts slightly on mouse-over */
        .login-card:hover {
            transform: translateY(-4px); 
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.3); 
        }

        /* Input Focus and Hover Effects */
        .input-field {
            transition: all 0.3s ease-in-out;
            border-color: #d1d5db;
        }
        
        /* Enhanced Input Focus: Clearer and using the primary blue color */
        .input-field:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.3);
        }

        /* Button Gradient (Blue/Purple) */
        .btn-gradient {
            background: linear-gradient(90deg, #4F46E5 0%, #A78BFA 100%); 
            transition: all 0.2s ease-in-out;
        }
        
        /* Button Hover Effect: Maintains shadow, scales slightly */
        .btn-gradient:hover {
            opacity: 1;
            transform: scale(1.01);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
        }
        .btn-gradient:active {
            opacity: 0.9;
            transform: scale(0.99);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        /* Link Hover Effect: Clear color change */
        .forgot-link:hover {
            color: #4F46E5; 
            text-decoration: underline;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="login-card p-8 rounded-2xl w-full max-w-xs sm:max-w-sm">
            
            <div class="text-center mb-8 space-y-2">
                
                <img src="{{ asset('limehills.png') }}" alt="Limehills Logo" class="h-16 w-16 mx-auto mb-2 filter drop-shadow-md">
                
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">HRIS SYSTEM</h1>
                <p class="text-sm text-gray-500">Human Resources Information System</p>
            </div>
            
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 text-sm px-4 py-3 rounded-lg mb-6 shadow-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" required autocomplete="email"
                        class="input-field w-full px-4 py-2 border rounded-md text-sm placeholder-gray-400 focus:ring-opacity-50"
                        placeholder="e.g., user@company.com"
                        value="{{ old('email') }}">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                        class="input-field w-full px-4 py-2 border rounded-md text-sm placeholder-gray-400 focus:ring-opacity-50"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-600">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 forgot-link">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit" class="btn-gradient w-full text-white py-2.5 rounded-lg font-bold text-base shadow-lg mt-6">
                    Login
                </button>
            </form>
            
        </div>
    </div>
</body>
</html>