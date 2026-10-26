<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shared Meta Model API Configuration
    |--------------------------------------------------------------------------
    | Core Hackathon Requirement:
    | A single shared Meta Model API key is used for every compatible hosted
    | Meta model (Muse Spark, Muse Wonder, Muse Image, Muse Voice Transcribe,
    | and SAM 3.1).
    |
    | Never assume that the shared key grants access to every model automatically.
    | Service layer validates model access, handles endpoint differences, and
    | returns descriptive configuration and credit notices.
    */
    'api_key' => env('META_MODEL_API_KEY', ''),
    'base_url' => rtrim(env('META_MODEL_API_BASE_URL', 'https://api.meta.ai/v1'), '/'),
    'timeout' => (int) env('META_MODEL_API_TIMEOUT', 45),
    'connect_timeout' => (int) env('META_MODEL_API_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Hosted Meta Model Identifiers
    |--------------------------------------------------------------------------
    | Individual model IDs for each capability, all sharing the centralized
    | client authentication infrastructure.
    */
    'models' => [
        'spark' => env('META_MUSE_SPARK_MODEL', 'muse-spark-1.3'),
        'wonder' => env('META_MUSE_WONDER_MODEL', 'muse-wonder-1.0'),
        'image' => env('META_MUSE_IMAGE_MODEL', 'muse-image-1.0'),
        'voice_transcribe' => env('META_VOICE_TRANSCRIBE_MODEL', 'muse-voice-transcribe-1.0'),
        'sam' => env('META_SAM_MODEL', 'sam-3.1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Open-Weight Inference Configuration (Independent Providers)
    |--------------------------------------------------------------------------
    | Muse Glimmer and Muse Spark Open must be configured through compatible
    | open-weight inference setups (vLLM, Ollama, HuggingFace Inference, etc.)
    | if they are not available through the hosted Meta Model API.
    |
    | Do NOT assume the shared hosted Meta key works for open-weight models.
    */
    'open_weight' => [
        'provider' => env('META_OPEN_WEIGHT_PROVIDER', ''),
        'glimmer' => [
            'endpoint' => env('META_GLIMMER_ENDPOINT', ''),
            'api_key' => env('META_GLIMMER_API_KEY', ''),
            'model' => env('META_GLIMMER_MODEL', 'meta-models/muse-glimmer-8b'),
            'timeout' => (int) env('META_GLIMMER_TIMEOUT', 60),
        ],
        'spark_open' => [
            'endpoint' => env('META_SPARK_OPEN_ENDPOINT', ''),
            'api_key' => env('META_SPARK_OPEN_API_KEY', ''),
            'model' => env('META_SPARK_OPEN_MODEL', 'meta-models/muse-spark-open-1026'),
            'timeout' => (int) env('META_SPARK_OPEN_TIMEOUT', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cultural Safety & Moderation Settings
    |--------------------------------------------------------------------------
    | AI must assist cultural preservation without replacing human authority.
    | All generated content is treated as untrusted draft proposal requiring
    | human guardian review and moderator approval.
    */
    'cultural_safety' => [
        'strict_no_hallucination' => true,
        'require_contributor_consent' => true,
        'watermark_ai_illustrations' => true,
        'disallow_auto_publishing' => true,
    ],
];
