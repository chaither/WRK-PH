<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMEHILLS DTR - Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>

        /* HRIS Theme: Deep Ocean Blue Gradient - Professional & Calming */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #051025 0%, #0E2242 100%);
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

        /* Parallax Elements: Barely-There Visual Texture */
        .parallax {
            position: absolute;
            will-change: transform;
            pointer-events: none;
            transition: transform 0.08s ease-out;
            opacity: 0.04; /* Even more subtle */
        }
        .parallax.bg-purple-500 { background-color: rgba(147, 51, 234, 0.025); }
        .parallax.bg-indigo-400 { background-color: rgba(79, 70, 229, 0.015); }

        /* Icon Wrapper: Minimalist & Clean */
        .icon-wrapper {
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1.05);
            background-color: rgba(255, 255, 255, 0.04); /* Even more subtle white background */
            border-radius: 50%;
            padding: 1.15rem; /* Further reduced icon padding */
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06); /* Lighter shadow */
        }
        .icon-wrapper:hover {
            transform: translateY(-3px) scale(1.02); /* Reduced lift */
            background-color: rgba(255, 255, 255, 0.06);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .icon-wrapper svg {
            transition: transform 0.3s ease, color 0.3s ease;
            color: #C3D0E8;
            width: 3rem; /* Further smaller icon size */
            height: 3rem; /* Further smaller icon size */
            stroke-width: 1.5; /* Slightly finer stroke */
        }
        .icon-wrapper:hover svg {
            color: #FFFFFF;
        }

        /* Specific Text Adjustments for HRIS Clarity */
        .main-title {
            font-size: 3.5rem; /* Further reduced main title size */
            line-height: 1.1; 
            letter-spacing: -0.06em;
            font-weight: 800;
        }
        .sub-title {
            font-size: 1.15rem; /* Further reduced subtitle size */
            opacity: 0.9;
            line-height: 1.35;
            margin-bottom: 1.8rem; /* Further reduced margin */
            max-width: 400px;
        }
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
        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2D3748;
            letter-spacing: 0.01em;
        }
        .remember-forgot-text {
            font-size: 0.85rem; /* Further reduced */
            color: #616E82;
        }
        .remember-forgot-text a {
            color: #4299E1;
            font-weight: 500;
        }
        /* Password visibility icon styling */
        .password-toggle-icon {
            color: #A0AEC0;
            transition: color 0.2s ease;
        }
        .password-toggle-icon:hover {
            color: #718096;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                overflow-y: auto; /* Allow scrolling on smaller screens */
            }
            .parallax {
                display: none; /* Hide parallax on mobile */
            }
            .left-section {
                display: none; /* Hide left section on mobile */
            }
            .login-card {
                width: 90%; /* Take up more width on small screens */
                max-width: 350px; /* Smaller max-width for mobile */
                padding: 2rem; /* Reduced padding for mobile */
                border-radius: 1.25rem;
                margin: 15px auto; /* Center card on mobile with less vertical margin */
            }
            .main-title {
                font-size: 2.2rem; /* Even smaller title on mobile */
                margin-bottom: 0.8rem;
            }
            .sub-title {
                font-size: 0.9rem;
                margin-bottom: 1.2rem;
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
            .icon-wrapper {
                padding: 0.8rem;
            }
            .icon-wrapper svg {
                width: 2rem;
                height: 2rem;
            }
            .flex-1.flex.items-center.justify-center.p-12.relative {
                padding: 0.5rem; /* Adjust container padding for mobile */
            }
        }

    </style>
</head>
<body class="antialiased min-h-screen flex relative overflow-hidden">

    <!-- Parallax Circles - More & Larger, Extremely Subtle -->
    <div class="parallax w-72 h-72 rounded-full top-12 left-1/4 transform -translate-x-1/2 bg-purple-500 hidden md:block" data-speed="0.00025"></div>
    <div class="parallax w-56 h-56 rounded-full bottom-24 right-1/3 transform translate-x-1/2 bg-indigo-400 hidden md:block" data-speed="0.00015"></div>
    <div class="parallax w-96 h-96 rounded-full top-1/2 left-3/4 transform -translate-x-1/2 -translate-y-1/2 bg-purple-500 hidden md:block" data-speed="0.00035"></div>
    <div class="parallax w-40 h-40 rounded-full top-1/3 right-1/4 transform translate-x-1/2 -translate-y-1/2 bg-indigo-400 hidden md:block" data-speed="0.0002"></div>

    <!-- Left Section -->
    <div class="hidden md:flex flex-1 flex-col justify-center items-center relative px-20 py-32 left-section">
        <h1 class="main-title font-extrabold mb-6 text-center text-white">Human Resource <br> Information System</h1>
        <p class="sub-title mb-24 text-center text-white max-w-xl">Your gateway to effortless time management and employee empowerment with a modern, intuitive interface.</p>

        
        <div class="flex space-x-14 mt-10">
            <div class="icon-wrapper">
                <!-- Clock Icon -->
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12,6 12,12 16,14"></polyline>
                </svg>
            </div>
            <div class="icon-wrapper">
                <!-- Calendar Icon -->
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="icon-wrapper">
                <!-- Person Icon -->
                <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="flex-1 flex items-center justify-center p-12 relative">
        <div class="login-card p-10 w-full max-w-md relative z-10">
            <div class="text-center mb-8">
                <img src="{{ asset('limehills.png') }}" alt="Limehills Logo" class="mx-auto h-24 w-24 mb-4">
                <h2 class="limehills-heading font-extrabold mb-1 text-gray-900">LIMEHILLS HRIS</h2>
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

            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

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

                <div>
                    <label for="password" class="form-label block mb-2">New Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required autocomplete="new-password"
                            class="input-field w-full pr-12"
                            placeholder="••••••••">
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer password-toggle-icon"
                            onclick="togglePasswordVisibility('password')">
                            <i id="togglePasswordIcon" class="far fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label block mb-2">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                            class="input-field w-full pr-12"
                            placeholder="••••••••">
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer password-toggle-icon"
                            onclick="togglePasswordVisibility('password_confirmation')">
                            <i id="togglePasswordConfirmationIcon" class="far fa-eye"></i>
                        </span>
                    </div>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-gradient w-full text-white font-bold shadow-lg mt-5">
                    Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <!-- Parallax Script -->
    <script>
        const parallaxElements = document.querySelectorAll('.parallax');

        document.addEventListener('mousemove', (e) => {
            parallaxElements.forEach(el => {
                const speed = parseFloat(el.getAttribute('data-speed')) || 0.00025; // Use data-speed or a subtle default
                const x = (window.innerWidth - e.pageX * speed);
                const y = (window.innerHeight - e.pageY * speed);
                el.style.transform = `translate(${x}px, ${y}px)`;
            });
        });

        // Set initial random positions for a more dynamic start
        parallaxElements.forEach(el => {
            const randomX = Math.random() * 30 - 15; // -15 to 15px
            const randomY = Math.random() * 30 - 15;
            el.style.transform = `translate(${randomX}px, ${randomY}px)`;
            el.setAttribute('data-speed', (Math.random() * 0.0003 + 0.0001).toFixed(5)); // Even finer random speed
        });


        function togglePasswordVisibility(fieldId) {
            const passwordField = document.getElementById(fieldId);
            let toggleIcon;
            if (fieldId === 'password') {
                toggleIcon = document.getElementById('togglePasswordIcon');
            } else if (fieldId === 'password_confirmation') {
                toggleIcon = document.getElementById('togglePasswordConfirmationIcon');
            }

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>