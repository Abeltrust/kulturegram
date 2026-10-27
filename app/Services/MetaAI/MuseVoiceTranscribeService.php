<?php

namespace App\Services\MetaAI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MuseVoiceTranscribeService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public const ALLOWED_EXTENSIONS = ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'webm', 'mp4', 'mov', 'avi', 'm4v', 'mkv', 'mpeg', 'mpga'];
    public const MAX_FILE_SIZE_KB = 102400; // 100 MB

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.voice_transcribe', 'muse-voice-transcribe');
    }

    /**
     * Validate audio file for transcription.
     *
     * @throws ValidationException
     */
    public function validateAudio(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw ValidationException::withMessages([
                'audio' => "Unsupported audio format (.{$extension}). Supported formats: " . implode(', ', self::ALLOWED_EXTENSIONS) . '.',
            ]);
        }

        $sizeKb = $file->getSize() / 1024;
        if ($sizeKb > self::MAX_FILE_SIZE_KB) {
            throw ValidationException::withMessages([
                'audio' => 'Audio file exceeds the 25MB limit for cultural audio recordings.',
            ]);
        }
    }

    /**
     * Transcribe an audio recording using Muse Voice Transcribe.
     *
     * @param UploadedFile|string $audio Uploaded file object or stored relative path
     * @param string|null $languageHint Optional ISO code or dialect note (e.g. 'yor', 'hau', 'ibo', 'en')
     * @return array Standardized result envelope
     */
    public function transcribe(UploadedFile|string $audio, ?string $languageHint = null): array
    {
        if (!$this->client->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'transcript' => '',
                'language' => null,
                'segments' => [],
                'model' => $this->model,
                'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your environment to enable audio transcription.',
                'status_code' => 503,
                'latency_ms' => 0.0,
            ];
        }

        // Determine contents and filename
        if ($audio instanceof UploadedFile) {
            $this->validateAudio($audio);
            $contents = file_get_contents($audio->getRealPath());
            $filename = $audio->getClientOriginalName();
            $mime = $audio->getMimeType();
        } else {
            // Stored path
            if (!Storage::disk('public')->exists($audio)) {
                return [
                    'success' => false,
                    'configured' => true,
                    'transcript' => '',
                    'language' => null,
                    'segments' => [],
                    'model' => $this->model,
                    'error' => "Audio file not found at path: {$audio}",
                    'latency_ms' => 0.0,
                ];
            }
            $contents = Storage::disk('public')->get($audio);
            $filename = basename($audio);
            $mime = Storage::disk('public')->mimeType($audio) ?: 'audio/mpeg';
        }

        $multipart = [
            [
                'file' => true,
                'name' => 'file',
                'contents' => $contents,
                'filename' => $filename,
                'headers' => ['Content-Type' => $mime],
            ],
            [
                'name' => 'model',
                'contents' => $this->model,
            ],
        ];

        if (!empty($languageHint)) {
            $multipart[] = [
                'name' => 'language',
                'contents' => $languageHint,
            ];
        }

        $response = $this->client->postMultipart('audio/transcriptions', $multipart, $this->model);

        if (!$response['success']) {
            return [
                'success' => false,
                'configured' => true,
                'transcript' => '',
                'language' => null,
                'segments' => [],
                'model' => $this->model,
                'error' => $response['error'] ?? 'Audio transcription failed upstream.',
                'status_code' => $response['status_code'],
                'latency_ms' => $response['latency_ms'],
            ];
        }

        // Parse transcript response
        $data = $response['data'] ?? [];
        $transcript = trim($data['text'] ?? ($data['transcript'] ?? ''));
        $detectedLanguage = $data['language'] ?? $languageHint;
        $segments = $data['segments'] ?? [];

        if (empty($transcript)) {
            return [
                'success' => false,
                'configured' => true,
                'transcript' => '',
                'language' => $detectedLanguage,
                'segments' => [],
                'model' => $this->model,
                'error' => 'No clear speech could be recognized in the provided audio recording. Please verify audio clarity.',
                'status_code' => 200,
                'latency_ms' => $response['latency_ms'],
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'transcript' => $transcript,
            'language' => $detectedLanguage,
            'segments' => $segments,
            'model' => $this->model,
            'error' => null,
            'status_code' => 200,
            'latency_ms' => $response['latency_ms'],
        ];
    }
}
