<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-blue-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transition-all duration-300 ease-in-out z-50">
            <div class="flex items-center space-x-2 px-4 mb-8">
                <button id="sidebarToggle" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <span id="sidebarTitle" class="text-2xl font-bold whitespace-nowrap">HRIS SYSTEM</span>
            </div>
            
            <nav>
                <a href="{{ route('dashboard') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-home text-xl"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                <!-- Attendance Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('dtr.*') || request()->routeIs('attendance.change-shift.*') || request()->routeIs('attendance.change-restday.*') || request()->routeIs('attendance.no-bio-request.*') || request()->routeIs('attendance.overtime-request.*') || request()->routeIs('admin.attendance.*') ? 'true' : 'false' }} }" class="relative ">
                    <button @click="open = !open" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white w-full text-sm font-semibold justify-between {{ request()->routeIs('dtr.*') || request()->routeIs('attendance.change-shift.*') || request()->routeIs('attendance.change-restday.*') || request()->routeIs('admin.attendance.*') ? 'bg-blue-700' : '' }}"
                        :aria-expanded="open ? 'true' : 'false'">
                        <span class="inline-flex items-center">
                            <i class="fas fa-clock text-xl mr-3"></i>
                            <span class="ml-3">Attendance</span>
                        </span>
                        <svg id="attendanceDropdownArrow" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <template x-if="open">
                        <ul x-transition:enter="transition-all ease-in-out duration-300"
                            x-transition:enter-start="opacity-25 max-h-0"
                            x-transition:enter-end="opacity-100 max-h-xl"
                            x-transition:leave="transition-all ease-in-out duration-300"
                            x-transition:leave-start="opacity-100 max-h-xl"
                            x-transition:leave-end="opacity-0 max-h-0"
                            class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-white bg-blue-700 rounded-md shadow-inner"
                            aria-label="submenu">
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ Auth::user()->isEmployee() ? route('dtr.index') : route('dtr.admin') }}">
                                    Daily Time Record
                                </a>
                            </li>
                            @if (Auth::user()->hasRole(['admin', 'hr']))
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('admin.attendance.change-shift.review') }}">
                                    Shift Approval
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('admin.attendance.change-restday.review') }}">
                                    Restday Approval
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('admin.attendance.no-bio-request.review') }}">
                                    No Bio Request Approval
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('admin.attendance.overtime-request.review') }}">
                                    Overtime Approval
                                </a>
                            </li>
                            @else
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('attendance.change-shift.index') }}">
                                    Change Shift
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('attendance.change-restday.index') }}">
                                    Change Restday
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('attendance.no-bio-request.index') }}">
                                    No Bio Request
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-200">
                                <a class="w-full" href="{{ route('attendance.overtime-request.index') }}">
                                    Apply for Overtime
                                </a>
                            </li>
                            @endif
                        </ul>
                    </template>
                </div>
                <!-- End Attendance Dropdown -->

                @if(auth()->user()->role === 'admin')
                <a href="{{ route('department.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('department.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-building text-xl"></i>
                    <span class="ml-3">Department</span>
                </a>
                @endif
                
                @if (Auth::user()->hasRole(['admin', 'hr']))
                    <a href="{{ route('leave.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('leave.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-calendar-alt text-xl"></i>
                        <span class="ml-3">Leave Management</span>
                    </a>
                    <a href="{{ route('leave.review') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('leave.review') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-clipboard-list text-xl"></i>
                        <span class="ml-3">Leave Request Review</span>
                    </a>
                    <a href="{{ route('holidays.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('holidays.*') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-calendar-check text-xl"></i>
                        <span class="ml-3">Holiday Management</span>
                    </a>
                @endif
                @if (Auth::user()->isEmployee())
                    <a href="{{ route('employee.leave.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('employee.leave.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-briefcase text-xl"></i>
                        <span class="ml-3">My Leave Requests</span>
                    </a>
                    <a href="{{ route('employee.payslips.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('employee.payslips.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-money-check-alt text-xl"></i>
                        <span class="ml-3">My Payslips</span>
                    </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                <a href="{{ route('payroll.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('payroll.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-money-bill text-xl"></i>
                    <span class="ml-3">Payroll</span>
                </a>
                @endif
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4">
                
            </div>
        </div>

        <!-- Content -->
        <div id="content" class="flex-1 transition-all duration-300 ease-in-out h-screen overflow-y-auto">
            <!-- Top Nav -->
            <nav class="bg-blue-800 text-white p-4">
                <div class="flex items-center px-4">
                    
                    <div class="flex-1">

                    </div>
                    
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
            <main class="p-6 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>
    {{-- Render modals and stacked scripts pushed from views --}}
    @stack('modals')
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileDropdownToggle = document.getElementById('profileDropdownToggle');
            const profileDropdown = document.getElementById('profileDropdown');

            
            profileDropdownToggle.addEventListener('click', function() {
                profileDropdown.classList.toggle('hidden');
            });

            // Close the dropdown if the user clicks outside of it
            window.addEventListener('click', function(e) {
                if (!profileDropdownToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });

            const sidebar = document.getElementById('sidebar');
            const sidebarTitle = document.getElementById('sidebarTitle');
            const content = document.getElementById('content');
            const navSpans = document.querySelectorAll('#sidebar nav span');
            const mobileOverlay = document.getElementById('mobile-overlay');

            // Function to set sidebar state
            function setSidebarState() {
                // Temporarily disable transitions
                sidebar.style.transition = 'none';
                sidebarTitle.style.transition = 'none';
                content.style.transition = 'none';
                navSpans.forEach(span => span.style.transition = 'none');

                const sidebarOpen = localStorage.getItem('sidebarOpen');

                if (sidebarOpen === 'true' || sidebarOpen === null) {
                    // Sidebar is open/expanded
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64');
                    sidebarTitle.classList.remove('hidden');
                    content.classList.remove('ml-16');
                    content.classList.add('ml-64');
                    navSpans.forEach(span => span.classList.remove('hidden'));
                } else {
                    // Sidebar is closed/collapsed
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-16');
                    sidebarTitle.classList.add('hidden');
                    content.classList.remove('ml-64');
                    content.classList.add('ml-16');
                    navSpans.forEach(span => span.classList.add('hidden'));
                }

                // Re-enable transitions after a short delay
                setTimeout(() => {
                    sidebar.style.transition = ''; // Resets to CSS-defined transition
                    sidebarTitle.style.transition = ''; // Resets to CSS-defined transition
                    content.style.transition = ''; // Resets to CSS-defined transition
                    navSpans.forEach(span => span.style.transition = '');
                }, 50);
            }

            // Set initial state on load
            setSidebarState();

            // Adjust state on resize
            window.addEventListener('resize', setSidebarState);

            // Sidebar toggle
            sidebarToggle.addEventListener('click', function() {
                if (sidebar.classList.contains('w-16')) {
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64');
                    sidebarTitle.classList.remove('hidden');
                    content.classList.remove('ml-16');
                    content.classList.add('ml-64');
                    navSpans.forEach(span => span.classList.remove('hidden'));
                    localStorage.setItem('sidebarOpen', 'true');
                } else {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-16');
                    sidebarTitle.classList.add('hidden');
                    content.classList.remove('ml-64');
                    content.classList.add('ml-16');
                    navSpans.forEach(span => span.classList.add('hidden'));
                    localStorage.setItem('sidebarOpen', 'false');
                }
            });
        });
    </script>
</body>
</html>