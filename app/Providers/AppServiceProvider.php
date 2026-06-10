<?php

namespace App\Providers;

use App\Models\Prospect;
use App\Models\SingleSupportConflict;
use App\Policies\ProspectPolicy;
use App\Services\Ai\AiClientInterface;
use App\Services\Ai\GeminiClient;
use App\Services\Ai\MockAiClient;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AiClientInterface::class, function () {
            $provider = config('services.ai.provider', 'mock');

            if ($provider === 'gemini' && config('services.gemini.api_key')) {
                return new GeminiClient();
            }

            return new MockAiClient();
        });
    }

    public function boot(): void
    {
        Gate::policy(Prospect::class, ProspectPolicy::class);

        View::composer('layouts.app', function ($view) {
            $openConflicts = auth()->check()
                ? SingleSupportConflict::where('status', 'OPEN')->count()
                : 0;
            $view->with('openConflicts', $openConflicts);
        });
    }
}
