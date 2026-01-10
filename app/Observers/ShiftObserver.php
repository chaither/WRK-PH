<?php

namespace App\Observers;

use App\Models\Shift;
use App\Services\BiometricSyncService;

class ShiftObserver
{
    protected $syncService;

    public function __construct(BiometricSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(Shift $shift)
    {
        // Skip sync if this was created via API (from biometric-app)
        // to prevent circular sync
        if (request()->is('api/*')) {
            return;
        }
        
        $this->syncService->syncShift($shift);
    }

    public function updated(Shift $shift)
    {
        $this->syncService->syncShift($shift);
    }

    public function deleted(Shift $shift)
    {
        $this->syncService->deleteShift($shift->id);
    }
}
