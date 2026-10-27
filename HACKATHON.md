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
4. **Stage 4 (Multi-Model Enrichment & Safeguarding):**
   - **Muse Wonder:** Bounded preservation assistant providing:
     - **Preservation Gap Audit & Task Checklist:** Actionable elder questions and conservation tasks.
     - **Cultural Fact-Check & Plagiarism Screen:** Evaluates oral authenticity (0-100), flags generic web copy/textbook plagiarism risk (low/medium/high), checks fidelity against source recording, and identifies historical assertions requiring community elder verification.
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
| **Meta Model API Key** | Shared Key Architecture | Verified (`MetaModelApiClientTest`, 6 tests) | **LIVE VERIFIED** (HTTP 200 via `GET /models`) |
| **Muse Spark** | `muse-spark-1.3` | Verified (`LivingHeritageCaptureTest`) | **LIVE VERIFIED** (Extracted structured archival record in ~2.9s) |
| **Muse Wonder** | `muse-wonder-1.0` | Verified (`LivingHeritageCaptureTest`) | **LIVE VERIFIED** (Bounded preservation plan & checklist generated) |
| **Muse Voice Transcribe**| `muse-voice-transcribe-1.0` | Verified (`Http::fake()`, multipart upload, language parsing) | Wired to verified live model ID; ready for audio test |
| **Muse Image** | `muse-image-1.0` | Verified (`Http::fake()`, attribution disclaimer, storage) | Wired to verified live model ID; ready for generation test |
| **SAM 3.1** | `sam-3.1` | Verified (`Http::fake()`, cutout isolation, authenticity notice) | Wired to verified live model ID; ready for segmentation test |
| **Muse Glimmer** | Open-Weight Service | Verified (independent provider architecture) | Pending open-weight endpoint (e.g. Ollama/vLLM) |
| **Muse Spark Open** | Open-Weight Service | Verified (independent provider architecture) | Pending open-weight endpoint (e.g. Ollama/vLLM) |
| **Muse Code** | Development Tool | Verified (development & testing workflows) | N/A (Dev Tool) |

---

## 4. Platform-Wide AI Integrations (Live Stream, Privacy Audit, Cultural Connections, and Events)

In addition to Living Heritage Capture, AI is integrated across Kulturegram's core features:

### A. AI-Powered Live Stream Monitoring (`/live-stream/{stream}`)
- **Context-Aware Stream Analysis:** Uses Meta AI (`muse-spark-1.3`) to analyze live broadcast chat in real time against the stream title, category, and cultural topic.
- **Cultural Nuance Understanding:** Specifically calibrated to respect traditional dialect expressions, proverbs, historical debates, chieftaincy disputes, and playful banter without censorship.
- **Transparent Platform Rule Citations:** Flags messages for targeted harassment, hate speech, spam, unauthorized PII exposure, and dangerous disruption with citations to Kulturegram Community Guidelines.
- **Non-Blocking Architecture:** Chat message persistence and broadcast remain sub-second; AI moderation runs asynchronously without blocking the live room experience.
- **Human-in-the-Loop Host Moderation Queue:** Host and authorized moderators can review flagged messages, inspect AI rationale, dismiss false flags, or remove offending messages. No automated bans.

### B. AI Privacy Protection & System Policy Auditing (`/admin/privacy-audit`)
- **Content Privacy Protection:** Detects private phone numbers (Nigerian & international), email addresses, government identity numbers (NIN/SSN), credit cards, and plaintext credentials with safe redaction preview and pre-submission warnings.
- **6-Pillar Technical Security Verification:**
  1. *Cryptographic Password Hashing:* Sampled user accounts verified for standard Bcrypt/Argon2 signatures.
  2. *Personal Heritage Vault Isolation:* Private heirloom access strictly partitioned by user ownership.
  3. *Timeline Post Privacy Boundaries:* Private posts quarantined from public feeds and unauthenticated indexers.
  4. *Public Model Serialization Protection:* Token and password hash stripping verified against Eloquent `$hidden` rules.
  5. *Session Timeout & Cookie Hardening:* Enforces HttpOnly session cookies and idle timeouts.
  6. *Indigenous Data Sovereignty & Right to Erasure:* Verified authenticated account deletion cascading personal artifacts.
- **Executive AI Synthesis:** Uses Meta AI (`muse-wonder-1.0`) to generate an executive compliance briefing comparing technical system benchmarks against the published Data Sovereignty & Privacy Policy (`/privacy`).
- **Interactive PII Test Bench:** Admin tool to test and inspect redaction rules in real time.

### C. AI Cultural Connection Recommendations (`/cultural-discovery/recommendations`)
- **Voluntary-Only Matching:** Recommends peers, cultural groups, and historical projects based strictly on user-declared interests, language proficiencies, and regional heritage—never private messages or sensitive inferences.
- **Explainable Matches:** Clear attribution badges on every card (e.g., *"Recommended because you share an interest in Oral History and Yoruba Heritage"*).
- **User Control & Data Sovereignty:** One-click dismissals stored in `cultural_recommendation_dismissals` and an instant global opt-out switch respecting user preference.
- **Seamless Interface Integration:** Embedded directly into the main Timeline feed (`timeline/index.blade.php`) and Communities directory (`communities/index.blade.php`).

### D. AI-Assisted Event Discovery & Organizer Reach (`/events` & `/events/create`)
- **Interest-Based Discovery:** Recommends public cultural gatherings matching declared traditions and regional proximity with clear explanations.
- **Organizer Reach Assistant:** Suggests culturally resonant titles, engaging descriptions, targeted interest tags, and optimal community reach channels before publication.
- **Strict Privacy & Permissions:** Preserves event privacy boundaries and registration caps.

---

## 5. Test Suite Summary

- **Total Test Files:** 11 feature test suites
- **New AI Platform Test Suite:** `tests/Feature/AIPlatformIntegrationsTest.php` (13 tests, 49 assertions, 100% passing)
- **Living Heritage Test Suite:** `tests/Feature/LivingHeritageCaptureTest.php` (17 tests, 68 assertions, 100% passing)
- **Zero Live Credential Dependency:** Automated tests run fully offline with mocks, SQLite in-memory, and deterministic fixtures.

