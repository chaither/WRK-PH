<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiometricSyncService
{
    protected $baseUrl;

    public function __construct()
    {
        // Address of the Biometric App (running on port 8081 in our semi-deployment)
        $this->baseUrl = config('services.biometric_app.url', 'http://localhost:8081');
    }

    public function syncDepartment($department)
    {
        try {
            // Fire and forget, or wait for response?
            // Since we want near-realtime, we'll try to push immediately.
            // If the biometric app is down, we might want to queue this, 
            // but for now, we'll just log errors.
            Http::timeout(2)->post($this->baseUrl . '/api/sync/departments', [
                'id' => $department->id,
                'name' => $department->name,
                // Add fields as necessary
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync department {$department->name} to biometric app: " . $e->getMessage());
        }
    }

    public function deleteDepartment($departmentId)
    {
        try {
            Http::timeout(2)->delete($this->baseUrl . "/api/sync/departments/{$departmentId}");
        } catch (\Exception $e) {
            Log::error("Failed to sync delete department {$departmentId} to biometric app: " . $e->getMessage());
        }
    }

    public function syncEmployee($employee)
    {
        try {
            Http::timeout(5)->post($this->baseUrl . '/api/sync/employees', [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'position' => $employee->position,
                'employee_id' => $employee->employee_id,
                'card_number' => $employee->card_number ?? '',
                'department_id' => $employee->department_id,
                'shift_id' => $employee->shift_id,
                'role' => $employee->role,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync employee {$employee->id} to biometric app: " . $e->getMessage());
        }
    }

    public function deleteEmployee($employeeId)
    {
        try {
            Http::timeout(2)->delete($this->baseUrl . "/api/sync/employees/{$employeeId}");
        } catch (\Exception $e) {
            Log::error("Failed to sync delete employee {$employeeId} to biometric app: " . $e->getMessage());
        }
    }

    public function syncShift($shift)
    {
        try {
            Http::timeout(5)->post($this->baseUrl . '/api/sync/shifts', [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => $shift->start_time ? $shift->start_time->format('H:i:s') : null,
                'end_time' => $shift->end_time ? $shift->end_time->format('H:i:s') : null,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync shift {$shift->name} to biometric app: " . $e->getMessage());
        }
    }

    public function deleteShift($shiftId)
    {
        try {
            Http::timeout(2)->delete($this->baseUrl . "/api/sync/shifts/{$shiftId}");
        } catch (\Exception $e) {
            Log::error("Failed to sync delete shift {$shiftId} to biometric app: " . $e->getMessage());
        }
    }
}
