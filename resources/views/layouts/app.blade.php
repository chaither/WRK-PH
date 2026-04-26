<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRK Services PH HRIS - @yield('title')</title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <link rel="preload" as="image" href="{{ asset('logo.png') }}">
            <script type="module">
                import hotwiredTurbo from 'https://cdn.skypack.dev/@hotwired/turbo';
            </script>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            <style>
                /* Hide dropdown text immediately when sidebar is collapsed */
                #sidebar.sidebar-collapsed .sidebar-dropdown-text {
                    display: none !important;
                }

                /* Initial load: Force collapsed state without transition to prevent FOUC */
                html.has-initial-sidebar-collapsed #sidebar {
                    width: 4rem !important; /* Force w-16 */
                    transform: translateX(0%) !important; /* Ensure it's not off-screen */
                    transition: none !important;
                }
                html.has-initial-sidebar-collapsed #content {
                    margin-left: 4rem !important; /* Force md:ml-16 */
                    transition: none !important;
                }
                html.has-initial-sidebar-collapsed #sidebar .sidebar-text,
                html.has-initial-sidebar-collapsed #sidebar .sidebar-dropdown-text {
                    display: none !important;
                }

                #sidebar.sidebar-collapsed .sidebar-dropdown-text {
                    display: none !important;
                }
            </style>
    <script>
        // Check localStorage immediately to apply initial sidebar state
        const sidebarOpenState = localStorage.getItem('sidebarOpen');
        if (sidebarOpenState === 'false' && window.innerWidth >= 768) {
            document.documentElement.classList.add('has-initial-sidebar-collapsed');
        }
    </script>
