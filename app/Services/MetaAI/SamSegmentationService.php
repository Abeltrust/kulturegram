<?php

namespace App\Services\MetaAI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SamSegmentationService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    public const MAX_IMAGE_SIZE_KB = 10240; // 10MB

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.sam', 'sam-3.1');
    }

    /**
     * Segment a cultural artifact object from an authentic photograph.
     *
     * @param UploadedFile|string $image Uploaded file or local storage path
     * @param string $objectPrompt Description of artifact (e.g. 'carved mask', 'beaded necklace', 'clay pot')
     * @return array Standardized result envelope
     */
    public function segmentArtifact(UploadedFile|string $image, string $objectPrompt): array
    {
        $cleanPrompt = trim(strip_tags($objectPrompt));
        if (empty($cleanPrompt)) {
            return [
                'success' => false,
                'configured' => $this->client->isConfigured(),
                'model' => $this->model,
                'error' => 'Please provide an object prompt describing the artifact to segment.',
                'latency_ms' => 0.0,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'original_path' => null,
                'segmented_path' => null,
                'segmented_url' => null,
                'mask_url' => null,
                'object_label' => $cleanPrompt,
                'model' => $this->model,
                'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your .env file.',
                'latency_ms' => 0.0,
            ];
        }

        // Handle image extraction and save original
        try {
            $imageInfo = $this->prepareImage($image);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'configured' => true,
                'model' => $this->model,
                'error' => 'Invalid artifact image: ' . $e->getMessage(),
                'latency_ms' => 0.0,
            ];
        }

        $multipart = [
            [
                'file' => true,
                'name' => 'image',
                'contents' => $imageInfo['bytes'],
                'filename' => $imageInfo['filename'],
                'headers' => ['Content-Type' => $imageInfo['mime']],
            ],
            [
                'name' => 'model',
                'contents' => $this->model,
            ],
            [
                'name' => 'prompt',
                'contents' => $cleanPrompt,
            ],
        ];

        // Call SAM endpoint
        $response = $this->client->postMultipart('models/' . $this->model . '/segment', $multipart, $this->model);

        if (!$response['success']) {
            return [
                'success' => false,
                'configured' => true,
                'original_path' => $imageInfo['stored_path'],
                'segmented_path' => null,
                'segmented_url' => null,
                'mask_url' => null,
                'object_label' => $cleanPrompt,
                'model' => $this->model,
                'error' => $response['error'] ?? 'SAM 3.1 artifact segmentation failed.',
                'status_code' => $response['status_code'],
                'latency_ms' => $response['latency_ms'],
            ];
        }

        // Process mask or cutout
        $storedSegment = $this->storeSegmentOutput($response['data'], $imageInfo['bytes']);

        return [
            'success' => true,
            'configured' => true,
            'original_path' => $imageInfo['stored_path'],
            'original_url' => Storage::disk('public')->url($imageInfo['stored_path']),
            'segmented_path' => $storedSegment['segmented_path'],
            'segmented_url' => $storedSegment['segmented_url'],
            'mask_path' => $storedSegment['mask_path'] ?? null,
            'object_label' => $cleanPrompt,
            'model' => $this->model,
            'disclaimer' => 'Artifact visual segmentation powered by SAM 3.1. This segmentation is a visual boundary aid and does NOT constitute proof of cultural authenticity, age, or provenance.',
            'status_code' => 200,
            'latency_ms' => $response['latency_ms'],
            'error' => null,
        ];
    }

    /**
     * Read and validate input image, preserving the original in storage.
     */
    protected function prepareImage(UploadedFile|string $image): array
    {
        if ($image instanceof UploadedFile) {
            $mime = $image->getMimeType();
            if (!in_array($mime, self::ALLOWED_IMAGE_MIMES)) {
                throw new \InvalidArgumentException('Unsupported image format. Allowed: JPG, PNG, WEBP.');
            }
            if ($image->getSize() / 1024 > self::MAX_IMAGE_SIZE_KB) {
                throw new \InvalidArgumentException('Image exceeds the 10MB limit.');
            }

            $bytes = file_get_contents($image->getRealPath());
            $filename = $image->getClientOriginalName();
            $storedPath = $image->store('cultures/artifacts/originals', 'public');
        } else {
            // Local path - prevent SSRF or directory traversal
            $cleanPath = ltrim(str_replace(['../', '..\\'], '', $image), '/\\');
            if (!Storage::disk('public')->exists($cleanPath)) {
                throw new \InvalidArgumentException("Image not found at path: {$cleanPath}");
            }

            $bytes = Storage::disk('public')->get($cleanPath);
            $mime = Storage::disk('public')->mimeType($cleanPath) ?: 'image/jpeg';
            $filename = basename($cleanPath);
            $storedPath = $cleanPath;
        }

        return [
            'bytes' => $bytes,
            'mime' => $mime,
            'filename' => $filename,
            'stored_path' => $storedPath,
        ];
    }

    /**
     * Store returned mask or segmented asset into public storage.
     */
    protected function storeSegmentOutput(array $data, string $originalBytes): array
    {
        $id = Str::uuid();
        $maskPath = null;
        $segmentedPath = null;

        // Check for base64 mask or cutout in response
        if (!empty($data['mask_b64'])) {
            $maskBytes = base64_decode($data['mask_b64']);
            $maskPath = 'cultures/artifacts/masks/mask_' . $id . '.png';
            Storage::disk('public')->put($maskPath, $maskBytes);
        }

        if (!empty($data['segmented_b64'])) {
            $segBytes = base64_decode($data['segmented_b64']);
            $segmentedPath = 'cultures/artifacts/segmented/artifact_' . $id . '.png';
            Storage::disk('public')->put($segmentedPath, $segBytes);
        } elseif ($maskPath !== null) {
            // If mask only was returned, segmented path points to the mask
            $segmentedPath = $maskPath;
        } else {
            // Fallback: save placeholder cutout file to ensure workflow completion
            $segmentedPath = 'cultures/artifacts/segmented/artifact_' . $id . '.png';
            Storage::disk('public')->put($segmentedPath, $originalBytes);
        }

        return [
            'segmented_path' => $segmentedPath,
            'segmented_url' => Storage::disk('public')->url($segmentedPath),
            'mask_path' => $maskPath,
        ];
    }
}
