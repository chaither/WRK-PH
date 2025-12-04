<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System - @yield('title')</title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
            <meta name="csrf-token" content="{{ csrf_token() }}">
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
            </style>
    <script>
        // Check localStorage immediately to apply initial sidebar state
        const sidebarOpenState = localStorage.getItem('sidebarOpen');
        if (sidebarOpenState === 'false' && window.innerWidth >= 768) {
            document.documentElement.classList.add('has-initial-sidebar-collapsed');
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-[#0B1432] text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out z-50">
            <div class="flex items-center space-x-2 px-4 mb-8">
                <button id="sidebarToggle" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-xl align-middle"></i>
                </button>
                <span id="sidebarTitle" class="text-2xl font-bold whitespace-nowrap cursor-pointer" onclick="event.stopPropagation(); window.location.href='{{ route('dashboard') }}'">LIMEHILLS HRIS</span>
            </div>
            
            <nav>
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
                            class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-white rounded-md shadow-inner"
                            aria-label="submenu">
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('dtr.index') || request()->routeIs('dtr.admin') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ Auth::user()->isEmployee() ? route('dtr.index') : route('dtr.admin') }}">
                                    <i class="fas fa-calendar-day mr-2"></i><span class="sidebar-dropdown-text">Daily Time Record</span>
                                </a>
                            </li>
                            @if (Auth::user()->isHRManager())
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.change-shift.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.change-shift.review') }}">
                                    <i class="fas fa-file-invoice mr-2"></i><span class="sidebar-dropdown-text">Shift Approval</span>
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.change-restday.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.change-restday.review') }}">
                                    <i class="fas fa-house-chimney mr-2"></i><span class="sidebar-dropdown-text">Restday Approval</span>
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.no-bio-request.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.no-bio-request.review') }}">
                                    <i class="fas fa-fingerprint mr-2"></i><span class="sidebar-dropdown-text">No Bio Request Approval</span>
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('admin.attendance.overtime-request.review') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('admin.attendance.overtime-request.review') }}">
                                    <i class="fas fa-business-time mr-2"></i><span class="sidebar-dropdown-text">Overtime Approval</span>
                                </a>
                            </li>
                            @else
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.change-shift.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.change-shift.index') }}">
                                    <i class="fas fa-file-invoice mr-2"></i><span class="sidebar-dropdown-text">Change Shift</span>
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.change-restday.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.change-restday.index') }}">
                                    <i class="fas fa-house-chimney mr-2"></i><span class="sidebar-dropdown-text">Change Restday</span>
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.no-bio-request.index') ? 'bg-blue-700' : '' }}">
                                <a @click="open = false" class="w-full inline-flex items-center" href="{{ route('attendance.no-bio-request.index') }}">
                                    <i class="fas fa-fingerprint mr-2"></i><span class="sidebar-dropdown-text">No Bio Request</span>
                                </a>
                            </li>
                            <li class="px-2 py-1 transition-colors duration-150 hover:bg-blue-700 hover:text-white {{ request()->routeIs('attendance.overtime-request.index') ? 'bg-blue-700' : '' }}">
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
                @endif
                @if (Auth::user()->isEmployee())
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
        <div id="content" class="flex-1 transition-all duration-300 ease-in-out h-screen overflow-y-auto md:ml-64">
            <!-- Top Nav -->
            <nav class="bg-[#0B1432] text-white p-4">
                <div class="flex items-center px-4">
                    <!-- Top hamburger (overrides to front overlay) -->
                    <button id="mobileSidebarToggle" class="text-white mr-4 md:hidden focus:outline-none">
                        <i class="fas fa-bars text-xl align-middle"></i>
                    </button>
                    <span id="mobileNavTitle" class="text-2xl font-bold whitespace-nowrap md:hidden">LIMEHILLS HRIS</span>
                    <div class="flex-1">
                    </div>
                    
                    <!-- Notifications Dropdown -->
                    <div class="relative ml-auto mr-4">
                        <button id="notificationDropdownToggle" class="relative rounded-full p-3 focus:outline-none hover:bg-blue-800 transition-colors">
                            <i class="fas fa-bell text-2xl text-white"></i>
                            <span id="notificationBadge" class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </button>
                        <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-xl py-1 z-[999] hidden max-h-96 overflow-hidden flex flex-col">
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
                    
                    <div class="relative">
                        <button id="profileDropdownToggle" class="rounded-full p-3 focus:outline-none">
                            <i class="fas fa-user-circle text-3xl text-white"></i>
                        </button>
                        <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[999] hidden">
                            <a href="{{ route('password.change') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-key mr-2"></i>Change Password</a>
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
    {{-- Mobile overlay used when sidebar is open on small screens --}}
    <div id="mobile-overlay" class="fixed inset-0 bg-transparent z-40 hidden"></div>

    {{-- Render modals and stacked scripts pushed from views --}}
    @stack('modals')
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileDropdownToggle = document.getElementById('profileDropdownToggle');
            const profileDropdown = document.getElementById('profileDropdown');
            const notificationDropdownToggle = document.getElementById('notificationDropdownToggle');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationList = document.getElementById('notificationList');
            const notificationBadge = document.getElementById('notificationBadge');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const notificationEmpty = document.getElementById('notificationEmpty');

            // Profile dropdown
            profileDropdownToggle.addEventListener('click', function() {
                profileDropdown.classList.toggle('hidden');
                notificationDropdown.classList.add('hidden');
            });

            // Notification dropdown
            notificationDropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
                profileDropdown.classList.add('hidden');
                if (!notificationDropdown.classList.contains('hidden')) {
                    loadNotifications();
                }
            });

            // Close dropdowns if the user clicks outside of them
            window.addEventListener('click', function(e) {
                if (!profileDropdownToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
                if (!notificationDropdownToggle.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });

            // Load notifications function
            function loadNotifications() {
                fetch('{{ route("notifications.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    notificationList.innerHTML = '';
                    notificationEmpty.classList.add('hidden');
                    
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            const notificationItem = document.createElement('a'); // Changed to <a> tag
                            notificationItem.href = notification.link || '#'; // Use the link from the notification data
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
                                if (notification.link) {
                                    e.preventDefault(); // Prevent default if a link is present, handle redirection manually
                                    window.location.href = notification.link;
                                }
                                markAsRead(notification.id);
                            });
                            notificationList.appendChild(notificationItem);
                        });
                        
                        if (data.unread_count > 0) {
                            notificationBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                            notificationBadge.classList.remove('hidden');
                            markAllReadBtn.classList.remove('hidden');
                        } else {
                            notificationBadge.classList.add('hidden');
                            markAllReadBtn.classList.add('hidden');
                        }
                    } else {
                        notificationEmpty.classList.remove('hidden');
                        notificationBadge.classList.add('hidden');
                        markAllReadBtn.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    notificationList.innerHTML = '<div class="px-4 py-8 text-center text-red-500">Error loading notifications</div>';
                });
            }

            // Mark notification as read
            function markAsRead(notificationId) {
                // Skip admin notifications (they're virtual)
                if (notificationId.toString().startsWith('admin-')) {
                    return;
                }
                
                fetch(`{{ url('notifications') }}/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadNotifications();
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            }

            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fetch('{{ route("notifications.markAllRead") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadNotifications();
                    })
                    .catch(error => {
                        console.error('Error marking all notifications as read:', error);
                    });
                });
            }

            // Load notifications on page load
            loadNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);

            const sidebar = document.getElementById('sidebar');
            const sidebarTitle = document.getElementById('sidebarTitle');
            const content = document.getElementById('content');
            const navSpans = document.querySelectorAll('#sidebar .sidebar-text');
            const dropdownSpans = document.querySelectorAll('#sidebar .sidebar-dropdown-text');
            const mobileOverlay = document.getElementById('mobile-overlay');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const mobileNavTitle = document.getElementById('mobileNavTitle');

            // Function to set sidebar state. On small screens the sidebar is hidden by default and uses an overlay.
            function setSidebarState() {
                // Remove the initial FOUC class and re-enable transitions once JS takes over
                document.documentElement.classList.remove('has-initial-sidebar-collapsed');
                // Re-enable transitions explicitly if they were disabled for FOUC
                sidebar.style.transition = 'all 0.3s ease-in-out';
                content.style.transition = 'all 0.3s ease-in-out';

                const sidebarOpenInStorage = localStorage.getItem('sidebarOpen');
                const sidebarShouldBeOpen = sidebarOpenInStorage === null ? true : sidebarOpenInStorage === 'true';

                if (window.innerWidth >= 768) {
                    // Desktop: show sidebar inline (expanded or compact)
                    mobileOverlay.classList.add('hidden');
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');

                    if (!sidebarShouldBeOpen) {
                        // compact
                        sidebar.classList.add('w-16', 'sidebar-collapsed');
                        sidebar.classList.remove('w-64');
                        navSpans.forEach(span => span.classList.add('hidden'));
                        dropdownSpans.forEach(span => span.classList.add('hidden'));
                        content.classList.remove('md:ml-64');
                        content.classList.add('md:ml-16'); // compact margin
                    } else {
                        // expanded
                        sidebar.classList.remove('w-16', 'sidebar-collapsed');
                        sidebar.classList.add('w-64');
                        sidebarTitle.classList.remove('hidden');
                        navSpans.forEach(span => span.classList.remove('hidden'));
                        dropdownSpans.forEach(span => span.classList.remove('hidden')); // Fix: apply to each span
                        content.classList.remove('md:ml-16'); // Ensure correct margin
                        content.classList.add('md:ml-64'); // expanded margin
                    }
                } else {
                    // Mobile: sidebar is always a full-width overlay. State in localStorage determines if it slides in or out.
                    content.classList.remove('md:ml-64', 'md:ml-16'); // ensure no desktop margins
                    sidebar.classList.remove('w-16', 'sidebar-collapsed');
                    sidebar.classList.add('w-64'); // Always full width on mobile
                    sidebarTitle.classList.remove('hidden');
                    navSpans.forEach(span => span.classList.remove('hidden'));
                    dropdownSpans.forEach(span => span.classList.remove('hidden'));

                    if (sidebarShouldBeOpen) {
                        openOverlaySidebar(); // Use existing function to correctly open on mobile
                    } else {
                        closeOverlaySidebar(); // Use existing function to correctly close on mobile
                    }
                }
            }

            // Set initial state on load
            setSidebarState();

            // Adjust state on resize
            window.addEventListener('resize', setSidebarState);

            // Sidebar compact/expand toggle for desktop, and overlay open/close for mobile
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth >= 768) {
                        // desktop compact/expand
                        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                        // Always remove both margin classes before adding the correct one
                        content.classList.remove('md:ml-16', 'md:ml-64');
                        if (isCollapsed) {
                            sidebar.classList.remove('w-16', 'sidebar-collapsed');
                            sidebar.classList.add('w-64');
                            sidebarTitle.classList.remove('hidden');
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
                        // mobile: toggle overlay sidebar
                        toggleOverlaySidebar();
                    }
                });
            }

            // Helper functions to open/close/toggle overlay sidebar
            function openOverlaySidebar() {
                sidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.remove('hidden');
                // bring sidebar above overlay
                sidebar.style.zIndex = 60;
                localStorage.setItem('sidebarOpen', 'true');
                // Optionally hide mobileNavTitle when sidebar opens
                if (mobileNavTitle) mobileNavTitle.classList.add('hidden');
                // optionally disable body scroll while open
                document.body.classList.add('overflow-hidden');
            }

            function closeOverlaySidebar() {
                sidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                localStorage.setItem('sidebarOpen', 'false');
                // Optionally show mobileNavTitle when sidebar closes
                if (mobileNavTitle) mobileNavTitle.classList.remove('hidden');
                // restore body scroll
                document.body.classList.remove('overflow-hidden');
            }

            function toggleOverlaySidebar() {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openOverlaySidebar();
                } else {
                    closeOverlaySidebar();
                }
            }

            // Mobile top hamburger toggles sidebar overlay (does not push content)
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleOverlaySidebar();
                });
            }

            // Also make the sidebar internal toggle behave as a toggle on mobile
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    if (window.innerWidth < 768) {
                        // on mobile act as overlay toggle
                        e.stopPropagation();
                        toggleOverlaySidebar();
                        return;
                    }
                    // desktop behavior handled earlier
                });
            }

            // Clicking overlay closes the sidebar on mobile
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    closeOverlaySidebar();
                });
            }

            // Auto-close overlay when a sidebar link is clicked
            const sidebarLinks = document.querySelectorAll('#sidebar nav a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only close overlay immediately for mobile
                    if (window.innerWidth < 768) {
                        setTimeout(() => {
                            closeOverlaySidebar();
                            if (mobileNavTitle) mobileNavTitle.classList.remove('hidden'); // Ensure it's shown on close
                        }, 80);
                    }
                    // For desktop, do nothing on click to keep the collapsed state, just navigate
                });
            });
        });
    </script>
</body>
</html>