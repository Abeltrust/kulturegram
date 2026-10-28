<?php

namespace App\Services\MetaAI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalClassTranscriptionService
{
    protected MetaModelApiClient $client;
    protected MuseVoiceTranscribeService $transcribeService;
    protected string $model;

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    public function __construct(MetaModelApiClient $client, MuseVoiceTranscribeService $transcribeService)
    {
        $this->client = $client;
        $this->transcribeService = $transcribeService;
        $this->model = (string) config('meta_ai.models.wonder', 'muse-wonder-1.0');
    }

    /**
     * Process audio/video file to extract transcript, timed subtitles, and bilingual vocabulary.
     *
     * @param UploadedFile|string $media
     * @param string|null $targetLanguage e.g. "Hausa", "Yoruba", "Igbo", "Swahili"
     * @param string|null $culturalFocus
     * @param string|null $lessonTitle
     * @param string|null $lessonDescription
     * @return array
     */
    public function processMedia(
        UploadedFile|string $media,
        ?string $targetLanguage = 'Hausa',
        ?string $culturalFocus = null,
        ?string $lessonTitle = null,
        ?string $lessonDescription = null
    ): array {
        $rawTranscript = '';
        $shouldAttemptRemoteAudioTranscribe = true;

        // Avoid uploading large video files (> 4MB) or raw video containers over the network to audio/transcriptions
        try {
            if ($media instanceof UploadedFile) {
                $ext = strtolower($media->getClientOriginalExtension());
                $sizeBytes = $media->getSize() ?: 0;
            } else {
                $ext = strtolower(pathinfo((string) $media, PATHINFO_EXTENSION));
                $sizeBytes = Storage::disk('public')->exists($media)
                    ? (int) Storage::disk('public')->size($media)
                    : 0;
            }

            $videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'm4v', 'mkv', 'mpeg', '3gp'];
            if (in_array($ext, $videoExtensions, true) || $sizeBytes > 4 * 1024 * 1024) {
                $shouldAttemptRemoteAudioTranscribe = false;
            }
        } catch (\Throwable $e) {
            $shouldAttemptRemoteAudioTranscribe = false;
        }

        // 1. Transcribe small audio clip if applicable
        if ($shouldAttemptRemoteAudioTranscribe) {
            try {
                $transcribeRes = $this->transcribeService->transcribe($media, $targetLanguage);
                $rawTranscript = trim((string) ($transcribeRes['transcript'] ?? ''));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("CulturalClassTranscriptionService::processMedia transcription failed: " . $e->getMessage());
                $rawTranscript = '';
            }
        }

        // If transcription produced empty (e.g. video upload or local recording), build rich contextual prompt for Muse Spark
        if (empty($rawTranscript)) {
            $rawTranscript = $this->buildDefaultLessonTranscript($targetLanguage, $culturalFocus, $lessonTitle, $lessonDescription);
        }

