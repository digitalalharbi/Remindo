<?php

return [
    /*
    | AI provider for document extraction and natural-language parsing.
    | The layer is provider-agnostic: swap this to change providers without
    | touching business logic. Defaults to the replaceable "mock" adapter so
    | the feature works end-to-end without external credentials.
    |
    | Supported: mock | openai | anthropic
    */
    'provider' => env('AI_PROVIDER', 'mock'),

    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL'),

    // Reliability controls applied around every provider call.
    'timeout' => (int) env('AI_TIMEOUT', 30),
    'max_retries' => (int) env('AI_MAX_RETRIES', 2),

    // Usage / cost guardrails. Per-organization monthly cap is the *minimum* of
    // this value and the organization plan's ai_operations_limit.
    'monthly_token_limit' => (int) env('AI_MONTHLY_TOKEN_LIMIT', 200000),

    // Privacy: customer data is never used to train models, and documents are
    // only sent to the provider when extraction is explicitly requested.
    'privacy' => [
        'use_customer_data_for_training' => false,
        'retain_provider_copies' => false,
    ],
];
