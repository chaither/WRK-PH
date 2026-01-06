<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZktecoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ZktecoController extends Controller
{
    protected $zktecoService;

    public function __construct(ZktecoService $zktecoService)
    {
        $this->zktecoService = $zktecoService;
    }

    public function deviceInfo(): JsonResponse
    {
        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $info = $this->zktecoService->getDeviceInfo();
            $this->zktecoService->disconnect();

            return response()->json($info);
        } catch (\Exception $e) {
            Log::error('Error getting device info: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get device info'], 500);
        }
    }

    public function getUsers(): JsonResponse
    {
        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $users = $this->zktecoService->getUsers();
            $this->zktecoService->disconnect();

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error getting users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get users'], 500);
        }
    }

    public function getAttendances(Request $request): JsonResponse
    {
        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $callback = null;
            if ($request->has('user_id')) {
                $userId = $request->input('user_id');
                $callback = function ($item) use ($userId) {
                    return $item['id'] == $userId;
                };
            }

            $attendances = $this->zktecoService->getAttendances($callback);
            $this->zktecoService->disconnect();

            return response()->json($attendances);
        } catch (\Exception $e) {
            Log::error('Error getting attendances: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get attendances'], 500);
        }
    }

    public function clearAttendances(): JsonResponse
    {
        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $result = $this->zktecoService->clearAttendances();
            $this->zktecoService->disconnect();

            if ($result) {
                return response()->json(['message' => 'Attendances cleared successfully']);
            } else {
                return response()->json(['error' => 'Failed to clear attendances'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error clearing attendances: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to clear attendances'], 500);
        }
    }

    public function setUser(Request $request): JsonResponse
    {
        $request->validate([
            'uid' => 'required|integer',
            'userid' => 'required|string',
            'name' => 'required|string',
            'password' => 'nullable|string',
            'role' => 'nullable|integer',
        ]);

        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $result = $this->zktecoService->setUser(
                $request->uid,
                $request->userid,
                $request->name,
                $request->password ?? '',
                $request->role ?? 0
            );
            $this->zktecoService->disconnect();

            if ($result) {
                return response()->json(['message' => 'User set successfully']);
            } else {
                return response()->json(['error' => 'Failed to set user'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error setting user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to set user'], 500);
        }
    }

    public function deleteUser(Request $request): JsonResponse
    {
        $request->validate([
            'uid' => 'required|integer',
        ]);

        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $result = $this->zktecoService->deleteUser($request->uid);
            $this->zktecoService->disconnect();

            if ($result) {
                return response()->json(['message' => 'User deleted successfully']);
            } else {
                return response()->json(['error' => 'Failed to delete user'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete user'], 500);
        }
    }

    public function getTime(): JsonResponse
    {
        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $time = $this->zktecoService->getTime();
            $this->zktecoService->disconnect();

            return response()->json(['time' => $time]);
        } catch (\Exception $e) {
            Log::error('Error getting time: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get time'], 500);
        }
    }

    public function setTime(Request $request): JsonResponse
    {
        $request->validate([
            'time' => 'required|date',
        ]);

        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $result = $this->zktecoService->setTime($request->time);
            $this->zktecoService->disconnect();

            if ($result) {
                return response()->json(['message' => 'Time set successfully']);
            } else {
                return response()->json(['error' => 'Failed to set time'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error setting time: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to set time'], 500);
        }
    }

    public function syncUsers(): JsonResponse
    {
        try {
            if (!$this->zktecoService->connect()) {
                return response()->json(['error' => 'Unable to connect to ZKTeco device'], 500);
            }

            $synced = $this->zktecoService->syncUsersToDevice();
            $this->zktecoService->disconnect();

            if ($synced !== false) {
                return response()->json(['message' => "Synced {$synced} users to device successfully"]);
            } else {
                return response()->json(['error' => 'Failed to sync users'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error syncing users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync users'], 500);
        }
    }

    public function syncAttendances(Request $request): JsonResponse
    {
        try {
            $clearDevice = $request->input('clear_device', false);
            
            $result = $this->zktecoService->syncAttendancesToDTR($clearDevice);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'synced' => $result['synced'] ?? 0,
                    'skipped' => $result['skipped'] ?? 0
                ], 500);
            }

            return response()->json([
                'message' => 'Attendance sync completed',
                'synced' => $result['synced'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'skip_reasons' => $result['skip_reasons'] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing attendances: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync attendances: ' . $e->getMessage()], 500);
        }
    }
}