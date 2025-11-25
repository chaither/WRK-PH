<?php

namespace App\Http\Controllers;

use App\Models\OrganizationChartNode;
use Illuminate\Http\Request;

class EmployeeOrganizationChartController extends Controller
{
    public function index()
    {
        $nodes = OrganizationChartNode::all();
        $ceoNode = $nodes->whereNull('parent_id')->first();
        return view('employee.organization.index', compact('ceoNode'));
    }
}
