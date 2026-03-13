<?php

return [
    'provider' => env('AI_PROVIDER', 'huggingface'), // huggingface | local

    // Hugging Face settings
    'huggingface' => [
        'api_key' => env('HUGGINGFACE_API_KEY'),
        'timeout' => env('HUGGINGFACE_TIMEOUT', 60),
        'language_detection_model' => env('AI_HF_LANG_DETECT_MODEL', 'papluca/xlm-roberta-base-language-detection'),
        'task_generation_model' => env('AI_HF_TASK_GEN_MODEL', 'mistralai/Mixtral-8x7B-Instruct'),
    ],

    // Local (python script) settings
    'local' => [
        'provider' => env('AI_LOCAL_PROVIDER', 'ollama'), // ollama | lmstudio | localai | custom
        'endpoint' => env('AI_LOCAL_ENDPOINT', 'http://localhost:11434'),
        'model' => env('AI_LOCAL_MODEL', 'llama2'),
        'timeout' => env('AI_LOCAL_TIMEOUT', 60),
        'script_path' => env('AI_LOCAL_SCRIPT', base_path('scripts/ai_task_gen.py')),
        'python_bin' => env('AI_LOCAL_PYTHON', 'python3'),
    ],

    // Assistant configuration
    'assistant' => [
        'use_local' => env('AI_USE_LOCAL', true), // Use local AI instead of OpenAI
        'sections' => [
            'management' => [
                'name' => 'Management',
                'description' => 'Projects, tickets, tasks, and team management',
                'icon' => 'heroicon-o-briefcase',
            ],
            'hr' => [
                'name' => 'Human Resources',
                'description' => 'Employees, attendance, leaves, and payroll',
                'icon' => 'heroicon-o-users',
            ],
            'referential' => [
                'name' => 'Reference Data',
                'description' => 'Departments, positions, and system settings',
                'icon' => 'heroicon-o-database',
            ],
        ],
    ],
];
