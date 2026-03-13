<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private string $baseUrl;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.base_url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'llama2');
        $this->timeout = config('services.ollama.timeout', 300); // Increased to 5 minutes
    }

    /**
     * Detect language from text content
     */
    public function detectLanguage(string $text): string
    {
        try {
            $prompt = "Detect the primary language of the following text and respond with ONLY the language name (e.g., 'English', 'Spanish', 'French', etc.):\n\n{$text}";
            
            $response = $this->generate($prompt);
            return trim($response);
        } catch (\Exception $e) {
            Log::error('Ollama language detection failed: ' . $e->getMessage());
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
            $response = $this->generate($prompt);
            
            return $this->parseBacklogResponse($response);
        } catch (\Exception $e) {
            Log::error('Ollama backlog generation failed: ' . $e->getMessage());
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
     * Parse Ollama response into structured backlog array
     */
    private function parseBacklogResponse(string $response): array
    {
        // Extract JSON from response (handle markdown code blocks)
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception('Invalid JSON response from Ollama');
        }
        
        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $data = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decode error: ' . json_last_error_msg());
            Log::error('Response: ' . $response);
            throw new \Exception('Failed to parse Ollama response: ' . json_last_error_msg());
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
        
        $cleanedData = ['epics' => []];
        
        foreach ($data['epics'] as $epic) {
            $cleanedEpic = [
                'title' => $epic['title'] ?? 'Untitled Epic',
                'description' => $epic['description'] ?? '',
                'priority' => $this->validatePriority($epic['priority'] ?? 'Medium'),
                'features' => []
            ];
            
            if (isset($epic['features']) && is_array($epic['features'])) {
                foreach ($epic['features'] as $feature) {
                    $cleanedFeature = [
                        'title' => $feature['title'] ?? 'Untitled Feature',
                        'description' => $feature['description'] ?? '',
                        'priority' => $this->validatePriority($feature['priority'] ?? 'Medium'),
                        'user_stories' => []
                    ];
                    
                    if (isset($feature['user_stories']) && is_array($feature['user_stories'])) {
                        foreach ($feature['user_stories'] as $story) {
                            $cleanedStory = [
                                'title' => $story['title'] ?? 'Untitled User Story',
                                'description' => $story['description'] ?? '',
                                'priority' => $this->validatePriority($story['priority'] ?? 'Medium'),
                                'estimated_hours' => (int) ($story['estimated_hours'] ?? 8),
                                'acceptance_criteria' => $story['acceptance_criteria'] ?? []
                            ];
                            $cleanedFeature['user_stories'][] = $cleanedStory;
                        }
                    }
                    
                    $cleanedEpic['features'][] = $cleanedFeature;
                }
            }
            
            $cleanedData['epics'][] = $cleanedEpic;
        }
        
        return $cleanedData;
    }

    /**
     * Validate priority value
     */
    private function validatePriority(string $priority): string
    {
        $validPriorities = ['High', 'Medium', 'Low'];
        $priority = ucfirst(strtolower($priority));
        
        return in_array($priority, $validPriorities) ? $priority : 'Medium';
    }

    /**
     * Generate completion using Ollama API
     */
    private function generate(string $prompt): string
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ollama API request failed: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['response'])) {
            throw new \Exception('Invalid response from Ollama API');
        }

        return $data['response'];
    }

    /**
     * Check if Ollama service is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/tags");
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['models'] ?? [];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch Ollama models: ' . $e->getMessage());
            return [];
        }
    }
}