</head>
<body class="text-white font-sans antialiased selection:bg-blue-500 selection:text-white" style="background-color:#051534;">
    <!-- Fixed Header -->
    <!-- Fixed Header -->
    <header id="permanent-header" data-turbo-permanent class="fixed top-0 left-0 w-full h-16 text-white flex items-center justify-between px-4 shadow-lg border-b" 
            style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 64px !important; z-index: 100 !important; background: linear-gradient(90deg, #051534 0%, #0b2059 100%) !important; border-color: rgba(59,130,246,0.18) !important;">
        <!-- Left: Toggle + Logo -->
        <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="text-white focus:outline-none hover:bg-white/10 p-2 rounded-lg transition-colors">
                 <i class="fas fa-bars text-xl align-middle"></i>
            </button>
            <a href="{{ route('dashboard') }}" id="sidebarTitle" class="flex items-center gap-2 cursor-pointer group" style="text-decoration:none;">
                <div style="background:white; border-radius:8px; padding:3px 6px; display:flex; align-items:center; box-shadow:0 2px 8px rgba(59,130,246,0.25);">
                    <img src="{{ asset('logo.png') }}" alt="WRK Services PH HRIS" decoding="sync" class="object-contain" style="height:2rem; width:auto;">
                </div>
                <div class="sidebar-text" style="line-height:1.1;">
                    <div style="font-size:0.78rem; font-weight:800; color:#ffffff; letter-spacing:0.06em; text-transform:uppercase;">WRK SERVICES PH</div>
                    <div style="font-size:0.6rem; font-weight:500; color:rgba(147,197,253,0.85); letter-spacing:0.08em; text-transform:uppercase;">HRIS</div>
                </div>
            </a>
        </div>

        <!-- Right: Notifications & Profile -->
        <div class="flex items-center gap-4">
             <!-- Notification Dropdown -->
             <div class="relative">
                <button id="notificationDropdownToggle" class="relative rounded-full p-2 focus:outline-none hover:bg-blue-800 transition-colors">
                    <i class="fas fa-bell text-xl text-white"></i>
                    <span id="notificationBadge" class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>
                </button>
                <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-xl py-1 z-[999] hidden max-h-96 overflow-hidden flex flex-col text-gray-800">
                     <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                        <button id="markAllReadBtn" class="text-xs text-blue-600 hover:text-blue-800 hidden">Mark all as read</button>
                    </div>
                    <div id="notificationList" class="overflow-y-auto max-h-80">
                        <div class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Loading notifications...</p>
                        </div>
                    </div>
                    <div id="notificationEmpty" class="px-4 py-8 text-center text-gray-500 hidden">
                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                        <p>No notifications</p>
                    </div>
                    <div class="px-4 py-2 border-t border-gray-200 text-center">
                        @if(Auth::check() && Auth::user()->role === 'employee')
                            <a href="{{ route('employee.notifications.history') }}" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
                        @else
                            <a href="{{ route('notifications.history') }}" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
                        @endif
                    </div>
                </div>
             </div>
             
             <!-- Profile Dropdown -->
             <div class="relative">
                <button id="profileDropdownToggle" class="rounded-full p-2 focus:outline-none hover:bg-white/10 transition-colors">
                    <i class="fas fa-user-circle text-3xl text-white"></i>
                </button>
                <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[999] hidden text-left">
                    <a href="{{ route('password.change') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-key mr-2"></i>Change Password</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button>
                    </form>
                </div>
             </div>
        </div>
    </header>

    <div class="flex min-h-screen" style="padding-top: 4rem !important;">
        <!-- Sidebar -->
        <div id="sidebar" class="text-white w-64 space-y-6 py-4 px-2 fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out z-50 border-r h-[calc(100vh-4rem)] overflow-y-auto custom-scrollbar" style="top: 4rem !important; background: linear-gradient(180deg, #051534 0%, #0a1e50 100%) !important; border-color: rgba(59,130,246,0.15) !important; box-shadow: 4px 0 24px rgba(0,0,0,0.35) !important;">
            
            <nav class="mt-2">
                <a href="{{ route('dashboard') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-home text-xl"></i>
                    <span class="ml-3 sidebar-text">Dashboard</span>
                </a>

                <!-- Attendance Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('dtr.*') || request()->routeIs('attendance.change-shift.*') || request()->routeIs('attendance.change-restday.*') || request()->routeIs('attendance.no-bio-request.*') || request()->routeIs('attendance.overtime-request.*') || request()->routeIs('admin.attendance.*') ? 'true' : 'false' }} }" class="relative ">
                    <button @click="open = !open" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white w-full text-sm font-semibold justify-between {{ request()->routeIs('dtr.*') || request()->routeIs('attendance.change-shift.*') || request()->routeIs('attendance.change-restday.*') || request()->routeIs('admin.attendance.*') ? 'bg-blue-700' : '' }}"
                        :aria-expanded="open ? 'true' : 'false'">
                        <span class="flex items-center">
                            <i class="fas fa-clock text-xl mr-3"></i>
                            <span class="sidebar-text">Attendance</span>
                        </span>
                        <svg id="attendanceDropdownArrow" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="currentColor" viewBox="0 0 20 20">
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
                            class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-white rounded-md shadow-inner bg-blue-800 border border-blue-500"
                            aria-label="submenu">
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dtr.index') || request()->routeIs('dtr.admin') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ Auth::user()->isEmployee() ? route('dtr.index') : route('dtr.admin') }}">
                                    <i class="fas fa-calendar-day mr-2"></i><span class="sidebar-dropdown-text">Daily Time Record</span>
                                </a>
                            </li>
                            @if (Auth::user()->isHRManager())
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.change-shift.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.change-shift.review') }}">
                                    <i class="fas fa-file-invoice mr-2"></i><span class="sidebar-dropdown-text">Shift Approval</span>
                                </a>
                            </li>
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.change-restday.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.change-restday.review') }}">
                                    <i class="fas fa-house-chimney mr-2"></i><span class="sidebar-dropdown-text">Restday Approval</span>
                                </a>
                            </li>
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.no-bio-request.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.no-bio-request.review') }}">
                                    <i class="fas fa-fingerprint mr-2"></i><span class="sidebar-dropdown-text">No Bio Request Approval</span>
                                </a>
                            </li>
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.overtime-request.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.overtime-request.review') }}">
                                    <i class="fas fa-business-time mr-2"></i><span class="sidebar-dropdown-text">Overtime Approval</span>
                                </a>
                            </li>
                            @else
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.change-shift.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.change-shift.index') }}">
                                    <i class="fas fa-file-invoice mr-2"></i><span class="sidebar-dropdown-text">Change Shift</span>
                                </a>
                            </li>
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.change-restday.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.change-restday.index') }}">
                                    <i class="fas fa-house-chimney mr-2"></i><span class="sidebar-dropdown-text">Change Restday</span>
                                </a>
                            </li>
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.no-bio-request.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.no-bio-request.index') }}">
                                    <i class="fas fa-fingerprint mr-2"></i><span class="sidebar-dropdown-text">No Bio Request</span>
                                </a>
                            </li>
                            <li class="pl-6 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.overtime-request.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.overtime-request.index') }}">
                                    <i class="fas fa-business-time mr-2"></i><span class="sidebar-dropdown-text">Apply for Overtime</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </template>
                </div>
                <!-- End Attendance Dropdown -->

                @if (Auth::user()->isHRManager())
                <a href="{{ route('department.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('department.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-building text-xl"></i>
                    <span class="ml-3 sidebar-text">Department</span>
                </a>
                <a href="{{ route('dtr.face-recognition.management') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dtr.face-recognition.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-user-shield text-xl"></i>
                    <span class="ml-3 sidebar-text">Face Recognition</span>
                </a>
                @endif
                
                @if (Auth::user()->hasRole(['admin', 'hr']))
                    <a href="{{ route('leave.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('leave.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-calendar-alt text-xl"></i>
                        <span class="ml-3 sidebar-text">Leave Management</span>
                    </a>
                    <a href="{{ route('leave.review') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('leave.review') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-clipboard-list text-xl"></i>
                        <span class="ml-3 sidebar-text">Leave Request Review</span>
                    </a>
                    <a href="{{ route('holidays.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('holidays.*') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-calendar-check text-xl"></i>
                        <span class="ml-3 sidebar-text">Holiday Management</span>
                    </a>
                    <a href="{{ route('shifts.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('shifts.*') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-clock text-xl"></i>
                        <span class="ml-3 sidebar-text">Shift Management</span>
                    </a>
                @endif
                @if (Auth::user()->isEmployee())
                    <a href="{{ route('employees.profile', Auth::user()->id) }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('employees.profile') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-user text-xl"></i>
                        <span class="ml-3 sidebar-text">My Profile</span>
                    </a>
                    <a href="{{ route('employee.leave.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('employee.leave.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-briefcase text-xl"></i>
                        <span class="ml-3 sidebar-text">My Leave Requests</span>
                    </a>
                    <a href="{{ route('employee.payslips.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white {{ request()->routeIs('employee.payslips.index') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-money-check-alt text-xl"></i>
                        <span class="ml-3 sidebar-text">My Payslips</span>
                    </a>
                @endif

                @if (Auth::user()->isHRManager())
                <a href="{{ route('payroll.index') }}" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white {{ request()->routeIs('payroll.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-money-bill text-xl"></i>
                    <span class="ml-3 sidebar-text">Payroll</span>
                </a>
                @endif
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4">
                
            </div>
        </div>

        <!-- Content -->
        <!-- Content -->
        <div id="content" class="flex-1 transition-all duration-300 ease-in-out md:ml-64 p-6" style="background: linear-gradient(160deg, #051534 0%, #0a1e4f 50%, #071640 100%); min-height: 100vh;">
             @yield('content')
        </div>
        </div>
    </div>
    {{-- Mobile overlay used when sidebar is open on small screens --}}
    <div id="mobile-overlay" class="fixed inset-0 bg-transparent z-40 hidden"></div>

    {{-- Render modals and stacked scripts pushed from views --}}
    @stack('modals')
    @stack('scripts')
    <script>
        // Use a single function for app initialization that runs on Turbo loads
        const initApp = () => {
             // Elements - Re-query them as Turbo replaces the body content
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content'); // Re-query content
            const navSpans = document.querySelectorAll('#sidebar .sidebar-text');
            const dropdownSpans = document.querySelectorAll('#sidebar .sidebar-dropdown-text');
            const mobileOverlay = document.getElementById('mobile-overlay');
            // Note: Header elements (toggle, profile, notifications) might be permanent, 
            // but we use delegation to handle their events safely across reloads.

            const notificationList = document.getElementById('notificationList');
            const notificationBadge = document.getElementById('notificationBadge');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const notificationEmpty = document.getElementById('notificationEmpty');

            // --- Sidebar Logic ---
            
            function setSidebarState() {
                // Remove the initial FOUC class and re-enable transitions once JS takes over
                document.documentElement.classList.remove('has-initial-sidebar-collapsed');
                if(!sidebar) return; // Guard clause

                sidebar.style.transition = 'all 0.3s ease-in-out';
                if(content) content.style.transition = 'all 0.3s ease-in-out';

                const sidebarOpenInStorage = localStorage.getItem('sidebarOpen');
                const sidebarShouldBeOpen = sidebarOpenInStorage === null ? true : sidebarOpenInStorage === 'true';

                if (window.innerWidth >= 768) {
                    // Desktop
                    if(mobileOverlay) mobileOverlay.classList.add('hidden');
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');

                    if (!sidebarShouldBeOpen) {
                        // compact
                        sidebar.classList.add('w-16', 'sidebar-collapsed');
                        sidebar.classList.remove('w-64');
                        navSpans.forEach(span => span.classList.add('hidden'));
                        dropdownSpans.forEach(span => span.classList.add('hidden'));
                        if(content) {
                            content.classList.remove('md:ml-64');
                            content.classList.add('md:ml-16');
                        }
                    } else {
                        // expanded
                        sidebar.classList.remove('w-16', 'sidebar-collapsed');
                        sidebar.classList.add('w-64');
                        navSpans.forEach(span => span.classList.remove('hidden'));
                        dropdownSpans.forEach(span => span.classList.remove('hidden'));
                        if(content) {
                            content.classList.remove('md:ml-16');
                            content.classList.add('md:ml-64');
                        }
                    }
                } else {
                    // Mobile
                    if(content) content.classList.remove('md:ml-64', 'md:ml-16');
                    sidebar.classList.remove('w-16', 'sidebar-collapsed');
                    sidebar.classList.add('w-64');
                    navSpans.forEach(span => span.classList.remove('hidden'));
                    dropdownSpans.forEach(span => span.classList.remove('hidden'));

                    if (sidebarShouldBeOpen) {
                         // open logic
                         sidebar.classList.remove('-translate-x-full');
                         if(mobileOverlay) mobileOverlay.classList.remove('hidden');
                         sidebar.style.zIndex = 60;
                         document.body.classList.add('overflow-hidden');
                    } else {
                        // close logic
                         sidebar.classList.add('-translate-x-full');
                         if(mobileOverlay) mobileOverlay.classList.add('hidden');
                         document.body.classList.remove('overflow-hidden');
                    }
                }
            }

            // --- Notifications Logic ---
            function loadNotifications() {
                if(!notificationList) return; 

                fetch('{{ route("notifications.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    notificationList.innerHTML = '';
                    if(notificationEmpty) notificationEmpty.classList.add('hidden');
                    
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            const notificationItem = document.createElement('a'); 
                            notificationItem.href = notification.link || '#';
                            notificationItem.className = `block px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer ${!notification.read_at ? 'bg-blue-50' : ''}`;
                            notificationItem.innerHTML = `
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 ${!notification.read_at ? 'font-semibold' : ''}">${notification.message}</p>
                                        <p class="text-xs text-gray-500 mt-1">${notification.time_ago}</p>
                                    </div>
                                    ${!notification.read_at ? '<span class="ml-2 w-2 h-2 bg-blue-500 rounded-full"></span>' : ''}
                                </div>
                            `;
                            notificationItem.addEventListener('click', function(e) {
                                // If inside Turbo, allow Turbo to handle navigation unless it's external
                                if (notification.link) {
                                     // Turbo handles links automatically. 
                                     // We just mark as read.
                                }
                                markAsRead(notification.id);
                            });
                            notificationList.appendChild(notificationItem);
                        });
                        
                        if (data.unread_count > 0 && notificationBadge) {
                            notificationBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                            notificationBadge.classList.remove('hidden');
                            if(markAllReadBtn) markAllReadBtn.classList.remove('hidden');
                        } else {
                            if(notificationBadge) notificationBadge.classList.add('hidden');
                            if(markAllReadBtn) markAllReadBtn.classList.add('hidden');
                        }
                    } else {
                        if(notificationEmpty) notificationEmpty.classList.remove('hidden');
                        if(notificationBadge) notificationBadge.classList.add('hidden');
                        if(markAllReadBtn) markAllReadBtn.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
            }

            function markAsRead(notificationId) {
                if (notificationId.toString().startsWith('admin-')) return;
                fetch(`{{ url('notifications') }}/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(()=> loadNotifications());
            }

            // Initialize Sidebar State
            setSidebarState();

            // Initialize Notifications (load once per nav)
            loadNotifications();

            // Refresh notifications every 30s
            // Clear existing interval if any to prevent duplicates
            if (window.notificationInterval) clearInterval(window.notificationInterval);
            window.notificationInterval = setInterval(loadNotifications, 30000);
        };
        
        // --- Event Delegation (Run once) ---
        // We use delegation because elements might be replaced by Turbo, or stay permanent.
        // Delegation ensures we don't lose listeners or double-bind.
        if (!window.eventsDelegated) {
            window.eventsDelegated = true;

            // Sidebar Toggle
            document.addEventListener('click', function(e) {
                const toggle = e.target.closest('#sidebarToggle');
                if (toggle) {
                    const sidebar = document.getElementById('sidebar');
                    const content = document.getElementById('content');
                    const navSpans = document.querySelectorAll('#sidebar .sidebar-text');
                    const dropdownSpans = document.querySelectorAll('#sidebar .sidebar-dropdown-text');

                    if (window.innerWidth >= 768) {
                        // Desktop toggle
                        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                        content.classList.remove('md:ml-16', 'md:ml-64');
                        if (isCollapsed) {
                            sidebar.classList.remove('w-16', 'sidebar-collapsed');
                            sidebar.classList.add('w-64');
                            navSpans.forEach(span => span.classList.remove('hidden'));
                            dropdownSpans.forEach(span => span.classList.remove('hidden'));
                            localStorage.setItem('sidebarOpen', 'true');
                            content.classList.add('md:ml-64');
                        } else {
                            sidebar.classList.remove('w-64');
                            sidebar.classList.add('w-16', 'sidebar-collapsed');
                            navSpans.forEach(span => span.classList.add('hidden'));
                            dropdownSpans.forEach(span => span.classList.add('hidden'));
                            localStorage.setItem('sidebarOpen', 'false');
                            content.classList.add('md:ml-16');
                        }
                    } else {
                        // Mobile toggle
                        const mobileOverlay = document.getElementById('mobile-overlay');
                        if (sidebar.classList.contains('-translate-x-full')) {
                            // Open
                            sidebar.classList.remove('-translate-x-full');
                            mobileOverlay.classList.remove('hidden');
                            sidebar.style.zIndex = 60;
                            localStorage.setItem('sidebarOpen', 'true');
                            document.body.classList.add('overflow-hidden');
                        } else {
                            // Close
                            sidebar.classList.add('-translate-x-full');
                            mobileOverlay.classList.add('hidden');
                            localStorage.setItem('sidebarOpen', 'false');
                            document.body.classList.remove('overflow-hidden');
                        }
                    }
                    return;
                }

                // Profile Dropdown Toggle
                const profileToggle = e.target.closest('#profileDropdownToggle');
                if (profileToggle) {
                    const profileDropdown = document.getElementById('profileDropdown');
                    const notificationDropdown = document.getElementById('notificationDropdown');
                    profileDropdown.classList.toggle('hidden');
                    if(notificationDropdown) notificationDropdown.classList.add('hidden');
                    e.stopPropagation(); // prevent window click
                    return;
                }

                // Notification Dropdown Toggle
                const notifToggle = e.target.closest('#notificationDropdownToggle');
                if (notifToggle) {
                    const profileDropdown = document.getElementById('profileDropdown');
                    const notificationDropdown = document.getElementById('notificationDropdown');
                    notificationDropdown.classList.toggle('hidden');
                    if(profileDropdown) profileDropdown.classList.add('hidden');
                    // We don't need to call loadNotifications here as it's auto-loaded or loaded by interval. 
                    // But if we want instant load:
                    // Only if we expose loadNotifications globally or move logic here. 
                    // Let's leave it to the interval for now or reload if easy.
                    e.stopPropagation();
                    return;
                }

                // Mark All Read Btn
                const markAll = e.target.closest('#markAllReadBtn');
                if(markAll) {
                     e.stopPropagation();
                     fetch('{{ route("notifications.markAllRead") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }).then(() => {
                        // Trigger a reload of notifications manually?
                        const list = document.getElementById('notificationList');
                        if(list) list.innerHTML = '<div class="px-4 py-8 text-center text-gray-500"><p>Reloading...</p></div>';
                        // Wait for interval or force reload if we made the function global.
                        // Since initApp is local, we can't call it easily. 
                        // It's fine, the interval will pick it up or the user can refresh.
                        // Or we can dispatch a custom event.
                    });
                }
            });

            // Close dropdowns on outside click
            window.addEventListener('click', function(e) {
                const profileDropdown = document.getElementById('profileDropdown');
                const notificationDropdown = document.getElementById('notificationDropdown');
                const profileToggle = document.getElementById('profileDropdownToggle');
                const notifToggle = document.getElementById('notificationDropdownToggle');

                if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                    if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                }
                if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
                     if (!notifToggle.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.classList.add('hidden');
                    }
                }
                
                // Close mobile overlay/sidebar if clicked outside (on overlay)
                const mobileOverlay = document.getElementById('mobile-overlay');
                const sidebar = document.getElementById('sidebar');
                if (mobileOverlay && !mobileOverlay.classList.contains('hidden') && e.target === mobileOverlay) {
                     sidebar.classList.add('-translate-x-full');
                     mobileOverlay.classList.add('hidden');
                     localStorage.setItem('sidebarOpen', 'false');
                     document.body.classList.remove('overflow-hidden');
                }
            });

            // Sidebar Links - close overlay on mobile
            document.addEventListener('click', function(e) {
                 const link = e.target.closest('#sidebar nav a');
                 if(link && window.innerWidth < 768) {
                      const mobileOverlay = document.getElementById('mobile-overlay');
                      const sidebar = document.getElementById('sidebar');
                      setTimeout(() => {
                         sidebar.classList.add('-translate-x-full');
                         if(mobileOverlay) mobileOverlay.classList.add('hidden');
                         document.body.classList.remove('overflow-hidden');
                      }, 80);
                 }
            });
        }

        // Initialize on DOMContentLoaded (fallback) and turbo:load
        document.addEventListener('DOMContentLoaded', initApp);
        document.addEventListener('turbo:load', initApp);
    </script>
</body>
</html>