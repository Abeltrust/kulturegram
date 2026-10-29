<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name');
            return $indexes->contains($indexName);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Run the migrations to add high-throughput composite indexes for high concurrency.
     */
    public function up(): void
    {
        // 1. Posts table indexes (Timeline feed & profile feeds)
        Schema::table('posts', function (Blueprint $table) {
            if (!$this->hasIndex('posts', 'idx_posts_privacy_created')) {
                $table->index(['privacy', 'created_at'], 'idx_posts_privacy_created');
            }
            if (!$this->hasIndex('posts', 'idx_posts_user_created')) {
                $table->index(['user_id', 'created_at'], 'idx_posts_user_created');
            }
        });

        // 2. Messages table indexes (Fast polling by conversation & id, unread count checks)
        Schema::table('messages', function (Blueprint $table) {
            if (!$this->hasIndex('messages', 'idx_messages_conv_id')) {
                $table->index(['conversation_id', 'id'], 'idx_messages_conv_id');
            }
            if (!$this->hasIndex('messages', 'idx_messages_conv_user_status')) {
                $table->index(['conversation_id', 'user_id', 'status'], 'idx_messages_conv_user_status');
            }
        });

        // 3. Resonances (Comments / reactions lookups)
        Schema::table('resonances', function (Blueprint $table) {
            if (!$this->hasIndex('resonances', 'idx_resonances_post_parent')) {
                $table->index(['post_id', 'parent_id', 'created_at'], 'idx_resonances_post_parent');
            }
        });

        // 4. Notifications (Instant unread count & user inbox queries)
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->hasIndex('notifications', 'idx_notifications_user_read')) {
                $table->index(['user_id', 'read', 'created_at'], 'idx_notifications_user_read');
            }
        });

        // 5. Live Stream Messages (Chat polling by stream and message id)
        if (Schema::hasTable('live_stream_messages')) {
            Schema::table('live_stream_messages', function (Blueprint $table) {
                if (!$this->hasIndex('live_stream_messages', 'idx_stream_msg_stream_id')) {
                    $table->index(['live_stream_id', 'id'], 'idx_stream_msg_stream_id');
                }
            });
        }

        // 6. Taps (Fast check if user already tapped a model)
        if (Schema::hasTable('taps')) {
            Schema::table('taps', function (Blueprint $table) {
                if (!$this->hasIndex('taps', 'idx_taps_user_model')) {
                    $table->index(['user_id', 'tappable_type', 'tappable_id'], 'idx_taps_user_model');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if ($this->hasIndex('posts', 'idx_posts_privacy_created')) {
                $table->dropIndex('idx_posts_privacy_created');
            }
            if ($this->hasIndex('posts', 'idx_posts_user_created')) {
                $table->dropIndex('idx_posts_user_created');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if ($this->hasIndex('messages', 'idx_messages_conv_id')) {
                $table->dropIndex('idx_messages_conv_id');
            }
            if ($this->hasIndex('messages', 'idx_messages_conv_user_status')) {
                $table->dropIndex('idx_messages_conv_user_status');
            }
        });

        Schema::table('resonances', function (Blueprint $table) {
            if ($this->hasIndex('resonances', 'idx_resonances_post_parent')) {
                $table->dropIndex('idx_resonances_post_parent');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if ($this->hasIndex('notifications', 'idx_notifications_user_read')) {
                $table->dropIndex('idx_notifications_user_read');
            }
        });

        if (Schema::hasTable('live_stream_messages')) {
            Schema::table('live_stream_messages', function (Blueprint $table) {
                if ($this->hasIndex('live_stream_messages', 'idx_stream_msg_stream_id')) {
                    $table->dropIndex('idx_stream_msg_stream_id');
                }
            });
        }

        if (Schema::hasTable('taps')) {
            Schema::table('taps', function (Blueprint $table) {
                if ($this->hasIndex('taps', 'idx_taps_user_model')) {
                    $table->dropIndex('idx_taps_user_model');
                }
            });
        }
    }
};
