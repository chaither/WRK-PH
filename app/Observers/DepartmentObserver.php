<?php

namespace App\Observers;

use App\Models\Department;
use App\Services\BiometricSyncService;

class DepartmentObserver
{
    protected $syncService;

    public function __construct(BiometricSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(Department $department)
    {
        // Skip sync if this was created via API (from biometric-app)
        // to prevent circular sync
        if (request()->is('api/*')) {
            return;
        }
        
        $this->syncService->syncDepartment($department);
    }

    public function updated(Department $department)
    {
        $this->syncService->syncDepartment($department);
    }

    public function deleted(Department $department)
    {
        $this->syncService->deleteDepartment($department->id);
    }
}
