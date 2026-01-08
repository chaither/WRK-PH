<?php

namespace App\Http\Controllers;

use App\Models\OrganizationChartNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EmployeeOrganizationChartController extends Controller
{
    public function index()
    {
        $ceoNode = null;

        // If table doesn't exist, don't throw 500 – just log and show empty state
        if (!Schema::hasTable('organization_chart_nodes')) {
            Log::error('organization_chart_nodes table does not exist (employee view)');
            return view('employee.organization.index', compact('ceoNode'));
        }

        // Try to ensure base nodes exist, but don't break the page if it fails
        try {
            $this->ensureBaseNodes();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in EmployeeOrganizationChartController::ensureBaseNodes: ' . $e->getMessage());
            // continue – we will still try to read whatever nodes exist
        } catch (\Exception $e) {
            Log::error('Error in EmployeeOrganizationChartController::ensureBaseNodes: ' . $e->getMessage());
            // continue
        }

        // Load nodes; if this fails it will surface a clear error in logs
        $nodes = OrganizationChartNode::with('children')->get();
        $departments = \App\Models\Department::with('employees')->orderBy('name')->get();
        $ceoNode = $nodes->whereNull('parent_id')->first();

        return view('employee.organization.index', compact('ceoNode', 'departments'));
    }

    /**
     * Ensure required top-level nodes exist for display.
     */
    private function ensureBaseNodes(): void
    {
        // Table already checked in index(), but keep this guard to be safe
        if (!Schema::hasTable('organization_chart_nodes')) {
            throw new \Exception('organization_chart_nodes table does not exist. Please run migrations.');
        }

        // Create CEO if missing
        $ceo = OrganizationChartNode::where('position', 'CEO')
            ->whereNull('parent_id')
            ->first();

        if (!$ceo) {
            $existingRoot = OrganizationChartNode::whereNull('parent_id')->first();

            if (!$existingRoot) {
                $ceo = OrganizationChartNode::create([
                    'name' => 'CEO',
                    'position' => 'CEO',
                    'parent_id' => null,
                ]);
                Log::info('Created CEO node for employee org view');
            } else {
                $ceo = $existingRoot;
                Log::info('Using existing root node as CEO for employee org view');
            }
        }

        // Create or reattach CO-CEO under CEO
        if ($ceo) {
            $coCeo = OrganizationChartNode::where('position', 'CO-CEO')->first();
            if (!$coCeo) {
                $coCeo = OrganizationChartNode::create([
                    'name' => 'CO-CEO',
                    'position' => 'CO-CEO',
                    'parent_id' => $ceo->id,
                ]);
                Log::info('Created CO-CEO node for employee org view');
            } elseif ($coCeo->parent_id !== $ceo->id) {
                $coCeo->update(['parent_id' => $ceo->id]);
                Log::info('Updated CO-CEO parent to CEO for employee org view');
            }

            // Create or reattach HR Manager under CO-CEO
            if ($coCeo) {
                $hrManager = OrganizationChartNode::where('position', 'HR Manager')->first();
                if (!$hrManager) {
                    OrganizationChartNode::create([
                        'name' => 'HR Manager',
                        'position' => 'HR Manager',
                        'parent_id' => $coCeo->id,
                    ]);
                    Log::info('Created HR Manager node for employee org view');
                } elseif ($hrManager->parent_id !== $coCeo->id) {
                    $hrManager->update(['parent_id' => $coCeo->id]);
                    Log::info('Updated HR Manager parent to CO-CEO for employee org view');
                }
            }
        }
    }
}
