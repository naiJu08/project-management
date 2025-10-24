<?php

namespace App\Services\AI;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\PayrollRecord;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HrActionHandler
{
    /**
     * Check in attendance
     */
    public function checkIn(array $parameters): array
    {
        $user = Auth::user();
        $today = today();

        $existing = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return [
                'success' => false,
                'message' => 'You have already checked in today.',
            ];
        }

        $record = AttendanceRecord::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['check_in' => now(), 'status' => 'present']
        );

        return [
            'success' => true,
            'message' => 'Checked in successfully at ' . now()->format('h:i A'),
            'data' => $record,
        ];
    }

    /**
     * Check out attendance
     */
    public function checkOut(array $parameters): array
    {
        $user = Auth::user();
        $today = today();

        $record = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$record || !$record->check_in) {
            return [
                'success' => false,
                'message' => 'You need to check in first.',
            ];
        }

        if ($record->check_out) {
            return [
                'success' => false,
                'message' => 'You have already checked out today.',
            ];
        }

        $record->check_out = now();
        $record->calculateWorkHours();
        $record->save();

        return [
            'success' => true,
            'message' => 'Checked out successfully at ' . now()->format('h:i A') . '. Total work hours: ' . number_format($record->work_hours, 1) . 'h',
            'data' => $record,
        ];
    }

    /**
     * Request leave
     */
    public function requestLeave(array $parameters): array
    {
        $required = ['leave_type_id', 'start_date', 'end_date'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $parameters['leave_type_id'],
            'start_date' => $parameters['start_date'],
            'end_date' => $parameters['end_date'],
            'reason' => $parameters['reason'] ?? null,
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Leave request submitted successfully and is pending approval.',
            'data' => $leaveRequest,
        ];
    }

    /**
     * Approve leave request
     */
    public function approveLeave(array $parameters): array
    {
        $required = ['leave_request_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $leaveRequest = LeaveRequest::find($parameters['leave_request_id']);

        if (!$leaveRequest) {
            return [
                'success' => false,
                'message' => 'Leave request not found.',
            ];
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => "Leave request for {$leaveRequest->user->name} has been approved.",
            'data' => $leaveRequest,
        ];
    }

    /**
     * Generate payslip
     */
    public function generatePayslip(array $parameters): array
    {
        $required = ['user_id', 'period_start', 'period_end', 'basic'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $payroll = PayrollRecord::create([
            'user_id' => $parameters['user_id'],
            'period_start' => $parameters['period_start'],
            'period_end' => $parameters['period_end'],
            'basic' => $parameters['basic'],
            'allowances' => $parameters['allowances'] ?? [],
            'deductions' => $parameters['deductions'] ?? [],
            'gross' => $parameters['gross'] ?? $parameters['basic'],
            'net' => $parameters['net'] ?? $parameters['basic'],
            'payslip_template_id' => $parameters['template_id'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Payslip generated successfully.',
            'data' => $payroll,
        ];
    }

    /**
     * Generate certificate
     */
    public function generateCertificate(array $parameters): array
    {
        $required = ['user_id', 'certificate_template_id', 'type'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $certificate = Certificate::create([
            'user_id' => $parameters['user_id'],
            'certificate_template_id' => $parameters['certificate_template_id'],
            'type' => $parameters['type'],
            'issued_on' => $parameters['issued_on'] ?? today(),
            'issued_by' => $parameters['issued_by'] ?? Auth::user()->name,
            'data' => $parameters['data'] ?? [],
        ]);

        return [
            'success' => true,
            'message' => 'Certificate generated successfully.',
            'data' => $certificate,
        ];
    }

    /**
     * Get attendance summary
     */
    public function getAttendanceSummary(array $parameters): array
    {
        $userId = $parameters['user_id'] ?? Auth::id();
        $month = $parameters['month'] ?? now()->month;
        $year = $parameters['year'] ?? now()->year;

        $records = AttendanceRecord::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $stats = [
            'total_days' => $records->count(),
            'present' => $records->whereIn('status', ['present', 'late'])->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'total_hours' => $records->sum('work_hours'),
        ];

        return [
            'success' => true,
            'message' => "Attendance summary for {$month}/{$year}",
            'data' => $stats,
        ];
    }

    protected function validateParameters(array $parameters, array $required): bool
    {
        foreach ($required as $param) {
            if (!isset($parameters[$param]) || empty($parameters[$param])) {
                return false;
            }
        }
        return true;
    }

    protected function missingParametersResponse(array $required): array
    {
        return [
            'success' => false,
            'message' => 'Missing required parameters: ' . implode(', ', $required),
            'requires_input' => true,
        ];
    }
}
