<?php

return [
    'enabled' => (bool) env('OLLAMA_ENABLED', true),
    'url' => (string) env('OLLAMA_URL', 'http://localhost:11434'),
    'model' => (string) env('OLLAMA_MODEL', 'llama3.2'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
    'retries' => (int) env('OLLAMA_RETRIES', 1),
    'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.1),
    'max_context_rows' => (int) env('OLLAMA_MAX_CONTEXT_ROWS', 10),
];