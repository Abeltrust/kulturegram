<?php

namespace Database\Seeders;

use App\Models\CulturalClass;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CulturalClassSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::first();
        if (!$instructor) {
            $instructor = User::factory()->create([
                'name' => 'Mallam Aminu Kano',
                'email' => 'aminu@kulturegram.com',
            ]);
        }

        // 1. Hausa Cultural Greeting & Etiquette Masterclass
        $hausaSubs = [
            [
                'start' => 0.0,
                'end' => 4.5,
                'text' => 'Barka da warhaka! Welcome to our cultural class on traditional Hausa greetings and respect.',
                'source_phrase' => 'Good day and welcome',
                'target_phrase' => 'Barka da warhaka',
                'cultural_note' => 'Warm universal greeting in Hausa culture across the Sahel.',
            ],
            [
                'start' => 4.8,
                'end' => 9.2,
                'text' => 'In Hausa culture, when meeting an elder in the morning, you say "Ina kwana", which means "How did you sleep?".',
                'source_phrase' => 'Good morning / How was your sleep?',
                'target_phrase' => 'Ina kwana',
                'cultural_note' => 'Customary to bow head slightly or kneel gently when greeting elders.',
            ],
            [
                'start' => 9.5,
                'end' => 14.0,
                'text' => 'The elder will reply with warmth and dignity: "Lafiya lau", which translates to "In health and tranquility".',
                'source_phrase' => 'Fine / In peace',
                'target_phrase' => 'Lafiya lau',
                'cultural_note' => 'Reflects ancestral emphasis on community well-being and peace.',
            ],
            [
                'start' => 14.3,
                'end' => 19.5,
                'text' => 'When visiting a marketplace or entering someone\'s compound, call out "Barka da zuwa" to welcome them.',
                'source_phrase' => 'Welcome',
                'target_phrase' => 'Barka da zuwa',
                'cultural_note' => 'Hospitality is paramount in northern heritage.',
            ],
            [
                'start' => 19.8,
                'end' => 25.0,
                'text' => 'When expressing deep gratitude, we say "Nagode kwarai", meaning "I thank you very much indeed".',
                'source_phrase' => 'Thank you very much',
                'target_phrase' => 'Nagode kwarai',
                'cultural_note' => 'Placing the right hand gently on the chest reinforces sincerity.',
            ],
        ];

        $hausaVocab = [
            [
                'source' => 'Good day / Welcome',
                'target' => 'Barka da warhaka',
                'language' => 'Hausa',
                'pronunciation' => 'BAHR-kah dah wahr-HAH-kah',
                'context' => 'General respectful greeting used throughout the daytime.',
            ],
            [
                'source' => 'Good morning (How did you sleep?)',
                'target' => 'Ina kwana',
                'language' => 'Hausa',
                'pronunciation' => 'EE-nah KWAH-nah',
                'context' => 'Primary morning salutation. Shows immediate care for household health.',
            ],
            [
                'source' => 'Very well / In peace',
                'target' => 'Lafiya lau',
                'language' => 'Hausa',
                'pronunciation' => 'Lah-FEE-yah LAH-oo',
                'context' => 'Standard dignified response conveying peace and wellness.',
            ],
            [
                'source' => 'Welcome',
                'target' => 'Barka da zuwa',
                'language' => 'Hausa',
                'pronunciation' => 'BAHR-kah dah ZOO-wah',
                'context' => 'Customary welcome offered to travelers, guests, and community visitors.',
            ],
            [
                'source' => 'Thank you very much',
                'target' => 'Nagode kwarai',
                'language' => 'Hausa',
                'pronunciation' => 'Nah-GOH-day KWAH-rye',
                'context' => 'Deep expression of appreciation with humility.',
            ],
        ];

        $hausaVtt = "WEBVTT - Hausa Greetings\n\n1\n00:00:00.000 --> 00:00:04.500\nBarka da warhaka! Welcome to our cultural class on traditional Hausa greetings.\n\n2\n00:00:04.800 --> 00:00:09.200\nIn Hausa culture, when meeting an elder, say \"Ina kwana\", which means \"Good morning\".\n\n3\n00:00:09.500 --> 00:00:14.000\nThe elder will reply: \"Lafiya lau\", which means \"In health and peace\".\n\n4\n00:00:14.300 --> 00:00:19.500\nWhen visiting a compound or meeting a guest, call out \"Barka da zuwa\" (Welcome).\n\n5\n00:00:19.800 --> 00:00:25.000\nTo express sincere gratitude, say \"Nagode kwarai\" (Thank you very much).\n";

        $class1 = CulturalClass::updateOrCreate(
            ['slug' => 'hausa-everyday-greetings-and-cultural-etiquette'],
            [
                'instructor_id' => $instructor->id,
                'title' => 'Hausa Everyday Greetings & Cultural Etiquette',
                'description' => 'Master the essential greetings, gestures, and respect traditions of the Hausa language. Learn how to address elders, greet friends, and navigate daily community interactions with dignity and warmth.',
                'cultural_focus' => 'Hausa Heritage & Northern Sahelian Etiquette',
                'target_language' => 'Hausa',
                'source_language' => 'English',
                'level' => 'beginner',
                'price_type' => 'free',
                'price' => 0.00,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&q=80&w=1200',
                'virtual_background_url' => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&q=80&w=1200',
                'duration_seconds' => 75,
                'transcript' => 'Barka da warhaka! Welcome to our cultural class on traditional Hausa greetings and respect. In Hausa culture, when meeting an elder in the morning, you say "Ina kwana", which means "How did you sleep?". The elder will reply with warmth and dignity: "Lafiya lau", which translates to "In health and tranquility". When visiting a marketplace or entering someone\'s compound, call out "Barka da zuwa" to welcome them. When expressing deep gratitude, we say "Nagode kwarai", meaning "I thank you very much indeed".',
                'subtitles_vtt' => $hausaVtt,
                'timed_subtitles' => $hausaSubs,
                'bilingual_vocabulary' => $hausaVocab,
                'ai_summary' => 'Comprehensive introductory lesson on Hausa salutations, vocal inflections, and bodily respect customs.',
                'ai_enhancements' => [
                    'key_takeaways' => [
                        'Elders are greeted with gentle head tilt or knee bend to symbolize deference.',
                        '"Lafiya" (peace/health) is the core philosophical pillar in every Hausa response.',
                        'Greetings in the market build trust before any commercial trade begins.',
                    ],
                    'vocabulary_count' => 5,
                    'subtitles_count' => 5,
                ],
                'views_count' => 142,
                'enrollments_count' => 38,
                'is_published' => true,
                'shared_to_timeline' => true,
            ]
        );

        // Cross-post to timeline
        Post::updateOrCreate(
            ['chapter' => 'Cultural Class', 'user_id' => $instructor->id],
            [
                'content' => "🎓 New Cultural Class: Hausa Everyday Greetings & Cultural Etiquette\n\nLearn authentic Hausa greetings, respectful conversational gestures, and living traditions with synchronized AI subtitles. Watch now in the Educational Hub!",
                'image' => $class1->thumbnail_url,
                'privacy' => 'public',
                'type' => 'text',
                'chapter' => 'Cultural Class',
                'tags' => ['education', 'cultural-class', 'hausa', 'living-heritage'],
            ]
        );

        // 2. Yoruba Proverbs & Omoluabi Ethics Masterclass (Paid)
        $yorubaSubs = [
            [
                'start' => 0.0,
                'end' => 5.0,
                'text' => 'E ku aaro! Welcome to our Yoruba cultural class on Omoluabi ethics and proverbs.',
                'source_phrase' => 'Good morning with honor',
                'target_phrase' => 'E ku aaro',
                'cultural_note' => 'Prefix "E" is the honorific mark of respect for elders and teachers.',
            ],
            [
                'start' => 5.3,
                'end' => 11.0,
                'text' => 'In Yoruba philosophy, an "Omoluabi" is a person of noble character, good breeding, and unwavering integrity.',
                'source_phrase' => 'Person of noble character',
                'target_phrase' => 'Omoluabi',
                'cultural_note' => 'The ultimate ideal of personhood in Yoruba civilization.',
            ],
            [
                'start' => 11.4,
                'end' => 18.0,
                'text' => 'When you greet someone older, you say "E pele" for gentle comfort, or "E seun" to offer sincere thanks.',
                'source_phrase' => 'Thank you / Greetings',
                'target_phrase' => 'E seun / E pele',
                'cultural_note' => 'Posture matters: bowing or kneeling demonstrates humility.',
            ],
        ];

        $yorubaVocab = [
            [
                'source' => 'Good morning (Respectful)',
                'target' => 'E ku aaro',
                'language' => 'Yoruba',
                'pronunciation' => 'Eh koo AH-roh',
                'context' => 'Honorific morning greeting used with parents, elders, and leaders.',
            ],
            [
                'source' => 'Person of noble character',
                'target' => 'Omoluabi',
                'language' => 'Yoruba',
                'pronunciation' => 'Oh-moh-loo-AH-bee',
                'context' => 'Embodiment of honesty, modesty, hard work, and communal love.',
            ],
            [
                'source' => 'Thank you very much',
                'target' => 'E seun pupo',
                'language' => 'Yoruba',
                'pronunciation' => 'Eh shay-OON poo-POH',
                'context' => 'Warm appreciation conveying grateful reverence.',
            ],
        ];

        $yorubaVtt = "WEBVTT - Yoruba Ethics\n\n1\n00:00:00.000 --> 00:00:05.000\nE ku aaro! Welcome to our Yoruba cultural class on Omoluabi ethics.\n\n2\n00:00:05.300 --> 00:00:11.000\nIn Yoruba philosophy, an \"Omoluabi\" is a person of noble character and integrity.\n\n3\n00:00:11.400 --> 00:00:18.000\nWhen greeting an elder, say \"E seun\" to offer sincere thanks.\n";

        CulturalClass::updateOrCreate(
            ['slug' => 'yoruba-proverbs-and-omoluabi-cultural-ethics'],
            [
                'instructor_id' => $instructor->id,
                'title' => 'Yoruba Proverbs & Omoluabi Cultural Ethics',
                'description' => 'Deep dive into ancestral Yoruba philosophical principles, proverbs (Owe), and the celebrated concept of Omoluabi. Includes interactive phonetic guides and cultural moral lessons.',
                'cultural_focus' => 'Yoruba Moral Philosophy & Ancestral Proverbs',
                'target_language' => 'Yoruba',
                'source_language' => 'English',
                'level' => 'intermediate',
                'price_type' => 'paid',
                'price' => 7.50,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?auto=format&fit=crop&q=80&w=1200',
                'virtual_background_url' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?auto=format&fit=crop&q=80&w=1200',
                'duration_seconds' => 90,
                'transcript' => 'E ku aaro! Welcome to our Yoruba cultural class on Omoluabi ethics and proverbs. In Yoruba philosophy, an "Omoluabi" is a person of noble character, good breeding, and unwavering integrity. When you greet someone older, you say "E pele" for gentle comfort, or "E seun" to offer sincere thanks.',
                'subtitles_vtt' => $yorubaVtt,
                'timed_subtitles' => $yorubaSubs,
                'bilingual_vocabulary' => $yorubaVocab,
                'ai_summary' => 'Masterclass exploring character, honorific language structures, and traditional proverb interpretations.',
                'ai_enhancements' => [
                    'key_takeaways' => [
                        'Omoluabi ethics prioritize communal good over selfish pursuit.',
                        'Prefix "E" creates an atmosphere of cultural sanctity and mutual respect.',
                    ],
                    'vocabulary_count' => 3,
                    'subtitles_count' => 3,
                ],
                'views_count' => 95,
                'enrollments_count' => 19,
                'is_published' => true,
                'shared_to_timeline' => true,
            ]
        );
    }
}
