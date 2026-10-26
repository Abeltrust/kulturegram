# Kulturegram — Meta Global AI Developer Hackathon Submission

**Project Title:** Kulturegram Living Heritage Capture  
**Tagline:** *Preserve the stories. Protect the knowledge. Pass the heritage forward.*  
**Track:** Meta Global AI Developer Hackathon  
**Platform:** Laravel 10 / PHP 8.2 / Tailwind CSS / Alpine.js / MySQL

---

## 1. What Existed Before the Hackathon

Prior to the hackathon implementation, Kulturegram provided:
- User registration, login, and profile management with origin and language preferences.
- Cultural Hub index and detail views (`app/Http/Controllers/CulturalHubController.php`, `Culture` model).
- Manual cultural entry form (`resources/views/cultural-hub/create.blade.php`).
- Basic photo, audio, and video upload storage.
- Administrative moderation workflow (`app/Http/Controllers/Admin/ModerationController.php`) with statuses `pending_review`, `approved`, and `featured`.
- Community hubs, events, timeline posts, and real-time messaging.

**Pre-existing AI functionality:** None. All cultural documentation required fully manual input.

---

## 2. What Is New (Hackathon Implementations)

### A. Living Heritage Capture Pipeline (`/cultural-hub/living-heritage`)
A 5-stage preservation workflow assisting contributors without replacing human authority:
1. **Stage 1 (Capture Story):** Audio recording upload (MP3, WAV, M4A, OGG, AAC <= 25MB) or written narrative input with mandatory **Cultural Consent & Safeguarding Agreement**.
2. **Stage 2 (Transcribe & Review):** **Muse Voice Transcribe** integration with editable transcript textarea allowing contributors to correct dialect words, names, and cultural nuances while preserving original audio authentic recordings.
3. **Stage 3 (Prepare with AI):** **Muse Spark** structured extraction mapping oral accounts into Kulturegram's archival schema (title, region, category, narrative description, rituals, transmission methods, challenges, tags) with anti-hallucination discipline and elder follow-up questions.
4. **Stage 4 (Multi-Model Enrichment):**
   - **Muse Wonder:** Bounded preservation assistant providing gap audits, verification flags, and an actionable preservation checklist.
   - **Muse Image:** Cultural illustration generator strictly watermarked and attributed as AI-generated artwork (never replacing authentic evidence).
   - **SAM 3.1:** Authentic artifact segmentation isolating cultural tools, carvings, and textiles without claiming antiquity proof.
5. **Stage 5 (Review & Moderation):** Dual submission options allowing contributors to save private drafts (`status = 'draft'`) or submit to community moderation (`status = 'pending_review'`).

### B. Shared Meta Model API Client Architecture (`app/Services/MetaAI/`)
- **Single Shared Credential:** All hosted Meta models (`Muse Spark`, `Muse Wonder`, `Muse Image`, `Muse Voice Transcribe`, `SAM 3.1`) share `META_MODEL_API_KEY` through centralized configuration (`config/meta_ai.php`).
- **Resilience & Security:** Unified `MetaModelApiClient` with connection timeouts, rate limit detection (429), access failure handling (401/403), masked key indicators (`getMaskedKey()`), and token redaction from log files.
- **Offline / Graceful Fallback:** When the API key is pending or credits are awaiting application, manual capture, recording uploads, and private drafts continue to work with helpful diagnostic guidance.

### C. Open-Weight Model Inference Lab (`/cultural-hub/living-heritage/compare`)
- **Muse Glimmer:** Open-weight taxonomy tagging and factual claim checking.
- **Muse Spark Open:** Open-weight structured extraction benchmarking.
- **Side-by-Side Comparison:** Compares latency (ms), provider, and raw outputs between hosted Meta Model API and open-weight inference providers (vLLM, Ollama, Together AI).

### D. Database Migrations
- Migration `2026_10_26_160000_add_living_heritage_fields_to_cultures_table.php` added:
  - `raw_story` (text nullable)
  - `audio_transcript` (text nullable)
  - `is_ai_assisted` (boolean default false)
  - `ai_illustration` (string nullable)
  - `ai_illustration_prompt` (text nullable)
  - `artifact_segmentation` (json nullable)
  - `wonder_preservation_plan` (json nullable)
  - `ai_metadata` (json nullable)
  - Upgraded `status` column to accommodate `'draft'`.

---

## 3. Models Tested vs. Awaiting Credits

| Model | Integration Status | Automated Test Verification | Live Credential Verification |
| :--- | :--- | :--- | :--- |
| **Muse Voice Transcribe** | Implemented (`MuseVoiceTranscribeService`) | Verified (`Http::fake()`, multipart upload, language parsing) | Pending hackathon credits |
| **Muse Spark** | Implemented (`MuseSparkService`) | Verified (`Http::fake()`, schema validation, anti-hallucination) | Pending hackathon credits |
| **Muse Wonder** | Implemented (`MuseWonderService`) | Verified (`Http::fake()`, bounded preservation checklist) | Pending hackathon credits |
| **Muse Image** | Implemented (`MuseImageService`) | Verified (`Http::fake()`, attribution disclaimer, storage) | Pending hackathon credits |
| **SAM 3.1** | Implemented (`SamSegmentationService`) | Verified (`Http::fake()`, cutout isolation, authenticity notice) | Pending hackathon credits |
| **Muse Glimmer** | Implemented (`MuseGlimmerService`) | Verified (independent provider architecture) | Pending open-weight endpoint |
| **Muse Spark Open** | Implemented (`MuseSparkOpenService`) | Verified (independent provider architecture) | Pending open-weight endpoint |
| **Muse Code** | Used as development tool | Verified (development & testing workflows) | N/A (Dev Tool) |

---

## 4. Test Suite Summary

- **Total Test Files:** 10 feature test suites
- **Total Tests Passing:** 86 tests passed (313 assertions)
  - `LivingHeritageCaptureTest`: 16 passed (62 assertions)
  - `MetaModelApiClientTest`: 6 passed (23 assertions)
  - Existing Core Test Suite (Admin, Auth, Community, Events, Messages, Timeline, Vault): 64 passed (228 assertions)
- **Zero Live Credential Dependency:** All automated tests run reproducibly in local environments without incurring credit costs or requiring live upstream tokens.
