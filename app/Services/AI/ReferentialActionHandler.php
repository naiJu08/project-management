<?php

namespace App\Services\AI;

use App\Models\Department;
use App\Models\Position;
use App\Models\LeaveType;
use App\Models\TicketType;
use App\Models\TicketPriority;
use App\Models\TicketStatus;

class ReferentialActionHandler
{
    /**
     * Create department
     */
    public function createDepartment(array $parameters): array
    {
        $required = ['name'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $department = Department::create([
            'name' => $parameters['name'],
            'description' => $parameters['description'] ?? null,
            'parent_id' => $parameters['parent_id'] ?? null,
            'manager_id' => $parameters['manager_id'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => "Department '{$department->name}' has been created successfully!",
            'data' => $department,
        ];
    }

    /**
     * Create position
     */
    public function createPosition(array $parameters): array
    {
        $required = ['name'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $position = Position::create([
            'name' => $parameters['name'],
            'description' => $parameters['description'] ?? null,
            'department_id' => $parameters['department_id'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => "Position '{$position->name}' has been created successfully!",
            'data' => $position,
        ];
    }

    /**
     * Create leave type
     */
    public function createLeaveType(array $parameters): array
    {
        $required = ['name'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $leaveType = LeaveType::create([
            'name' => $parameters['name'],
            'description' => $parameters['description'] ?? null,
            'days_per_year' => $parameters['days_per_year'] ?? 0,
            'is_paid' => $parameters['is_paid'] ?? true,
        ]);

        return [
            'success' => true,
            'message' => "Leave type '{$leaveType->name}' has been created successfully!",
            'data' => $leaveType,
        ];
    }

    /**
     * List departments
     */
    public function listDepartments(array $parameters): array
    {
        $departments = Department::with('manager')->get();

        return [
            'success' => true,
            'message' => "Found {$departments->count()} departments.",
            'data' => $departments,
        ];
    }

    /**
     * List positions
     */
    public function listPositions(array $parameters): array
    {
        $query = Position::query();

        if (isset($parameters['department_id'])) {
            $query->where('department_id', $parameters['department_id']);
        }

        $positions = $query->get();

        return [
            'success' => true,
            'message' => "Found {$positions->count()} positions.",
            'data' => $positions,
        ];
    }

    /**
     * List leave types
     */
    public function listLeaveTypes(array $parameters): array
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return [
            'success' => true,
            'message' => "Found {$leaveTypes->count()} leave types.",
            'data' => $leaveTypes,
        ];
    }

    /**
     * Create ticket type
     */
    public function createTicketType(array $parameters): array
    {
        $required = ['name'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $ticketType = TicketType::create([
            'name' => $parameters['name'],
            'icon' => $parameters['icon'] ?? 'heroicon-o-ticket',
            'color' => $parameters['color'] ?? 'primary',
        ]);

        return [
            'success' => true,
            'message' => "Ticket type '{$ticketType->name}' has been created successfully!",
            'data' => $ticketType,
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
