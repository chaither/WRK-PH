<?php

namespace App\Http\Controllers;

use App\Models\OrganizationChartNode;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class OrganizationChartController extends Controller
{
    public function index()
    {
        try {
            $this->ensureBaseNodes();
        } catch (\Exception $e) {
            // Log the error and continue - don't break the page if base nodes can't be created
            Log::error('Failed to ensure base nodes: ' . $e->getMessage());
        }

        try {
            $nodes = OrganizationChartNode::all();
            $departments = Department::with('employees')->orderBy('name')->get();
        } catch (\Exception $e) {
            // If there's a database error, log it and return empty collections
            Log::error('Failed to load organization data: ' . $e->getMessage());
            $nodes = collect([]);
            $departments = collect([]);
        }

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
        try {
            // Create CEO if missing
            $ceo = OrganizationChartNode::whereNull('parent_id')
                ->where('position', 'CEO')
                ->first();

            if (!$ceo) {
                $ceo = OrganizationChartNode::create([
                    'name' => 'CEO',
                    'position' => 'CEO',
                    'parent_id' => null,
                ]);
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
                } elseif ($coCeo->parent_id !== $ceo->id) {
                    $coCeo->update(['parent_id' => $ceo->id]);
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
                    } elseif ($hrManager->parent_id !== $coCeo->id) {
                        $hrManager->update(['parent_id' => $coCeo->id]);
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // If there's a database issue (e.g., table doesn't exist, constraint violation), log and rethrow
            Log::error('Database error in ensureBaseNodes: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Log any other errors
            Log::error('Error in ensureBaseNodes: ' . $e->getMessage());
            throw $e;
        }
    }
}
