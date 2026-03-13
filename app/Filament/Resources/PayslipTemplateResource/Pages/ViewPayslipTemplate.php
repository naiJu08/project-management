<?php

namespace App\Filament\Resources\PayslipTemplateResource\Pages;

use App\Filament\Resources\PayslipTemplateResource;
use App\Services\TemplateRenderer;
use Filament\Resources\Pages\ViewRecord;

class ViewPayslipTemplate extends ViewRecord
{
    protected static string $resource = PayslipTemplateResource::class;

    public function getRenderedPreview(): string
    {
        $record = $this->record;
        $sample = [
            'user' => ['name' => 'John Doe', 'employee_code' => 'EMP-001'],
            'period_start' => now()->subMonth()->startOfMonth()->toDateString(),
            'period_end' => now()->subMonth()->endOfMonth()->toDateString(),
            'basic' => 50000,
            'gross' => 65000,
            'net' => 58000,
        ];
        return TemplateRenderer::render($record->html, $sample);
    }
}
