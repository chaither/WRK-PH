<?php
use App\Models\User;
use App\Models\Department;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

$dept = Department::first();
$shift = Shift::first();

if (!$dept || !$shift) {
    echo "Error: Need at least one department and shift in HRIS.";
    exit(1);
}

$userData = [
    'first_name' => 'Sync',
    'last_name' => 'Tester',
    'email' => 'sync.tester@' . time() . '.com',
    'password' => Hash::make('password'),
    'role' => 'employee',
    'employee_id' => 'SYNC' . rand(100, 999),
    'department_id' => $dept->id,
    'shift_id' => $shift->id,
    'position' => 'Automation Tester',
    'status' => 'active'
];

echo "Attempting to create employee in HRIS...\n";
$user = User::create($userData);

if ($user) {
    echo "SUCCESS: Created Employee '{$user->first_name} {$user->last_name}' (ID: {$user->employee_id}) in HRIS.\n";
    echo "The Observer should have triggered the sync now.\n";
} else {
    echo "FAILED to create employee.\n";
}
