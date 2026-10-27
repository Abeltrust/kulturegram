<?php

namespace App\Services\MetaAI;

use App\Models\AiFeedbackLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AiFeedbackService
{
    /**
     * Record a controlled feedback event from a user or moderator.
     */
    public function logFeedback(
        User $user,
        string $feedbackType,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $rating = null,
        ?string $reason = null,
        ?string $userCorrection = null,
        array $metadata = []
    ): AiFeedbackLog {
        // Redact any private sensitive tokens or large text blocks from metadata
        $safeMetadata = $this->sanitizeMetadata($metadata);

        $log = AiFeedbackLog::create([
            'user_id' => $user->id,
            'feedback_type' => $feedbackType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'rating' => $rating,
            'reason' => $reason ? substr($reason, 0, 100) : null,
            'user_correction' => $userCorrection ? substr($userCorrection, 0, 2000) : null,
            'metadata' => $safeMetadata,
            'status' => 'logged',
        ]);

        Log::info('[AI-Feedback] Feedback captured', [
            'type' => $feedbackType,
            'user_id' => $user->id,
            'target' => "{$targetType}:{$targetId}",
        ]);

        return $log;
    }

    /**
     * Aggregate metrics for platform administrators.
     */
    public function getAdminMetrics(): array
    {
        $totalLogs = AiFeedbackLog::count();
        $byType = AiFeedbackLog::selectRaw('feedback_type, count(*) as count')
            ->groupBy('feedback_type')
            ->pluck('count', 'feedback_type')
            ->toArray();

        $byStatus = AiFeedbackLog::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentDisputes = AiFeedbackLog::whereIn('feedback_type', ['moderation_dispute', 'recommendation_inappropriate'])
            ->with('user:id,name,username')
            ->latest()
            ->limit(10)
            ->get();

        $recentCorrections = AiFeedbackLog::whereNotNull('user_correction')
            ->with('user:id,name,username')
            ->latest()
            ->limit(10)
            ->get();

        return [
            'total_feedback' => $totalLogs,
            'by_type' => $byType,
            'by_status' => $byStatus,
            'recent_disputes' => $recentDisputes,
            'recent_corrections' => $recentCorrections,
        ];
    }

    /**
     * Sanitize metadata to ensure private credentials or messages are never retained in logs.
     */
    protected function sanitizeMetadata(array $metadata): array
    {
        $sanitized = [];
        $allowedKeys = [
            'stream_id', 'culture_id', 'event_id', 'community_id',
            'previous_category', 'new_category', 'flag_rule',
            'action', 'relevance_score', 'source', 'language'
        ];

        foreach ($metadata as $key => $value) {
            if (in_array($key, $allowedKeys, true) && is_scalar($value)) {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
