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
        <div class="bg-blue-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
            <div class="flex items-center space-x-2 px-4 mb-6">
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
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white text-center">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1">
            <!-- Top Navigation -->
            <div class="bg-white shadow-md p-4">
                <div class="flex justify-between items-center">
                    <button class="block md:hidden" onclick="document.querySelector('.sidebar').classList.toggle('-translate-x-full')">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="flex items-center space-x-4">
                        <span>{{ auth()->user()->name }}</span>
                        <span class="px-2 py-1 rounded-full text-xs capitalize" style="background-color: #EEE">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                </div>
        @php $viteManifest = public_path('build/manifest.json'); @endphp
        @unless (file_exists($viteManifest))
        <!-- Minimal JS fallback when assets haven't been built. Keeps modal and delete helpers available in dev. -->
        <script>
        (function(){
            if (window.openEmployeeModal) return;
            window.openEmployeeModal = function(){
                const modal = document.getElementById('employeeModal');
                if (!modal) return;
                modal.classList.remove('hidden'); modal.classList.add('flex');
            };
            window.closeEmployeeModal = function(){
                const modal = document.getElementById('employeeModal');
                const form = document.getElementById('employeeForm');
                if (!modal) return;
                modal.classList.remove('flex'); modal.classList.add('hidden');
                if (form) form.reset();
            };
            window.deleteEmployee = function(id){
                if (confirm('Are you sure you want to delete this employee?')){
                    const form = document.createElement('form'); form.method='POST'; form.action=`/employees/${id}`;
                    const csrf = document.createElement('input'); csrf.type='hidden'; csrf.name='_token'; csrf.value=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'';
                    const method = document.createElement('input'); method.type='hidden'; method.name='_method'; method.value='DELETE';
                    form.appendChild(csrf); form.appendChild(method); document.body.appendChild(form); form.submit();
                }
            };
        })();
        </script>
        @endunless
            </div>

            <!-- Main Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
    {{-- Render modals and stacked scripts pushed from views --}}
    @stack('modals')
    @stack('scripts')
</body>
</html>