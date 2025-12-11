<?php

namespace App\Http\Controllers;

use App\Models\OrganizationChartNode;
use Illuminate\Http\Request;

class EmployeeOrganizationChartController extends Controller
{
    public function index()
    {
        $this->ensureBaseNodes();

        $nodes = OrganizationChartNode::all();
        $ceoNode = $nodes->whereNull('parent_id')->first();
        return view('employee.organization.index', compact('ceoNode'));
    }

    /**
     * Ensure required top-level nodes exist for display.
     */
    private function ensureBaseNodes(): void
    {
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
