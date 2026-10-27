<?php

namespace App\Services\MetaAI;

use App\Models\Community;
use App\Models\Culture;
use App\Models\CulturalRelationship;
use App\Models\Event;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Support\Collection;

class CulturalKnowledgeGraphService
{
    /**
     * Create or update a cultural relationship edge in the graph.
     */
    public function linkEntities(
        string $sourceType,
        int $sourceId,
        string $targetType,
        int $targetId,
        string $relationshipType,
        string $explanation,
        float $confidence = 0.850,
        bool $verifiedByHuman = false,
        array $metadata = []
    ): CulturalRelationship {
        return CulturalRelationship::updateOrCreate(
            [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'relationship_type' => $relationshipType,
            ],
            [
                'confidence' => $confidence,
                'explanation' => $explanation,
                'verified_by_human' => $verifiedByHuman,
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Get related cultural heritage records with explainable connections.
     */
    public function getRelatedRecords(Culture $culture, int $limit = 4): array
    {
        // 1. Check existing graph edges
        $existingEdges = CulturalRelationship::where(function ($q) use ($culture) {
            $q->where('source_type', 'Culture')
              ->where('source_id', $culture->id)
              ->where('target_type', 'Culture');
        })->orWhere(function ($q) use ($culture) {
            $q->where('target_type', 'Culture')
              ->where('target_id', $culture->id)
              ->where('source_type', 'Culture');
        })->orderByDesc('verified_by_human')
          ->orderByDesc('confidence')
          ->limit($limit)
          ->get();

        $relatedRecords = [];
        $seenIds = [$culture->id];

        foreach ($existingEdges as $edge) {
            $targetId = ($edge->source_id === $culture->id) ? $edge->target_id : $edge->source_id;
            if (in_array($targetId, $seenIds, true)) {
                continue;
            }
            $targetCulture = Culture::where('id', $targetId)->where('status', 'approved')->first();
            if ($targetCulture) {
                $seenIds[] = $targetCulture->id;
                $relatedRecords[] = [
                    'culture' => $targetCulture,
                    'relationship_type' => $edge->relationship_type,
                    'explanation' => $edge->explanation,
                    'confidence' => $edge->confidence,
                    'verified_by_human' => $edge->verified_by_human,
                ];
            }
        }

        // 2. If fewer than limit, discover and materialize grounded relationships
        if (count($relatedRecords) < $limit) {
            $candidates = Culture::where('status', 'approved')
                ->whereNotIn('id', $seenIds)
                ->where(function ($q) use ($culture) {
                    if ($culture->language) {
                        $q->orWhere('language', $culture->language);
                    }
                    if ($culture->region) {
                        $q->orWhere('region', $culture->region);
                    }
                    if ($culture->category) {
                        $q->orWhere('category', $culture->category);
                    }
                })
                ->limit($limit - count($relatedRecords))
                ->get();

            foreach ($candidates as $cand) {
                $reasons = [];
                $relType = 'related_practice';
                $confidence = 0.75;

                if ($cand->language && $cand->language === $culture->language) {
                    $reasons[] = "shares the {$culture->language} language";
                    $relType = 'shares_language';
                    $confidence = 0.90;
                }
                if ($cand->region && $cand->region === $culture->region) {
                    $reasons[] = "originates from the {$culture->region} region";
                    $relType = ($relType === 'shares_language') ? 'shares_language_and_region' : 'shares_region';
                    $confidence = max($confidence, 0.85);
                }
                if ($cand->category && $cand->category === $culture->category) {
                    $reasons[] = "shares {$culture->category} traditions";
                }

                $explanation = "Recommended because this record " . implode(' and ', $reasons) . ".";

                // Persist the discovered relationship into graph
                $this->linkEntities(
                    'Culture',
                    $culture->id,
                    'Culture',
                    $cand->id,
                    $relType,
                    $explanation,
                    $confidence,
                    false
                );

                $relatedRecords[] = [
                    'culture' => $cand,
                    'relationship_type' => $relType,
                    'explanation' => $explanation,
                    'confidence' => $confidence,
                    'verified_by_human' => false,
                ];
            }
        }

        return $relatedRecords;
    }

    /**
     * Connect a livestream discussion to related cultural heritage topics.
     */
    public function indexLiveStreamDiscussion(LiveStream $stream, array $culturalRefs): void
    {
        foreach ($culturalRefs as $ref) {
            if (!empty($ref['id'])) {
                $this->linkEntities(
                    'LiveStream',
                    $stream->id,
                    'Culture',
                    $ref['id'],
                    'discusses_heritage_topic',
                    "Live discussion \"{$stream->title}\" explored topics related to this heritage record.",
                    0.880,
                    false
                );
            }
        }
    }

    /**
     * Identify undocumented topics and unanswered cultural questions across the graph.
     */
    public function discoverUndocumentedGaps(): array
    {
        // 1. Records with high endangerment and no multimedia documentation
        $endangeredGaps = Culture::where('status', 'approved')
            ->whereIn('endangerment_level', ['critically_endangered', 'endangered'])
            ->whereNull('audio_path')
            ->whereNull('video_path')
            ->limit(5)
            ->get(['id', 'name', 'category', 'region', 'language', 'endangerment_level']);

        // 2. Unanswered questions captured from recent community discussions
        $streamsWithQuestions = LiveStream::whereNotNull('unanswered_questions')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'unanswered_questions', 'created_at']);

        $openQuestions = [];
        foreach ($streamsWithQuestions as $st) {
            $questions = (array) ($st->unanswered_questions ?? []);
            foreach ($questions as $q) {
                $openQuestions[] = [
                    'stream_id' => $st->id,
                    'stream_title' => $st->title,
                    'question' => $q,
                    'date' => $st->created_at->toFormattedDateString(),
                ];
            }
        }

        return [
            'undocumented_heritage' => $endangeredGaps,
            'unanswered_questions' => array_slice($openQuestions, 0, 8),
        ];
    }
}
