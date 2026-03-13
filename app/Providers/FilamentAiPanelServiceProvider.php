<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentAiPanelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::serving(function () {
            // Inject the AI assistant panel into all Filament pages (floating button at bottom-right).
            Filament::registerRenderHook('body.end', function () {
                return view('partials.filament.ai-assistant-panel');
            });

            // Place the dark mode toggle in the header. Register on both v3 ('panels::') and v2 ('filament::') hooks.
            // Filament v3
            Filament::registerRenderHook('panels::topbar.start', fn () => view('partials.filament.dark-mode-toggle-topbar'));
            Filament::registerRenderHook('panels::topbar.end', fn () => view('partials.filament.dark-mode-toggle-topbar'));
            Filament::registerRenderHook('panels::user-menu.start', fn () => view('partials.filament.dark-mode-toggle-topbar'));
            // Filament v2
            Filament::registerRenderHook('filament::topbar.start', fn () => view('partials.filament.dark-mode-toggle-topbar'));
            Filament::registerRenderHook('filament::topbar.end', fn () => view('partials.filament.dark-mode-toggle-topbar'));
            Filament::registerRenderHook('filament::user-menu.start', fn () => view('partials.filament.dark-mode-toggle-topbar'));
        });
    }
}
