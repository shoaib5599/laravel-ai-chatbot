<?php

namespace App\Providers;

use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Eloquent\ChatRepository;
use App\Services\ChatService;
use App\Services\Contracts\AIServiceInterface;
use App\Services\Contracts\ChatServiceInterface;
use App\Services\OllamaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(AIServiceInterface::class, OllamaService::class);
        $this->app->bind(ChatServiceInterface::class, ChatService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
