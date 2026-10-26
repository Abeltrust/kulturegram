# Meta Global AI Developer Hackathon — Kulturegram Setup & Integration Guide

## 1. Executive Summary & Architecture

**Kulturegram Living Heritage Capture** (`Preserve the stories. Protect the knowledge. Pass the heritage forward.`) empowers indigenous communities, elders, and cultural custodians to capture oral histories, endangered languages, sacred folktales, and authentic artifacts.

### Core Architectural Principle: Single Shared API Key

Per the Hackathon core requirement, a **single shared Meta Model API key** (`META_MODEL_API_KEY`) powers all compatible hosted Meta models:
- **Muse Voice Transcribe**: Authentic oral audio transcription with dialect safeguarding.
- **Muse Spark**: Structured cultural record synthesis with strict anti-hallucination discipline.
- **Muse Wonder**: Bounded preservation audit (gap detection, neutral elder questions, task checklist).
- **Muse Image**: Dignifying cultural illustrations explicitly labeled as AI-generated.
- **SAM 3.1**: Authentic artifact visual segmentation and boundary isolation.

Open-weight models (**Muse Glimmer** & **Muse Spark Open**) are configured via independent inference providers without assuming access through the shared key.

---

## 2. Configuration Setup

Add these keys to your local `.env` file (never commit secrets to version control):

### A. Shared Hosted Meta Model API (One Key)

```dotenv
# Central Meta Model API Credentials
META_MODEL_API_KEY=your_actual_meta_model_api_key_here
META_MODEL_API_BASE_URL=https://api.meta.ai/v1
META_MODEL_API_TIMEOUT=45
META_MODEL_API_CONNECT_TIMEOUT=10

# Individual Model Identifiers (all authenticated with the key above)
META_MUSE_SPARK_MODEL=muse-spark-1.3
META_MUSE_WONDER_MODEL=muse-wonder-1.0
META_MUSE_IMAGE_MODEL=muse-image-1.0
META_VOICE_TRANSCRIBE_MODEL=muse-voice-transcribe-1.0
META_SAM_MODEL=sam-3.1
```

### B. Open-Weight Inference Providers (Configured Independently)

If using a local engine (vLLM, Ollama) or hosted endpoint (Together AI, HuggingFace):

```dotenv
META_OPEN_WEIGHT_PROVIDER=vllm

# Muse Glimmer (Taxonomy & claim checking)
META_GLIMMER_ENDPOINT=http://localhost:8000/v1/chat/completions
META_GLIMMER_API_KEY=
META_GLIMMER_MODEL=meta-models/muse-glimmer-8b

# Muse Spark Open (Open-weight structured extraction)
META_SPARK_OPEN_ENDPOINT=http://localhost:8000/v1/chat/completions
META_SPARK_OPEN_API_KEY=
META_SPARK_OPEN_MODEL=meta-models/muse-spark-open-1026
```

---

## 3. Local Installation & Verification Commands

### Step 1: Run Migrations
Applies the Living Heritage database columns (`raw_story`, `audio_transcript`, `is_ai_assisted`, `ai_illustration`, `artifact_segmentation`, `wonder_preservation_plan`, `ai_metadata`) and expands `status` to allow `draft`:

```bash
php artisan migrate
```

### Step 2: Ensure Storage Symlink Exists
Links public storage for uploaded oral audio, segmented artifacts, and illustrations:

```bash
php artisan storage:link
```

### Step 3: Run the Automated Test Suite
All tests use Laravel's `Http::fake()` and fake file generators; **no live credentials or credits are required to run tests**:

```bash
# Run the Living Heritage feature tests
php artisan test --filter=LivingHeritageCaptureTest

# Run the MetaModelApiClient tests
php artisan test --filter=MetaModelApiClientTest

# Run all application tests (86 tests)
php artisan test
```

### Step 4: Run the Development Server

```bash
php artisan serve
```

Access the wizard at: `http://127.0.0.1:8000/cultural-hub/living-heritage/capture`  
Access the comparison lab at: `http://127.0.0.1:8000/cultural-hub/living-heritage/compare`

---

## 4. How to Verify Each Model Live When Credits Are Applied

When your Meta Model API key is credited, verify each capability:

| Model | Verification Workflow | Expected Live Behavior |
| :--- | :--- | :--- |
| **Muse Voice Transcribe** | Upload a 30s `.mp3` or `.wav` recording at Stage 2 and click **Transcribe Audio with AI**. | Generates text transcript, populates editable textarea, displays detected dialect/language. |
| **Muse Spark** | On Stage 3, click **Prepare Story with AI**. | Generates title, region, category, narrative summary, rituals, challenges, and neutral questions in under 2 seconds. |
| **Muse Wonder** | On Stage 4, click **Run Preservation Audit**. | Returns a structured preservation summary, identified gaps, and an actionable checklist. |
| **Muse Image** | On Stage 4, click **Generate Illustration**. | Returns an artistic rendering saved to `storage/app/public/cultures/illustrations/` labeled `AI-Generated Cultural Illustration`. |
| **SAM 3.1** | On Stage 4, upload an artifact photo (e.g. basket or mask), type the object prompt, click **Segment**. | Generates an isolated transparent PNG cutout alongside original photo without claiming antiquity. |
| **Muse Glimmer & Spark Open** | Navigate to `/cultural-hub/living-heritage/compare` and click **Run Comparative Benchmark**. | Side-by-side benchmark comparing latency and output between hosted and open-weight models. |

---

## 5. Security & Cultural Safeguards

1. **Authentication & Authorization**: All AI actions and draft saving require active authentication (`auth` middleware). Non-owners cannot edit or delete drafts.
2. **Cultural Consent Verification**: Contributors must explicitly check the Cultural Consent box before any data is sent to external AI endpoints.
3. **Strict Moderation Integrity**:
   - Submissions are assigned `status = 'pending_review'` (or `'draft'`).
   - Generated content is **NEVER** marked as `approved` automatically.
   - Platform administrators must review and approve submissions in `/admin/cultures`.
4. **Anti-Hallucination Constraints**: Muse Spark prompts strictly prohibit the model from inventing sacred rituals, sacred dates, or fake lineages. Unknown context is placed in `missing_information_warnings`.
5. **No Key Leakage**: API credentials are exclusively loaded server-side through `config/meta_ai.php`. The client masks keys (`••••••••`) and strips authorization tokens from all client responses and log files.

---

## 6. Official Meta Documentation & References

- [Meta AI Documentation](https://ai.meta.com/)
- [Segment Anything Model (SAM)](https://segment-anything.com/)
- [Llama & Open-Weight Model Hub](https://llama.meta.com/)
- [Meta Global AI Developer Hackathon Resources](https://lablab.ai/event/meta-global-ai-hackathon)
