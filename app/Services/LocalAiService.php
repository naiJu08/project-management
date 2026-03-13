<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Local AI Service for self-hosted models
 * Supports: Ollama, LM Studio, LocalAI, custom endpoints
 */
class LocalAiService
{
    protected $endpoint;
    protected $model;
    protected $provider;
    protected $timeout;

    public function __construct()
    {
        $this->provider = config('ai.local.provider', 'ollama'); // ollama, lmstudio, localai, custom
        $this->endpoint = config('ai.local.endpoint', 'http://localhost:11434');
        $this->model = config('ai.local.model', 'llama2');
        $this->timeout = config('ai.local.timeout', 60);
    }

    /**
     * Generate completion from local AI model
     */
    public function generate(array $messages, array $options = []): string
    {
        try {
            switch ($this->provider) {
                case 'ollama':
                    return $this->generateOllama($messages, $options);
                case 'lmstudio':
                    return $this->generateLMStudio($messages, $options);
                case 'localai':
                    return $this->generateLocalAI($messages, $options);
                case 'custom':
                    return $this->generateCustom($messages, $options);
                default:
                    throw new \Exception("Unsupported provider: {$this->provider}");
            }
        } catch (\Exception $e) {
            Log::error('Local AI Error: ' . $e->getMessage(), [
                'provider' => $this->provider,
                'model' => $this->model,
                'messages' => $messages,
            ]);
            throw $e;
        }
    }

    /**
     * Ollama API format
     * Install: curl -fsSL https://ollama.com/install.sh | sh
     * Run: ollama run llama2
     */
    protected function generateOllama(array $messages, array $options): string
    {
        $prompt = $this->buildPromptFromMessages($messages);
        
        $response = Http::timeout($this->timeout)
            ->post("{$this->endpoint}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => array_merge([
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                    'top_k' => 40,
                ], $options),
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ollama API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['response'] ?? '';
    }

    /**
     * LM Studio API format (OpenAI-compatible)
     * Download: https://lmstudio.ai/
     */
    protected function generateLMStudio(array $messages, array $options): string
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->endpoint}/v1/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'stream' => false,
            ]);

        if (!$response->successful()) {
            throw new \Exception('LM Studio API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * LocalAI API format (OpenAI-compatible)
     * Install: docker run -p 8080:8080 localai/localai
     */
    protected function generateLocalAI(array $messages, array $options): string
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->endpoint}/v1/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 1000,
            ]);

        if (!$response->successful()) {
            throw new \Exception('LocalAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Custom endpoint (define your own format)
     */
    protected function generateCustom(array $messages, array $options): string
    {
        $response = Http::timeout($this->timeout)
            ->post($this->endpoint, [
                'messages' => $messages,
                'model' => $this->model,
                'options' => $options,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Custom AI API request failed: ' . $response->body());
        }

        // Adjust based on your custom API response format
        $data = $response->json();
        return $data['response'] ?? $data['content'] ?? '';
    }

    /**
     * Build single prompt from message array (for non-chat models)
     */
    protected function buildPromptFromMessages(array $messages): string
    {
        $prompt = '';
        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'];
            
            if ($role === 'system') {
                $prompt .= "System: {$content}\n\n";
            } elseif ($role === 'user') {
                $prompt .= "User: {$content}\n\n";
            } elseif ($role === 'assistant') {
                $prompt .= "Assistant: {$content}\n\n";
            }
        }
        $prompt .= "Assistant: ";
        return $prompt;
    }

    /**
     * Extract structured data from AI response
     */
    public function extractJson(string $response): ?array
    {
        // Try to find JSON in response
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        return null;
    }

    /**
     * Check if local AI is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->endpoint);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get available models from endpoint
     */
    public function getAvailableModels(): array
    {
        try {
            switch ($this->provider) {
                case 'ollama':
                    $response = Http::get("{$this->endpoint}/api/tags");
                    if ($response->successful()) {
                        $data = $response->json();
                        return array_map(fn($m) => $m['name'], $data['models'] ?? []);
                    }
                    break;
                case 'lmstudio':
                case 'localai':
                    $response = Http::get("{$this->endpoint}/v1/models");
                    if ($response->successful()) {
                        $data = $response->json();
                        return array_map(fn($m) => $m['id'], $data['data'] ?? []);
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch available models: ' . $e->getMessage());
        }
        return [];
    }
}
