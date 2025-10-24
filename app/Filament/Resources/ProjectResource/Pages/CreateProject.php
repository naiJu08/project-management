<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Jobs\GenerateProjectTasks;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Schema;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        // Use raw state to include non-dehydrated controls
        $data = $this->form->getRawState();
        $auto = $data['ai_autogenerate'] ?? false;
        $context = $data['ai_context'] ?? null;
        if ($auto) {
            // Mark project AI status and notify
            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $this->record->ai_generation_status = 'running';
                $this->record->ai_last_run_at = now();
                $this->record->ai_last_message = null;
                $this->record->save();
            }
            Filament::notify('success', __('AI task generation started in background'));

            // Queue background job to generate tasks
            GenerateProjectTasks::dispatch($this->record->id, null, $context);
        }
    }
}
