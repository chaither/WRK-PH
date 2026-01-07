<?php

namespace App\Http\Controllers;

use App\Models\OrganizationChartNode;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class OrganizationChartController extends Controller
{
    public function index()
    {
        // First, ensure base nodes exist
        try {
            $this->ensureBaseNodes();
        } catch (\Illuminate\Database\QueryException $e) {
            // If table doesn't exist, log and show error to user
            Log::error('Database error ensuring base nodes: ' . $e->getMessage());
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                return redirect()->back()->with('error', 'Organization chart table not found. Please run migrations: php artisan migrate');
            }
            // For other database errors, log and continue
            Log::warning('Failed to ensure base nodes, but continuing: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Log the error but continue to try loading existing nodes
            Log::error('Error ensuring base nodes: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }

        // Load existing nodes - don't catch exceptions here, let Laravel handle them properly
        $nodes = OrganizationChartNode::all();
        $departments = Department::with('employees')->orderBy('name')->get();

        return view('organization.index', compact('nodes', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_chart_nodes,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            Storage::disk('public')->makeDirectory('organization_chart_images');
            $imagePath = $request->file('image')->store('organization_chart_images', 'public');
        }

        \App\Models\OrganizationChartNode::create([
            'name' => $request->name,
            'position' => $request->position,
            'parent_id' => $request->parent_id,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('organization.index')->with('success', 'Node added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_chart_nodes,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $node = \App\Models\OrganizationChartNode::findOrFail($id);

        $imagePath = $node->image_path;
        if ($request->hasFile('image')) {
            Storage::disk('public')->makeDirectory('organization_chart_images');
            // Delete old image if exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('organization_chart_images', 'public');
        }

        $node->update([
            'name' => $request->name,
            'position' => $request->position,
            'parent_id' => $request->parent_id,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('organization.index')->with('success', 'Node updated successfully.');
    }

    public function destroy($id)
    {
        $node = \App\Models\OrganizationChartNode::findOrFail($id);

        // Delete image if exists
        if ($node->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($node->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($node->image_path);
        }

        $node->delete();

        return redirect()->route('organization.index')->with('success', 'Node deleted successfully.');
    }

    /**
     * Ensure required top-level nodes exist and are linked correctly.
     */
    private function ensureBaseNodes(): void
    {
        // Check if table exists first
        if (!Schema::hasTable('organization_chart_nodes')) {
            throw new \Exception('organization_chart_nodes table does not exist. Please run migrations.');
        }

        // Create CEO if missing - check by position first, then by null parent
        $ceo = OrganizationChartNode::where('position', 'CEO')
            ->whereNull('parent_id')
            ->first();

        if (!$ceo) {
            // Check if there's any node with null parent (could be an existing CEO with different position name)
            $existingRoot = OrganizationChartNode::whereNull('parent_id')->first();
            
            if (!$existingRoot) {
                $ceo = OrganizationChartNode::create([
                    'name' => 'CEO',
                    'position' => 'CEO',
                    'parent_id' => null,
                ]);
                Log::info('Created CEO node in organization chart');
            } else {
                // Use existing root node as CEO
                $ceo = $existingRoot;
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
                Log::info('Created CO-CEO node in organization chart');
            } elseif ($coCeo->parent_id !== $ceo->id) {
                $coCeo->update(['parent_id' => $ceo->id]);
                Log::info('Updated CO-CEO parent to CEO');
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
                    Log::info('Created HR Manager node in organization chart');
                } elseif ($hrManager->parent_id !== $coCeo->id) {
                    $hrManager->update(['parent_id' => $coCeo->id]);
                    Log::info('Updated HR Manager parent to CO-CEO');
                }
            }
        }
    }
}
