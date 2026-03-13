<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiTaskGenerator
{
    public function detectLanguage(string $text): ?string
    {
        $provider = config('ai.provider', 'huggingface');
        try {
            if ($provider === 'huggingface') {
                $model = config('ai.huggingface.language_detection_model');
                $apiKey = config('ai.huggingface.api_key');
                if (empty($apiKey)) {
                    Log::warning('HuggingFace API key missing, skipping language detection');
                    return null;
                }
                $resp = Http::withToken($apiKey)
                    ->timeout((int)config('ai.huggingface.timeout', 60))
                    ->post("https://api-inference.huggingface.co/models/{$model}", [
                        'inputs' => $text,
                    ]);
                if ($resp->failed()) {
                    Log::warning('HF language detection failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                    return null;
                }
                $data = $resp->json();
                // Response is array of arrays; take top label
                $first = is_array($data) ? Arr::first($data) : null;
                $label = is_array($first) ? Arr::get($first, 'label') : null;
                if ($label) {
                    // Labels like "en" or "English"
                    return $label;
                }
                return null;
            }

            // Local python script for detection
            $script = config('ai.local.script_path');
            $python = config('ai.local.python_bin', 'python3');
            $timeout = (int) config('ai.local.timeout', 120);
            $payload = [
                'action' => 'detect_language',
                'text' => $text,
            ];
            $process = proc_open(
                "$python $script",
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );
            if (!is_resource($process)) {
                return null;
            }
            fwrite($pipes[0], json_encode($payload));
            fclose($pipes[0]);
            stream_set_timeout($pipes[1], $timeout);
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($process);
            $json = json_decode($out, true);
            return $json['language'] ?? null;
        } catch (Exception $e) {
            Log::error('Language detection error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a hierarchical task plan from project name and description.
     * Returns an array of tasks: [ [ 'title' => string, 'description' => string|null, 'priority' => 'low|medium|high', 'estimate_hours' => int|null, 'subtasks' => [ ... ] ] ]
     */
    public function generatePlan(string $projectName, string $projectDescription, ?string $language = null): array
    {
        $provider = config('ai.provider', 'huggingface');
        $language = $language ?: $this->detectLanguage($projectDescription) ?: 'en';
        try {
            if ($provider === 'huggingface') {
                $model = config('ai.huggingface.task_generation_model');
                $apiKey = config('ai.huggingface.api_key');
                if (empty($apiKey)) {
                    Log::warning('HuggingFace API key missing, cannot generate plan');
                    return [];
                }
                $prompt = $this->buildPrompt($projectName, $projectDescription, $language);
                $resp = Http::withToken($apiKey)
                    ->timeout((int)config('ai.huggingface.timeout', 60))
                    ->post("https://api-inference.huggingface.co/models/{$model}", [
                        'inputs' => $prompt,
                        'parameters' => [
                            'max_new_tokens' => 800,
                            'temperature' => 0.2,
                            'return_full_text' => false,
                        ],
                    ]);
                if ($resp->failed()) {
                    Log::warning('HF task generation failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                    return [];
                }
                $data = $resp->json();
                // Some models return array of objects with 'generated_text'
                $text = '';
                if (is_array($data)) {
                    $first = Arr::first($data);
                    $text = is_array($first) ? ($first['generated_text'] ?? '') : '';
                } elseif (is_string($data)) {
                    $text = $data;
                }
                $plan = $this->extractJson($text);
                return is_array($plan) ? $plan : [];
            }

            // Local python
            $script = config('ai.local.script_path');
            $python = config('ai.local.python_bin', 'python3');
            $timeout = (int) config('ai.local.timeout', 120);
            $payload = [
                'action' => 'generate_plan',
                'project_name' => $projectName,
                'description' => $projectDescription,
                'language' => $language,
            ];
            $process = proc_open(
                "$python $script",
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );
            if (!is_resource($process)) {
                return [];
            }
            fwrite($pipes[0], json_encode($payload));
            fclose($pipes[0]);
            stream_set_timeout($pipes[1], $timeout);
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($process);
            $json = json_decode($out, true);
            return is_array($json) ? ($json['tasks'] ?? []) : [];
        } catch (Exception $e) {
            Log::error('Task generation error: ' . $e->getMessage());
            return [];
        }
    }

    protected function buildPrompt(string $name, string $desc, string $lang): string
    {
        return <<<PROMPT
You are an expert project manager AI. Analyze the user's project description and generate a precise, comprehensive hierarchical task breakdown in the user's language ($lang). Identify domain, requirements, constraints, and produce tasks and subtasks without relying on fixed keywords. Think step-by-step and deeply.

Return ONLY valid JSON with this schema:
[
  {
    "title": string,                     // concise task title in $lang
    "description": string|null,          // brief details in $lang
    "priority": "low"|"medium"|"high",
    "estimate_hours": number|null,
    "subtasks": [ /* recursive same schema */ ]
  }
]

Project Name: "$name"
Project Description:
"""
$desc
"""

Output:
PROMPT;
    }

    protected function extractJson(string $text): array|string|null
    {
        // Try to extract the first JSON array from the text
        $start = strpos($text, '[');
        $end = strrpos($text, ']');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $json = substr($text, $start, $end - $start + 1);
        $data = json_decode($json, true);
        return $data;
    }
}
