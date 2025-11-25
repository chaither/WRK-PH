<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationChartController extends Controller
{
    public function index()
    {
        $nodes = \App\Models\OrganizationChartNode::all();
        return view('organization.index', compact('nodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_chart_nodes,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $node = \App\Models\OrganizationChartNode::findOrFail($id);

        $imagePath = $node->image_path;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
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
}
