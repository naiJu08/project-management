<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Jobs\GenerateProjectTasksWithCohere;
use App\Models\User;
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
        $clientUserIds = collect($data['client_user_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($clientUserIds->isNotEmpty()) {
            $validClientIds = User::query()
                ->whereIn('id', $clientUserIds->all())
                ->whereHas('roles', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['client']))
                ->pluck('id')
                ->all();

            if (!empty($validClientIds)) {
                $this->record->users()->syncWithoutDetaching(
                    collect($validClientIds)->mapWithKeys(fn ($id) => [
                        $id => ['role' => 'customer'],
                    ])->toArray()
                );
            }
        }

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
            Filament::notify('success', __('AI task generation started using Cohere AI'));

            // Queue background job to generate tasks using Cohere AI
            GenerateProjectTasksWithCohere::dispatch($this->record->id, null, $context);
        }
    }
}
