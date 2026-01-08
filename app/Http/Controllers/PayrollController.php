<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DTRRecord;
use App\Models\PayPeriod;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Log; // Added for debugging

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->middleware(['auth']);
        $this->middleware(\App\Http\Middleware\EnsureHrAdminRole::class)->except(['employeePayslips', 'showPayslip']); // Apply HR/Admin role to most methods, but allow all authenticated users for employeePayslips and showPayslip
        $this->payrollService = $payrollService;
    }

    /**
     * Display a listing of payslips for the authenticated employee.
     */
    public function employeePayslips()
    {
        $user = Auth::user();
        $payslips = Payslip::where('user_id', $user->id)
            ->orderByDesc('pay_period_end')
            ->get();
        
        return view('payroll.employee_payslips', compact('payslips'));
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view payroll.');
        }

        $payPeriods = PayPeriod::orderBy('end_date', 'desc')->get();
        $today = Carbon::today();
        $start = $request->input('start_date', $today->copy()->startOfMonth()->format('Y-m-d'));
        $end = $request->input('end_date', $today->copy()->endOfMonth()->format('Y-m-d'));

        $departmentIds = $request->input('department_ids', []); // Initialize departmentIds from request
        // Handle "All Departments" case: if no specific departments are selected,
        // or if an empty string/null is passed, treat as all departments.
        if (empty($departmentIds) || (count($departmentIds) === 1 && $departmentIds[0] === "")) {
            $departmentIds = null; // Represents all departments
        }

        $payrolls = collect();
        $currentPeriod = null;

        // Determine payScheduleFilter based on selected dates (same logic as generateForRange)
        $payScheduleFilter = null;
        if (Carbon::parse($start)->day === 1 && Carbon::parse($end)->day === 15) {
            $payScheduleFilter = 'semi-monthly';
        } elseif (Carbon::parse($start)->day === 16 && Carbon::parse($end)->isSameDay(Carbon::parse($end)->endOfMonth())) {
            $payScheduleFilter = 'semi-monthly';
        } elseif (Carbon::parse($start)->isSameDay(Carbon::parse($start)->startOfMonth()) && Carbon::parse($end)->isSameDay(Carbon::parse($end)->endOfMonth())) {
            $payScheduleFilter = 'monthly';
        }

        // ALWAYS calculate totalEmployees based on the determined pay schedule filter from the User model.
        // This count represents *eligible* employees, regardless of whether payslips are generated.
        $totalEmployeesQuery = User::where('role', 'employee');
        if ($payScheduleFilter) {
            $totalEmployeesQuery->where('pay_schedule', $payScheduleFilter);
        }
        $totalEmployees = $totalEmployeesQuery->count();

        // Try to find an existing pay period for the selected dates (for gross/net pay, etc.)
        $currentPeriod = PayPeriod::where('start_date', $start)->where('end_date', $end)->first();

        if ($currentPeriod) {
            Log::info('Current PayPeriod status before view: ' . $currentPeriod->status, [
                'department_ids' => $departmentIds,
                'start_date' => $start,
                'end_date' => $end
            ]);
            
            $payslipQuery = Payslip::with(['user', 'user.department', 'payPeriod'])
                ->where('pay_period_id', $currentPeriod->id);
            
            // Filter by department_ids if provided
            if ($departmentIds !== null && is_array($departmentIds) && !empty($departmentIds)) {
                $payslipQuery->whereHas('user', function($query) use ($departmentIds) {
                    $query->whereIn('department_id', $departmentIds);
                });
                Log::info('Filtering payslips by departments', ['department_ids' => $departmentIds]);
            }
            
            $payrolls = $payslipQuery->get();
            Log::info('Payrolls loaded', ['count' => $payrolls->count(), 'department_ids' => $departmentIds]);
            
        } else {
            // If no currentPeriod is found, there are no existing payroll records for this range.
            // Therefore, ensure $payrolls is an empty collection so sums are 0.
            $payrolls = collect();
        }

        // If departmentIds is null (all departments), or an array (one or more specific departments),
        // we want to display grouped tables.
        // The only exception where we wouldn't want to group is if there were *no* department filtering at all,
        // but the modal ensures department_ids is always sent.
        $isGroupedByDepartment = ($departmentIds === null || (is_array($departmentIds) && count($departmentIds) > 0));

        $groupedPayslips = collect(); // Initialize as an empty collection

        if ($isGroupedByDepartment) {
            // Group the *filtered* payslips by department name
            $groupedPayslips = $payrolls->groupBy(function ($payslip) {
                return $payslip->user->department->name ?? 'Unassigned';
            });
        }

        $totalGrossPay = $payrolls->sum('gross_pay');
        $totalDeductions = $payrolls->sum('deductions');
        $totalNetPay = $payrolls->sum('net_pay');

        $departments = \App\Models\Department::all(); // Fetch all departments

        // Fetch a global overtime multiplier (e.g., from the first employee, since it's global)
        $globalOvertimeMultiplier = User::where('role', 'employee')->first()->overtime_multiplier ?? 1.5;

        return view('payroll.index', compact('payPeriods', 'currentPeriod', 'payrolls', 'totalEmployees', 'totalGrossPay', 'totalDeductions', 'totalNetPay', 'start', 'end', 'departments', 'groupedPayslips', 'isGroupedByDepartment', 'globalOvertimeMultiplier'));
    }

    public function createPayPeriod(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to process payroll.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        PayPeriod::create($validated);
        return redirect()->route('payroll.index')->with('success', 'Pay period created successfully');
    }

    public function generatePayslips(PayPeriod $payPeriod)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to process payroll.');
        }

        try {
            // Call the PayrollService to generate payslips
            // Determine pay schedule filter based on the pay period duration
            $payScheduleFilter = null;
            if ($payPeriod->start_date->day === 1 && $payPeriod->end_date->day === 15) {
                $payScheduleFilter = 'semi-monthly';
            } elseif ($payPeriod->start_date->day === 16 && $payPeriod->end_date->isSameDay($payPeriod->end_date->endOfMonth())) {
                $payScheduleFilter = 'semi-monthly';
            } elseif ($payPeriod->start_date->isSameDay($payPeriod->start_date->copy()->startOfMonth()) && $payPeriod->end_date->isSameDay($payPeriod->end_date->copy()->endOfMonth())) {
                $payScheduleFilter = 'monthly';
            }
            
            // Convert date objects to Carbon instances with proper formatting
            $startDateCarbon = Carbon::parse($payPeriod->start_date)->startOfDay();
            $endDateCarbon = Carbon::parse($payPeriod->end_date)->endOfDay();
            
            $this->payrollService->generatePayslipsForPeriod($startDateCarbon, $endDateCarbon, $payScheduleFilter);

            // Mark period as completed after creating payslips
            $payPeriod->update(['status' => 'unpaid']); // Or 'processing' if there's another step

            return redirect()->route('payroll.index')->with('success', 'Payslips generated successfully');
        } catch (\Exception $e) {
            Log::error('Error generating payslips: ' . $e->getMessage(), [
                'pay_period_id' => $payPeriod->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('payroll.index')->with('error', 'Failed to generate payslips: ' . $e->getMessage());
        }
    }

    // Generate for arbitrary date range submitted from the UI
    public function generateForRange(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            if ($request->ajax() || $request->has('ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to process payroll.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'You are not authorized to process payroll.');
        }

        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'department_ids' => 'nullable|array', // Add department_ids validation
            ]);

            $start = $request->input('start_date');
            $end = $request->input('end_date');
            $departmentIds = $request->input('department_ids', []); // Get array of department IDs
            
            // Handle "All Departments" case: if no specific departments are selected,
            // or if an empty string/null is passed, treat as all departments.
            if (empty($departmentIds) || (count($departmentIds) === 1 && $departmentIds[0] === "")) {
                $departmentIds = null; // Represents all departments
            }

            $payScheduleFilter = null;
            if (Carbon::parse($start)->day === 1 && Carbon::parse($end)->day === 15) {
                $payScheduleFilter = 'semi-monthly';
            } elseif (Carbon::parse($start)->day === 16 && Carbon::parse($end)->isSameDay(Carbon::parse($end)->endOfMonth())) {
                $payScheduleFilter = 'semi-monthly';
            } elseif (Carbon::parse($start)->isSameDay(Carbon::parse($start)->startOfMonth()) && Carbon::parse($end)->isSameDay(Carbon::parse($end)->endOfMonth())) {
                $payScheduleFilter = 'monthly';
            }

            // Ensure dates are in the correct format for database comparison
            $startDate = Carbon::parse($start)->format('Y-m-d');
            $endDate = Carbon::parse($end)->format('Y-m-d');
            
            $payPeriod = PayPeriod::firstOrCreate([
                'start_date' => $startDate,
                'end_date' => $endDate
            ], ['status' => 'draft', 'pay_period_type' => $payScheduleFilter ?? 'semi-monthly', 'generated_by_user_id' => Auth::id()]);

            // Do not regenerate if already generated/unpaid or paid, or closed
            if (in_array($payPeriod->status, ['unpaid', 'paid', 'closed']) && !$request->has('force_regenerate')) {
                $redirectParams = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
                if (!empty($departmentIds) && is_array($departmentIds)) {
                    $redirectParams['department_ids'] = $departmentIds;
                }
                
                if ($payPeriod->status === 'closed') {
                    $message = 'Payroll for the selected period is closed and cannot be regenerated.';
                    if ($request->ajax() || $request->has('ajax')) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'redirect_url' => route('payroll.index', $redirectParams)
                        ], 400);
                    }
                    return redirect()->route('payroll.index', $redirectParams)->with('error', $message);
                } else {
                    $message = 'Payroll for the selected period has already been generated. Click \'Regenerate Payroll\' again to force regeneration.';
                    if ($request->ajax() || $request->has('ajax')) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'redirect_url' => route('payroll.index', $redirectParams)
                        ], 400);
                    }
                    return redirect()->route('payroll.index', $redirectParams)->with('info', $message);
                }
            }

            // If forced regeneration, set status to draft to allow re-generation
            if ($request->has('force_regenerate') && in_array($payPeriod->status, ['unpaid', 'paid', 'closed'])) {
                $redirectParams = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
                if (!empty($departmentIds) && is_array($departmentIds)) {
                    $redirectParams['department_ids'] = $departmentIds;
                }
                
                if ($payPeriod->status === 'closed') {
                    $message = 'Payroll for the selected period is closed and cannot be regenerated.';
                    if ($request->ajax() || $request->has('ajax')) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'redirect_url' => route('payroll.index', $redirectParams)
                        ], 400);
                    }
                    return redirect()->route('payroll.index', $redirectParams)->with('error', $message);
                }
                $payPeriod->update(['status' => 'draft', 'regenerated_by_user_id' => Auth::id()]);
            }

            // Refresh payPeriod to ensure we have the latest data
            $payPeriod->refresh();
            
            // Set execution time limit for long-running process
            set_time_limit(300); // 5 minutes
                
            // Call existing generator, pass departmentId array
            // Use Carbon instances for date calculations
            try {
                Log::info('Starting payroll generation in controller', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'pay_period_id' => $payPeriod->id
                ]);
                
                $this->payrollService->generatePayslipsForPeriod(
                    Carbon::parse($startDate)->startOfDay(), 
                    Carbon::parse($endDate)->endOfDay(), 
                    $payScheduleFilter, 
                    $departmentIds
                );

                // Refresh pay period to get latest data
                $payPeriod->refresh();
                
                // Update status after successful generation
                $payPeriod->update(['status' => 'unpaid']);
                
                Log::info('Payroll generation completed in controller', [
                    'pay_period_id' => $payPeriod->id,
                    'payslips_count' => $payPeriod->payslips()->count()
                ]);

                // Ensure department_ids are passed correctly as array for filtering
                $redirectParams = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
                
                // Add department_ids only if they were provided and not empty
                if (!empty($departmentIds) && is_array($departmentIds)) {
                    $redirectParams['department_ids'] = $departmentIds;
                }
                
                Log::info('Payroll generated successfully, redirecting', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'department_ids' => $departmentIds,
                    'payslips_count' => $payPeriod->payslips()->count()
                ]);
                
                // Handle AJAX requests differently
                if ($request->ajax() || $request->has('ajax')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payroll generated for selected period.',
                        'redirect_url' => route('payroll.index', $redirectParams),
                        'payslips_count' => $payPeriod->payslips()->count()
                    ]);
                }
                
                return redirect()->route('payroll.index', $redirectParams)->with('success', 'Payroll generated for selected period.');
            } catch (\Exception $serviceException) {
                Log::error('Error in payroll service during generation: ' . $serviceException->getMessage(), [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'pay_period_id' => $payPeriod->id,
                    'trace' => $serviceException->getTraceAsString()
                ]);
                
                // Rollback pay period status if generation failed
                if ($payPeriod->status === 'processing') {
                    $payPeriod->update(['status' => 'draft']);
                }
                
                // Handle AJAX requests
                if ($request->ajax() || $request->has('ajax')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate payroll: ' . $serviceException->getMessage()
                    ], 500);
                }
                
                throw $serviceException; // Re-throw to be caught by outer catch
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->ajax() || $request->has('ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->errors()['start_date'] ?? []) . ' ' . implode(', ', $e->errors()['end_date'] ?? []),
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error generating payroll for range: ' . $e->getMessage(), [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Handle AJAX requests
            if ($request->ajax() || $request->has('ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate payroll: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('payroll.index')->with('error', 'Failed to generate payroll: ' . $e->getMessage());
        }
    }

    // Mark pay period as completed (done payment)
    public function completePayPeriod(PayPeriod $payPeriod)
    {
        if ($payPeriod->status !== 'paid') {
            $payPeriod->update(['status' => 'paid', 'marked_paid_by_user_id' => Auth::id()]);
        }

        return redirect()->route('payroll.index', ['start_date' => $payPeriod->start_date, 'end_date' => $payPeriod->end_date])->with('success', 'Pay period marked as paid.');
    }

    // Mark pay period as closed
    public function closePayPeriod(PayPeriod $payPeriod)
    {
        if ($payPeriod->status !== 'closed') {
            $payPeriod->update(['status' => 'closed']);
        }

        return redirect()->route('payroll.index', ['start_date' => $payPeriod->start_date, 'end_date' => $payPeriod->end_date])->with('success', 'Pay period marked as closed and final.');
    }

    public function showPayslip(User $employee, PayPeriod $payPeriod)
    {
        $payslip = Payslip::where('user_id', $employee->id)
            ->where('pay_period_id', $payPeriod->id)
            ->firstOrFail();

        return view('payroll.payslip', compact('payslip', 'employee', 'payPeriod'));
    }

    /**
     * Update Other Deduction for a payslip.
     */
    public function updateOtherDeduction(Request $request, Payslip $payslip)
    {
        $request->validate([
            'other_deductions' => 'required|numeric|min:0',
        ]);
        $user = Auth::user();
        // Only admin/hr can do this.
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }
        $payslip->other_deductions = $request->input('other_deductions');
        $payslip->save();
        return redirect()->back()->with('success', 'Other Deduction updated successfully.');
    }

    public function updateDeductions(Request $request, Payslip $payslip)
    {
        $request->validate([
            'deductions' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $payslip->deductions = $request->input('deductions');
        // Recalculate net pay
        $grossPay = $payslip->gross_pay; // Use the gross_pay already calculated by PayrollService
        $payslip->net_pay = $grossPay - $payslip->deductions;

        $payslip->save();

        return back()->with('success', 'Deductions updated successfully.');
    }

    public function downloadPdf(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_ids' => 'nullable|array', // Add validation for department_ids
            'department_ids.*' => 'exists:departments,id', // Validate each department ID
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $departmentIds = $request->input('department_ids'); // Get department IDs from request

        $payPeriod = PayPeriod::where('start_date', $start)->where('end_date', $end)->first();

        if (!$payPeriod) {
            return redirect()->back()->with('error', 'No payroll period found for the selected dates.');
        }

        $payslipQuery = Payslip::with(['user.department', 'payPeriod']) // Eager load department
            ->where('pay_period_id', $payPeriod->id);
        
        if (!empty($departmentIds)) {
            $payslipQuery->whereHas('user', function ($query) use ($departmentIds) {
                $query->whereIn('department_id', $departmentIds);
            });
        }

        $payrolls = $payslipQuery->get();

        // Group payslips by department name
        $groupedPayrolls = $payrolls->groupBy('user.department.name');

        $data = [
            'payPeriod' => $payPeriod,
            'payrolls' => $payrolls,
            'groupedPayrolls' => $groupedPayrolls, // Pass grouped data to the view
            'payScheduleFilter' => null, // Pass to view for potential display
        ];

        // Render both views to HTML
        // $payrollHtml = view('payroll.payslips_pdf', $data)->render();
        // $signaturesHtml = view('payroll.payslips_signatures_pdf', $data)->render();

        // // No explicit page break needed here; will merge PDFs later
        // $combinedHtml = $payrollHtml . $signaturesHtml;

        $combinedHtml = '';
        foreach ($groupedPayrolls as $departmentName => $departmentPayslips) {
            $departmentData = [
                'payPeriod' => $payPeriod,
                'payrolls' => $departmentPayslips,
                'groupedPayrolls' => collect([$departmentName => $departmentPayslips]), // Pass single department for rendering
                'departmentName' => $departmentName,
                'payScheduleFilter' => null,
            ];
            $combinedHtml .= view('payroll.payslips_pdf', $departmentData)->render();
            $combinedHtml .= view('payroll.payslips_signatures_pdf', $departmentData)->render();
        }

        $pdf = Pdf::loadHtml($combinedHtml)->setPaper('a4', 'landscape');
        
        return $pdf->download('payroll_report_' . $start . '_' . $end . '.pdf');
    }

    public function updateGlobalOvertimeMultiplier(Request $request)
    {
        $request->validate([
            'overtime_multiplier' => 'required|numeric|min:0.1',
        ]);

        // Ensure only HR/Admin can update the multiplier
        if (!Auth::user()->isHRManager()) {
            abort(403, 'Unauthorized access.');
        }

        $newMultiplier = $request->input('overtime_multiplier');

        // Update all employees' overtime_multiplier
        User::where('role', 'employee')->update(['overtime_multiplier' => $newMultiplier]);

        return redirect()->back()->with('success', 'Global overtime multiplier updated successfully for all employees.');
    }

    public function indexHistory(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view payroll history.');
        }

        try {
            $type = $request->query('type', 'all'); // Default to 'all'

            $payPeriodsQuery = PayPeriod::with(['generatedBy', 'regeneratedBy', 'markedPaidBy', 'payslips']);

            if ($type === 'semiMonthly') {
                $payPeriodsQuery->where('pay_period_type', 'semi-monthly');
            } elseif ($type === 'monthly') {
                $payPeriodsQuery->where('pay_period_type', 'monthly');
            }

            $payPeriods = $payPeriodsQuery->orderByDesc('end_date')->get();

            return view('payroll.history.index', compact('payPeriods'));
        } catch (\Exception $e) {
            Log::error('Error loading payroll history: ' . $e->getMessage(), [
                'type' => $request->query('type'),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('payroll.index')->with('error', 'Failed to load payroll history: ' . $e->getMessage());
        }
    }

    public function showPayrollDetails(PayPeriod $payPeriod)
    {
        $payPeriod->load('generatedBy', 'regeneratedBy', 'markedPaidBy', 'payslips.user');
        return view('payroll.payroll_details', compact('payPeriod'));
    }
}