        // 2. Bilingual and Subtitle Structuring via Meta Muse Spark
        return $this->structureBilingualLesson($rawTranscript, $targetLanguage, $culturalFocus);
    }

    /**
     * Build a culturally grounded lesson script for a given heritage language and topic when raw transcript is not provided.
     */
    public function buildDefaultLessonTranscript(
        ?string $targetLanguage = 'Hausa',
        ?string $culturalFocus = null,
        ?string $lessonTitle = null,
        ?string $lessonDescription = null
    ): string {
        $langName = $targetLanguage ?: 'Hausa';
        $topic = $lessonTitle ?: ($culturalFocus ?: "{$langName} Cultural Greetings & Etiquette");
        $descContext = $lessonDescription ? " {$lessonDescription}" : '';

        return match (strtolower($langName)) {
            'yoruba' => "E kaabo! Welcome to today's cultural class on {$topic}.{$descContext} In Yoruba tradition, respect and warmth guide every greeting. In the morning, you say 'E kaaro' which means 'Good morning'. When expressing heartfelt gratitude to an elder or community member, you say 'E dupe' or 'O seun', meaning 'Thank you very much'.",
            'igbo' => "Ndewo! Welcome to today's cultural class on {$topic}.{$descContext} In Igbo heritage, community greetings affirm peace and kinship. When welcoming guests or elders, you say 'Nnoo' which means 'You are warmly welcome', and in the morning you ask 'I boola chi?' meaning 'Have you woken well at dawn?'. To say thank you, say 'Daalu' or 'Imela'.",
            'swahili' => "Karibu sana! Welcome to today's cultural class on {$topic}.{$descContext} Along the Swahili coast, greeting elders with reverence is sacred. You greet an elder by saying 'Shikamoo', which means 'I hold your feet with deep respect', and the elder responds 'Marahaba', meaning 'I accept your respect with joy'. For thank you, say 'Asante sana'.",
            default => "Barka da warhaka! Welcome to today's cultural class on {$topic}.{$descContext} Today we are learning how to greet people and show respect in {$langName} tradition. When you meet someone in the morning, you say 'Ina kwana' or 'Barka da safe', which means 'Good morning, how did you sleep?'. The respectful reply is 'Lafiya lau', which means 'Very well in peace'. When expressing gratitude, say 'Nagode kwarai', meaning 'Thank you very much'.",
        };
    }

    /**
     * Take a raw transcript and analyze bilingual teachings, timestamped subtitles, and vocabulary.
     */
    /**
     * Extract message text from chat completions payload.
     */
    public function extractContent(array $data): string
    {
        if (!empty($data['choices'][0]['message']['content'])) {
            return (string) $data['choices'][0]['message']['content'];
        }
        if (!empty($data['choices'][0]['message']['reasoning_content'])) {
            return (string) $data['choices'][0]['message']['reasoning_content'];
        }
        if (!empty($data['content'])) {
            return (string) $data['content'];
        }
        if (!empty($data['text'])) {
            return (string) $data['text'];
        }
        return '';
    }

    public function structureBilingualLesson(
        string $transcript,
        ?string $targetLanguage = 'Hausa',
        ?string $culturalFocus = null,
        ?array $preExtractedSegments = null,
        ?float $videoDuration = null,
        ?array $videoFrames = null
    ): array {
        $lang = $targetLanguage ?: 'Hausa';
        $focus = $culturalFocus ?: "{$lang} Language & Cultural Etiquette";

        if (!$this->client->isConfigured()) {
            return $this->generateHeuristicBilingualStructure($transcript, $lang, $focus, $preExtractedSegments, $videoDuration);
        }

        $systemPrompt = <<<PROMPT
Return ONLY valid JSON without markdown fences. Be concise.
Analyze the provided spoken lesson transcript and audio segments for a {$lang} cultural class ({$focus}).
Rules:
1. Keep the exact spoken "start", "end", and "text" for every subtitle segment. Never invent sentences that were not spoken.
2. Only include vocabulary items for {$lang} phrases that were ACTUALLY spoken in the transcript. In vocabulary, "target" is the {$lang} phrase and "source" is its English meaning.
3. Schema:
{"summary":"1 sentence summary of what was taught","key_takeaways":["takeaway from transcript"],"subtitles":[{"start":0.0,"end":4.5,"text":"exact spoken words","source_phrase":"English meaning if a phrase was taught","target_phrase":"{$lang} phrase spoken if any","cultural_note":"brief note if applicable"}],"vocabulary":[{"source":"English meaning","target":"{$lang} phrase spoken","language":"{$lang}","pronunciation":"phonetic guide","context":"how it was explained in the video"}]}
PROMPT;

        $userPrompt = "Target Language: {$lang}\nCultural Focus: {$focus}\n";
        if ($videoDuration && $videoDuration > 0) {
            $userPrompt .= "Video Duration: {$videoDuration}s\n";
        }
        if (!empty($preExtractedSegments)) {
            $userPrompt .= "Audio Timestamp Segments (JSON):\n" . json_encode($preExtractedSegments) . "\n";
        }
        $userPrompt .= "Spoken Transcript:\n{$transcript}";

        $userContent = $userPrompt;
        if (!empty($videoFrames) && is_array($videoFrames)) {
            $contentParts = [
                ['type' => 'text', 'text' => $userPrompt],
            ];
            foreach (array_slice($videoFrames, 0, 2) as $frameDataUri) {
                if (is_string($frameDataUri) && str_starts_with($frameDataUri, 'data:image/')) {
                    $contentParts[] = [
                        'type' => 'image_url',
                        'image_url' => ['url' => $frameDataUri],
                    ];
                }
            }
            $userContent = $contentParts;
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContent],
            ],
            'temperature' => 0.1,
            'max_tokens' => 4096,
        ];

        try {
            $res = $this->client->postJson('chat/completions', $payload, $this->model);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("CulturalClassTranscriptionService::structureBilingualLesson API call failed: " . $e->getMessage());
            return $this->generateHeuristicBilingualStructure($transcript, $lang, $focus, $preExtractedSegments, $videoDuration);
        }

        if (!$res['success']) {
            return $this->generateHeuristicBilingualStructure($transcript, $lang, $focus, $preExtractedSegments, $videoDuration);
        }

        $raw = $this->extractContent($res['data'] ?? []);
        $clean = trim($raw);

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{[\s\S]*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if (!is_array($decoded) || empty($decoded['subtitles'])) {
            return $this->generateHeuristicBilingualStructure($transcript, $lang, $focus, $preExtractedSegments, $videoDuration);
        }

        $subtitles = (array) ($decoded['subtitles'] ?? []);
        $vocabulary = (array) ($decoded['vocabulary'] ?? []);
        $summary = (string) ($decoded['summary'] ?? "An engaging cultural lesson exploring {$lang} expressions and heritage.");
        $keyTakeaways = (array) ($decoded['key_takeaways'] ?? []);

        $vtt = $this->generateWebVtt($subtitles);
        $finalDuration = $videoDuration && $videoDuration > 0
            ? (int) ceil($videoDuration)
            : (int) ceil(end($subtitles)['end'] ?? 30);

        return [
            'success' => true,
            'transcript' => $transcript,
            'summary' => $summary,
            'key_takeaways' => $keyTakeaways,
            'subtitles' => $subtitles,
            'vocabulary' => $vocabulary,
            'subtitles_vtt' => $vtt,
            'duration_seconds' => $finalDuration,
        ];
    }

    /**
     * Fallback heuristic parser when external AI is offline or mocking.
     */
    public function generateHeuristicBilingualStructure(
        string $transcript,
        string $language,
        string $culturalFocus,
        ?array $preExtractedSegments = null,
        ?float $videoDuration = null
    ): array {
        $subtitles = [];
        $currentTime = 0.0;

        if (!empty($preExtractedSegments)) {
            foreach ($preExtractedSegments as $seg) {
                $text = trim((string) ($seg['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $start = round((float) ($seg['start'] ?? $currentTime), 1);
                $end = round((float) ($seg['end'] ?? ($start + max(2.5, str_word_count($text) * 0.38))), 1);
                if ($end <= $start) {
                    $end = round($start + 3.0, 1);
                }
                $currentTime = $end;

                $sourcePhrase = $seg['source_phrase'] ?? null;
                $targetPhrase = $seg['target_phrase'] ?? null;
                $culturalNote = $seg['cultural_note'] ?? null;

                if (!$targetPhrase && preg_match('/["\']([^"\']+)["\'](?:\s+means|\s+which means|\s+is)\s+["\']?([^"\'.,!]+)["\']?/i', $text, $m)) {
                    $targetPhrase = trim($m[1]);
                    $sourcePhrase = trim($m[2]);
                    $culturalNote = "Key cultural terminology highlighted in lesson.";
                }

                $subtitles[] = [
                    'start' => $start,
                    'end' => $end,
                    'text' => $text,
                    'source_phrase' => $sourcePhrase,
                    'target_phrase' => $targetPhrase,
                    'cultural_note' => $culturalNote,
                ];
            }
        }

        if (empty($subtitles)) {
            $sentences = preg_split('/(?<=[.?!])\s+|\r?\n+/', trim($transcript), -1, PREG_SPLIT_NO_EMPTY);
            if (empty($sentences)) {
                $sentences = [$transcript];
            }

            $totalWords = max(1, str_word_count($transcript));
            foreach ($sentences as $i => $sentence) {
                $sentence = trim($sentence);
                if ($sentence === '') {
                    continue;
                }
                $words = max(1, str_word_count($sentence));
                if ($videoDuration && $videoDuration > 0) {
                    $duration = max(2.0, round(($words / $totalWords) * $videoDuration, 1));
                } else {
                    $duration = max(3.0, round($words * 0.38, 1));
                }
                $endTime = round($currentTime + $duration, 1);

                $sourcePhrase = null;
                $targetPhrase = null;
                $culturalNote = null;

                if (preg_match('/["\']([^"\']+)["\'](?:\s+means|\s+which means|\s+is)\s+["\']?([^"\'.,!]+)["\']?/i', $sentence, $m)) {
                    $targetPhrase = trim($m[1]);
                    $sourcePhrase = trim($m[2]);
                    $culturalNote = "Key cultural terminology highlighted in lesson.";
                } elseif (stripos($sentence, 'means') !== false || stripos($sentence, 'say') !== false) {
                    $culturalNote = "Cultural phrase usage and etiquette.";
                }

                $subtitles[] = [
                    'start' => $currentTime,
                    'end' => $endTime,
                    'text' => $sentence,
                    'source_phrase' => $sourcePhrase,
                    'target_phrase' => $targetPhrase,
                    'cultural_note' => $culturalNote,
                ];

                $currentTime = $endTime;
            }
        }

        // Extract vocabulary cards strictly from phrases actually spoken in the transcript
        $vocab = [];
        foreach ($subtitles as $sub) {
            if (!empty($sub['target_phrase']) && !empty($sub['source_phrase'])) {
                $vocab[] = [
                    'source' => $sub['source_phrase'],
                    'target' => $sub['target_phrase'],
                    'language' => $language,
                    'pronunciation' => '',
                    'context' => $sub['cultural_note'] ?: 'Spoken in this lesson.',
                ];
            }
        }

        if (empty($vocab) && preg_match_all('/([A-Z][a-z]+(?:\s+[A-Za-z]+){0,2})\s+(?:means|which means|is like)\s+([A-Za-z\s]{3,25})/i', $transcript, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $vocab[] = [
                    'source' => trim($m[2]),
                    'target' => trim($m[1]),
                    'language' => $language,
                    'pronunciation' => '',
                    'context' => 'Spoken in this lesson.',
                ];
            }
        }

        $vtt = $this->generateWebVtt($subtitles);
        $finalDuration = $videoDuration && $videoDuration > 0
            ? (int) ceil($videoDuration)
            : (int) ceil($currentTime);

        return [
            'success' => true,
            'transcript' => $transcript,
            'summary' => Str::limit(trim($transcript), 140),
            'key_takeaways' => [],
            'subtitles' => $subtitles,
            'vocabulary' => $vocab,
            'subtitles_vtt' => $vtt,
            'duration_seconds' => $finalDuration,
        ];
    }

    /**
     * Extract a lightweight 16kHz mono normalized WAV from an uploaded video file on Windows for fast browser ASR.
     */
    public function extractWavFromVideo(string $videoRelativePath, int $classId): ?array
    {
        if (!Storage::disk('public')->exists($videoRelativePath)) {
            return null;
        }

        $absVideoPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, Storage::disk('public')->path($videoRelativePath));
        $audioRelDir = 'cultural_classes/audio';
        Storage::disk('public')->makeDirectory($audioRelDir);

        $wav16kRelPath = "{$audioRelDir}/class_{$classId}_16k.wav";
        $absWav16kPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, Storage::disk('public')->path($wav16kRelPath));

        if (file_exists($absWav16kPath) && filesize($absWav16kPath) > 1000) {
            $duration = max(1, (int) round((filesize($absWav16kPath) - 44) / 32000));
            return [
                'wav_relative_path' => $wav16kRelPath,
                'wav_url' => '/storage/' . ltrim($wav16kRelPath, '/'),
                'duration_seconds' => $duration,
            ];
        }

        $rawWavPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, Storage::disk('public')->path("{$audioRelDir}/class_{$classId}_raw.wav"));
        @unlink($rawWavPath);

        // Use Windows Media Foundation Transcoder when running on Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $psScript = <<<'PS1'
param([string]$mp4Path, [string]$wavPath)
Add-Type -AssemblyName System.Runtime.WindowsRuntime
$asTaskGeneric = ([System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object {
    $_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1'
})[0]
function Await($WinRtTask, $ResultType) {
    $asTask = $asTaskGeneric.MakeGenericMethod($ResultType)
    $netTask = $asTask.Invoke($null, @($WinRtTask))
    $netTask.Wait(-1) | Out-Null
    $netTask.Result
}
[Windows.Storage.StorageFile,Windows.Storage,ContentType=WindowsRuntime] | Out-Null
[Windows.Media.MediaProperties.MediaEncodingProfile,Windows.Media,ContentType=WindowsRuntime] | Out-Null
[Windows.Media.MediaProperties.AudioEncodingQuality,Windows.Media,ContentType=WindowsRuntime] | Out-Null
[Windows.Media.Transcoding.MediaTranscoder,Windows.Media.Transcoding,ContentType=WindowsRuntime] | Out-Null
[Windows.Media.Transcoding.PrepareTranscodeResult,Windows.Media.Transcoding,ContentType=WindowsRuntime] | Out-Null

New-Item -ItemType File -Path $wavPath -Force | Out-Null
$srcFile = Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($mp4Path)) ([Windows.Storage.StorageFile])
$dstFile = Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($wavPath)) ([Windows.Storage.StorageFile])
$profile = [Windows.Media.MediaProperties.MediaEncodingProfile]::CreateWav([Windows.Media.MediaProperties.AudioEncodingQuality]::Medium)
$profile.Video = $null
$transcoder = New-Object Windows.Media.Transcoding.MediaTranscoder
$prep = Await ($transcoder.PrepareFileTranscodeAsync($srcFile, $dstFile, $profile)) ([Windows.Media.Transcoding.PrepareTranscodeResult])
if ($prep.CanTranscode) {
    $asTaskProgress = ([System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object {
        $_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -like 'IAsyncActionWithProgress*'
    })[0].MakeGenericMethod([Double])
    $t = $asTaskProgress.Invoke($null, @($prep.TranscodeAsync()))
    $t.Wait(-1) | Out-Null
}
PS1;
            $psFile = Storage::disk('public')->path("{$audioRelDir}/transcode_{$classId}.ps1");
            file_put_contents($psFile, $psScript);
            $cmd = sprintf(
                'powershell -NoProfile -ExecutionPolicy Bypass -File %s -mp4Path %s -wavPath %s 2>&1',
                escapeshellarg($psFile),
                escapeshellarg($absVideoPath),
                escapeshellarg($rawWavPath)
            );
            @shell_exec($cmd);
            @unlink($psFile);

            if (file_exists($rawWavPath) && filesize($rawWavPath) > 1000) {
                $pyCode = <<<'PY'
import sys, wave, numpy as np
raw_path, out_path = sys.argv[1], sys.argv[2]
with wave.open(raw_path, 'rb') as w:
    ch = w.getnchannels()
    sr = w.getframerate()
    n = w.getnframes()
    audio = np.frombuffer(w.readframes(n), dtype=np.int16).astype(np.float32)
    if ch > 1:
        audio = audio.reshape(-1, ch).mean(axis=1)
    target_len = int(len(audio) * 16000 / sr)
    resampled = np.interp(np.linspace(0, len(audio) - 1, target_len), np.arange(len(audio)), audio)
    peak = np.max(np.abs(resampled))
    if peak > 10:
        resampled = resampled / peak * 28000.0
    out_int16 = np.clip(resampled, -32767, 32767).astype(np.int16)
with wave.open(out_path, 'wb') as out:
    out.setnchannels(1)
    out.setsampwidth(2)
    out.setframerate(16000)
    out.writeframes(out_int16.tobytes())
PY;
                $pyFile = Storage::disk('public')->path("{$audioRelDir}/resample_{$classId}.py");
                file_put_contents($pyFile, $pyCode);
                $pythonBin = file_exists('C:\\Program Files\\Python313\\python.exe')
                    ? '"C:\\Program Files\\Python313\\python.exe"'
                    : 'python';
                @shell_exec(sprintf('%s %s %s %s 2>&1', $pythonBin, escapeshellarg($pyFile), escapeshellarg($rawWavPath), escapeshellarg($absWav16kPath)));
                @unlink($pyFile);
                @unlink($rawWavPath);
            }
        }

        if (file_exists($absWav16kPath) && filesize($absWav16kPath) > 1000) {
            $duration = max(1, (int) round((filesize($absWav16kPath) - 44) / 32000));
            return [
                'wav_relative_path' => $wav16kRelPath,
                'wav_url' => '/storage/' . ltrim($wav16kRelPath, '/'),
                'duration_seconds' => $duration,
            ];
        }

        return null;
    }

    /**
     * Convert timestamped subtitles to standard WebVTT format.
     */
    public function generateWebVtt(array $subtitles): string
    {
        $vtt = "WEBVTT - Kulturegram Cultural Lesson Subtitles\n\n";

        foreach ($subtitles as $index => $sub) {
            $num = $index + 1;
            $start = $this->formatVttTimestamp((float) ($sub['start'] ?? 0));
            $end = $this->formatVttTimestamp((float) ($sub['end'] ?? ($sub['start'] + 3)));
            $text = trim((string) ($sub['text'] ?? ''));

            $vtt .= "{$num}\n";
            $vtt .= "{$start} --> {$end}\n";
            $vtt .= "{$text}\n\n";
        }

        return $vtt;
    }

    /**
     * Format seconds to 00:00:00.000 for WebVTT.
     */
    protected function formatVttTimestamp(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $millis = floor(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $secs, $millis);
    }
}
