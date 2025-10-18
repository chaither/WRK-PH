<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIS DTR - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>

/* Dark blue gradient */
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(90deg, #1E3A8A 0%, #1E40AF 100%); /* dark blue to slightly lighter dark blue */
    min-height: 100vh;
    display: flex;
    overflow: hidden;
    color: white;
}


        /* Login Card White */
        .login-card {
            background: white; 
            border-radius: 1.25rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            transition: all 0.3s;
            color: #1F2937; /* Dark text */
        }

        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        /* Inputs with black border */
        .input-field {
            background: white;          /* white input background */
            color: #111827;             /* dark text for readability */
            border: 1px solid #000;     /* solid black border */
            border-radius: 0.375rem;    /* same as rounded-md in Tailwind */
            padding: 0.75rem 1rem;      /* matches px-4 py-3 */
            font-size: 0.875rem;        /* text-sm */
            transition: all 0.2s;
        }

        .input-field::placeholder {
            color: #6B7280;             /* gray placeholder for contrast */
        }

        .input-field:focus {
            outline: none;
            border-color: #000;         /* keep border black on focus */
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1); /* subtle black focus ring */
            background: white;          /* keep background white */
        }

        /* Button */
        .btn-gradient {
            background: linear-gradient(90deg, #2563EB 0%, #9333EA 100%);
            transition: all 0.25s;
        }

        .btn-gradient:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        }

        /* Parallax elements */
        .parallax {
            position: absolute;
            will-change: transform;
            pointer-events: none;
            transition: transform 0.1s;
        }

        /* Icon hover */
        .icon-wrapper svg {
            transition: transform 0.3s, color 0.3s;
        }

        .icon-wrapper:hover svg {
            transform: scale(1.2) rotate(10deg);
            color: #fff;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex relative overflow-hidden">

    <!-- Parallax Circles -->
    <div class="parallax w-48 h-48 bg-purple-500 rounded-full opacity-20 top-10 left-10"></div>
    <div class="parallax w-32 h-32 bg-indigo-400 rounded-full opacity-15 bottom-20 right-20"></div>

    <!-- Left Section -->
    <div class="hidden md:flex flex-1 flex-col justify-center items-center relative p-12">
        <h1 class="text-5xl font-extrabold mb-4 text-center text-white">Welcome!</h1>
<p class="text-xl mb-12 text-center text-white opacity-90 max-w-lg">Your gateway to effortless time management</p>

        
        <div class="flex space-x-8">
            <div class="icon-wrapper bg-white bg-opacity-20 p-6 rounded-full cursor-pointer">
                <!-- Clock Icon -->
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12,6 12,12 16,14"></polyline>
                </svg>
            </div>
            <div class="icon-wrapper bg-white bg-opacity-20 p-6 rounded-full cursor-pointer">
                <!-- Calendar Icon -->
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="icon-wrapper bg-white bg-opacity-20 p-6 rounded-full cursor-pointer">
                <!-- Person Icon -->
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="flex-1 flex items-center justify-center p-8 relative">
        <div class="login-card p-10 w-full max-w-md relative z-10">
            <div class="text-center mb-8">
                <img src="{{ asset('limehills.png') }}" alt="Limehills Logo" class="mx-auto h-20 w-20 mb-4">
                <h2 class="text-3xl font-bold mb-2 text-gray-900">LIMEHILLS</h2>
                <p class="text-gray-700">Date and Time Record</p>
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

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-900 mb-1">Email</label>
                    <input type="email" name="email" id="email" required autocomplete="email"
                        class="input-field w-full px-4 py-3 rounded-md text-sm text-gray-900 placeholder-gray-500 focus:ring focus:ring-indigo-300"
                        placeholder="user@company.com"
                        value="{{ old('email') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-900 mb-1">Password</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                        class="input-field w-full px-4 py-3 rounded-md text-sm text-gray-900 placeholder-gray-500 focus:ring focus:ring-indigo-300"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-sm text-gray-700">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="h-4 w-4 text-indigo-600 rounded focus:ring focus:ring-indigo-200">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="hover:underline text-indigo-600">Forgot password?</a>
                </div>

                <button type="submit" class="btn-gradient w-full text-white py-3 rounded-lg font-bold shadow-lg mt-4">
                    Log In
                </button>
            </form>
        </div>
    </div>

    <!-- Parallax Script -->
    <script>
        const parallaxElements = document.querySelectorAll('.parallax');

        document.addEventListener('mousemove', (e) => {
            parallaxElements.forEach(el => {
                const speed = el.offsetWidth * 0.0005;
                const x = (window.innerWidth - e.pageX * speed);
                const y = (window.innerHeight - e.pageY * speed);
                el.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
    </script>

</body>
</html>
