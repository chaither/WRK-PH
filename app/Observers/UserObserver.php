<?php

namespace App\Observers;

use App\Models\User;
use App\Services\BiometricSyncService;

class UserObserver
{
    protected $syncService;

    public function __construct(BiometricSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(User $user)
    {
        \Log::info("UserObserver::created triggered for user ID: {$user->id}, role: {$user->role}");
        
        // Skip sync if this was created via API (from biometric-app)
        // to prevent circular sync
        if (request()->is('api/*')) {
            \Log::info("Skipping sync - created via API");
            return;
        }
        
        if ($user->isEmployee()) {
            \Log::info("Syncing employee {$user->id} to biometric app");
            $this->syncService->syncEmployee($user);
        } else {
            \Log::info("User {$user->id} is not an employee, role: {$user->role}");
        }
    }

    public function updated(User $user)
    {
        if ($user->isEmployee()) {
            $this->syncService->syncEmployee($user);
        }
    }

    public function deleted(User $user)
    {
        if ($user->isEmployee()) {
            $this->syncService->deleteEmployee($user->id);
        }
    }
}
