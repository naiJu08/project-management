<?php

namespace App\Http\Livewire\Ticket;

use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketDependency;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Collection;

class EnhancedTicketDetail extends Component
{
    public Ticket $ticket;
    public string $activeTab = 'details';
    public bool $showApprovalForm = false;
    public bool $showDependencyForm = false;
    public string $approvalComment = '';
    public string $dependencyType = 'blocks';
    public ?int $dependencyTicketId = null;

    protected $listeners = ['ticketUpdated' => 'refreshTicket'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->ticket->load([
            'approvals.approver',
            'dependencies.dependsOnTicket',
            'dependentTickets.ticket',
            'childTickets',
            'parentTicket',
            'comments.user',
            'hours.user',
            'owner',
            'responsible'
        ]);
    }

    public function selectTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function approveTicket(): void
    {
        $approval = $this->ticket->approvals()
            ->where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($approval) {
            $approval->approve($this->approvalComment);
            $this->approvalComment = '';
            $this->showApprovalForm = false;
            $this->ticket->refresh();
            $this->notify('success', 'Ticket approved successfully');
        }
    }

    public function rejectTicket(): void
    {
        $approval = $this->ticket->approvals()
            ->where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($approval) {
            $approval->reject($this->approvalComment);
            $this->approvalComment = '';
            $this->showApprovalForm = false;
            $this->ticket->refresh();
            $this->notify('warning', 'Ticket rejected');
        }
    }

    public function approveWithComments(): void
    {
        $approval = $this->ticket->approvals()
            ->where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($approval && $this->approvalComment) {
            $approval->approveWithComments($this->approvalComment);
            $this->approvalComment = '';
            $this->showApprovalForm = false;
            $this->ticket->refresh();
            $this->notify('success', 'Ticket approved with comments');
        }
    }

    public function addDependency(): void
    {
        if (!$this->dependencyTicketId) {
            $this->notify('error', 'Please select a ticket');
            return;
        }

        TicketDependency::create([
            'ticket_id' => $this->ticket->id,
            'depends_on_ticket_id' => $this->dependencyTicketId,
            'type' => $this->dependencyType
        ]);

        $this->dependencyTicketId = null;
        $this->dependencyType = 'blocks';
        $this->showDependencyForm = false;
        $this->ticket->refresh();
        $this->notify('success', 'Dependency added');
    }

    public function removeDependency(int $dependencyId): void
    {
        TicketDependency::find($dependencyId)?->delete();
        $this->ticket->refresh();
        $this->notify('success', 'Dependency removed');
    }

    public function toggleBlocked(): void
    {
        $this->ticket->update(['is_blocked' => !$this->ticket->is_blocked]);
        $this->notify('success', $this->ticket->is_blocked ? 'Ticket blocked' : 'Ticket unblocked');
    }

    public function updateSLAStatus(): void
    {
        $this->ticket->updateSLAStatus();
        $this->ticket->refresh();
        $this->notify('success', 'SLA status updated');
    }

    public function getProgressPercentageProperty(): float
    {
        return $this->ticket->getProgressPercentage();
    }

    public function getTotalLoggedHoursProperty(): float
    {
        return $this->ticket->getTotalLoggedHours();
    }

    public function getRemainingHoursProperty(): ?float
    {
        return $this->ticket->getRemainingHours();
    }

    public function getBlockingReasonsProperty(): array
    {
        return $this->ticket->getBlockingReasons();
    }

    public function getPendingApprovalsProperty(): int
    {
        return $this->ticket->getPendingApprovals();
    }

    public function getAllApprovalsCompletedProperty(): bool
    {
        return $this->ticket->getAllApprovalsCompleted();
    }

    public function getChildTicketsProperty(): Collection
    {
        return $this->ticket->childTickets()->get();
    }

    public function getBlockedByTicketsProperty(): Collection
    {
        return $this->ticket->dependencies()
            ->where('type', 'blocked_by')
            ->with('dependsOnTicket')
            ->get()
            ->pluck('dependsOnTicket');
    }

    public function getBlocksTicketsProperty(): Collection
    {
        return $this->ticket->dependencies()
            ->where('type', 'blocks')
            ->with('dependsOnTicket')
            ->get()
            ->pluck('dependsOnTicket');
    }

    public function refreshTicket(): void
    {
        $this->ticket->refresh();
    }

    public function notify(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('notify', [
            'type' => $type,
            'message' => $message
        ]);
    }

    public function render()
    {
        return view('livewire.ticket.enhanced-ticket-detail', [
            'progressPercentage' => $this->progressPercentage,
            'totalLoggedHours' => $this->totalLoggedHours,
            'remainingHours' => $this->remainingHours,
            'blockingReasons' => $this->blockingReasons,
            'pendingApprovals' => $this->pendingApprovals,
            'allApprovalsCompleted' => $this->allApprovalsCompleted,
            'childTickets' => $this->childTickets,
            'blockedByTickets' => $this->blockedByTickets,
            'blocksTickets' => $this->blocksTickets,
        ]);
    }
}
