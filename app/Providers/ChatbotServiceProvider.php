<?php

namespace App\Providers;

use App\Services\Chatbot\GroqClient;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GroqClient::class);
        $this->app->bind('chatbot.groq', GroqClient::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
