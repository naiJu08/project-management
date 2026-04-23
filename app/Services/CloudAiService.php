<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudAiService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->provider = config('services.cloud_ai.provider', 'deepseek'); // deepseek, groq, openai, cohere
        $this->apiKey = config('services.cloud_ai.api_key', '');
        $this->model = config('services.cloud_ai.model', '');
        
        // Log configuration for debugging
        Log::info('CloudAiService initialized', [
            'provider' => $this->provider,
            'api_key_set' => !empty($this->apiKey),
            'model' => $this->model,
        ]);
        
        // Set base URL based on provider
        $this->baseUrl = match($this->provider) {
            'deepseek' => 'https://api.deepseek.com/v1',
            'groq' => 'https://api.groq.com/openai/v1',
            'openai' => 'https://api.openai.com/v1',
            'cohere' => 'https://api.cohere.ai/v1',
            default => config('services.cloud_ai.base_url', 'https://api.deepseek.com/v1'),
        };
    }

    /**
     * Detect language from text content
     */
    public function detectLanguage(string $text): string
    {
        try {
            $prompt = "Detect the primary language of the following text and respond with ONLY the language name (e.g., 'English', 'Spanish', 'French', etc.):\n\n{$text}";
            
            $response = $this->generate($prompt, 50);
            return trim($response);
        } catch (\Exception $e) {
            Log::error('Cloud AI language detection failed: ' . $e->getMessage());
            return 'English'; // Default fallback
        }
    }

    /**
     * Extract project requirements and generate backlog structure from Wiki content
     */
    public function generateBacklogFromWiki(string $wikiContent, string $projectName): array
    {
        try {
            $prompt = $this->buildBacklogPrompt($wikiContent, $projectName);
            $response = $this->generate($prompt, 4000);
            
            return $this->parseBacklogResponse($response);
        } catch (\Exception $e) {
            Log::error('Cloud AI backlog generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build comprehensive prompt for backlog generation
     */
    private function buildBacklogPrompt(string $wikiContent, string $projectName): string
    {
        return <<<PROMPT
You are an expert software project analyst. Analyze the following project documentation and extract a comprehensive backlog structure.

PROJECT NAME: {$projectName}

DOCUMENTATION:
{$wikiContent}

Your task is to create a detailed backlog following this EXACT structure:
- **Epics**: High-level business objectives or major features (2-5 epics)
- **Features**: Specific capabilities under each epic (2-4 features per epic)
- **User Stories**: Detailed requirements following "As a [user], I want [feature] so that [benefit]" format (3-6 stories per feature)

IMPORTANT RULES:
1. Extract information ONLY from the provided documentation
2. Be specific and actionable
3. Prioritize based on importance and dependencies
4. Use clear, concise language
5. Follow Agile best practices
6. Ensure each user story is independent and testable

OUTPUT FORMAT (Use this EXACT JSON structure):
```json
{
  "epics": [
    {
      "title": "Epic Title",
      "description": "Detailed epic description",
      "priority": "High|Medium|Low",
      "features": [
        {
          "title": "Feature Title",
          "description": "Detailed feature description",
          "priority": "High|Medium|Low",
          "user_stories": [
            {
              "title": "User Story Title",
              "description": "As a [user], I want [feature] so that [benefit]",
              "priority": "High|Medium|Low",
              "estimated_hours": 8,
              "acceptance_criteria": [
                "Criterion 1",
                "Criterion 2",
                "Criterion 3"
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

Respond with ONLY valid JSON, no additional text or explanation.
PROMPT;
    }

    /**
     * Generate completion using Cloud AI API
     */
    private function generate(string $prompt, int $maxTokens = 2000): string
    {
        // Cohere uses different API format
        if ($this->provider === 'cohere') {
            return $this->generateCohere($prompt, $maxTokens);
        }

        // OpenAI-compatible format (DeepSeek, Groq, OpenAI)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(120)
        ->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert software project analyst specializing in Agile backlog creation.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Cloud AI API request failed: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response from Cloud AI API');
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Generate completion using Cohere API
     */
    private function generateCohere(string $prompt, int $maxTokens = 2000): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(120)
        ->post("{$this->baseUrl}/chat", [
            'model' => $this->model,
            'message' => $prompt,
            'preamble' => 'You are an expert software project analyst specializing in Agile backlog creation.',
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Cohere API request failed: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['text'])) {
            throw new \Exception('Invalid response from Cohere API');
        }

        return $data['text'];
    }

    /**
     * Parse AI response into structured backlog array
     */
    private function parseBacklogResponse(string $response): array
    {
        // Extract JSON from response (handle markdown code blocks)
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception('Invalid JSON response from Cloud AI');
        }
        
        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $data = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decode error: ' . json_last_error_msg());
            Log::error('Response: ' . $response);
            throw new \Exception('Failed to parse Cloud AI response: ' . json_last_error_msg());
        }
        
        return $this->validateAndCleanBacklog($data);
    }

    /**
     * Validate and clean backlog data
     */
    private function validateAndCleanBacklog(array $data): array
    {
        if (!isset($data['epics']) || !is_array($data['epics'])) {
            throw new \Exception('Invalid backlog structure: missing epics');
        }
        
        foreach ($data['epics'] as &$epic) {
            $epic['priority'] = $this->normalizePriority($epic['priority'] ?? 'Medium');
            
            if (isset($epic['features']) && is_array($epic['features'])) {
                foreach ($epic['features'] as &$feature) {
                    $feature['priority'] = $this->normalizePriority($feature['priority'] ?? 'Medium');
                    
                    if (isset($feature['user_stories']) && is_array($feature['user_stories'])) {
                        foreach ($feature['user_stories'] as &$story) {
                            $story['priority'] = $this->normalizePriority($story['priority'] ?? 'Medium');
                            $story['estimated_hours'] = $story['estimated_hours'] ?? 8;
                        }
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Normalize priority values
     */
    private function normalizePriority(string $priority): string
    {
        $validPriorities = ['High', 'Medium', 'Low'];
        return in_array($priority, $validPriorities) ? $priority : 'Medium';
    }

    /**
     * Check if Cloud AI service is available
     */
    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(5)
            ->get("{$this->baseUrl}/models");
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }
}
