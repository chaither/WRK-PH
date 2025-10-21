<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            @php $viteManifest = public_path('build/manifest.json'); @endphp
            @if (file_exists($viteManifest))
                @vite(['resources/css/app.css', 'resources/js/app.js'])
            @else
                <!-- Vite manifest not found. Assets not built. -->
                <!-- Build assets with `npm run build` or `npm run dev` to enable app.js. -->
            @endif
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-30">
            <div class="flex items-center space-x-2 px-4 mb-8">
                <span class="text-2xl font-bold">HRIS SYSTEM</span>
            </div>
            
            <nav>
                <a href="{{ route('dashboard') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-home w-6"></i>
                    Dashboard
                </a>

                @if(auth()->user()->isEmployee())
                <a href="{{ route('dtr.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dtr.index') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-clock w-6"></i>
                    Daily Time Record
                </a>
                @endif

                @if(auth()->user()->role === 'admin')
                <a href="{{ route('employees.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('employees.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-users w-6"></i>
                    Employees
                </a>

                <a href="{{ route('dtr.admin') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dtr.admin') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-user-clock w-6"></i>
                    DTR Management
                </a>
                @endif
                @if (Auth::user()->hasRole(['admin', 'hr']))
                    <a href="{{ route('leave.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('leave.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-calendar-alt w-6"></i>
                        Leave Management
                    </a>
                    <a href="{{ route('leave.review') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('leave.review') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-clipboard-list w-6"></i>
                        Leave Request Review
                    </a>
                @endif
                @if (Auth::user()->isEmployee())
                    <a href="{{ route('employee.leave.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('employee.leave.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-briefcase w-6"></i>
                        My Leave Requests
                    </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                <a href="{{ route('payroll.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('payroll.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-money-bill w-6"></i>
                    Payroll
                </a>
                @endif
            </nav>

            <div class="absolute bottom-0 left-0 right-0 p-4">
                
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 md:ml-64">
            <!-- Top Nav -->
            <nav class="bg-blue-800 text-white p-4">
                <div class="container flex items-center px-4">
                    <button id="hamburger" class="text-white focus:outline-none md:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <div class="relative ml-auto">
                        <button id="profileDropdownToggle" class="rounded-full p-3 focus:outline-none">
                            <i class="fas fa-user-circle text-3xl text-white"></i>
                        </button>
                        <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 hidden">
                            <a href="{{ route('password.request') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-key mr-2"></i>Change Password</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
    {{-- Render modals and stacked scripts pushed from views --}}
    @stack('modals')
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const sidebar = document.querySelector('div.bg-blue-800');
            const profileDropdownToggle = document.getElementById('profileDropdownToggle');
            const profileDropdown = document.getElementById('profileDropdown');
            const mainContent = document.querySelector('.flex-1'); // Select the main content area

            hamburger.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                mainContent.classList.toggle('md:ml-64'); // Toggle margin for main content
            });

            profileDropdownToggle.addEventListener('click', function() {
                profileDropdown.classList.toggle('hidden');
            });

            // Close the dropdown if the user clicks outside of it
            window.addEventListener('click', function(e) {
                if (!profileDropdownToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>