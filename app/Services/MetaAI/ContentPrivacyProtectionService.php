<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Log;

class ContentPrivacyProtectionService
{
    /**
     * Scan arbitrary text for potential PII (Personally Identifiable Information)
     * before publication or external transmission.
     */
    public function scanContent(string $text): array
    {
        $clean = trim($text);
        if (empty($clean)) {
            return [
                'has_pii' => false,
                'risk_level' => 'low',
                'pii_types' => [],
                'masked_text' => $text,
                'warnings' => [],
                'recommendation' => 'Content contains no detected personal identifiers.',
            ];
        }

        $detectedTypes = [];
        $warnings = [];
        $masked = $clean;

        // 1. Email Addresses
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $clean, $matches)) {
            $detectedTypes[] = 'email_address';
            $warnings[] = 'Email address detected. Publishing private email addresses exposes you to unsolicited spam or phishing.';
            $masked = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[REDACTED_EMAIL]', $masked);
        }

        // 2. Phone Numbers (International & Local formats, including Nigerian +234/080...)
        if (preg_match_all('/(?:\+?234|0)[789][01]\d{8}\b|\b(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/', $clean, $matches)) {
            $detectedTypes[] = 'phone_number';
            $warnings[] = 'Personal telephone number detected. To protect your real-world privacy, do not share phone numbers in public cultural spaces.';
            $masked = preg_replace('/(?:\+?234|0)[789][01]\d{8}\b|\b(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/', '[REDACTED_PHONE]', $masked);
        }

        // 3. National IDs (e.g. Nigerian National Identity Number 11 digits, US SSN 9 digits)
        if (preg_match_all('/(?:\bNIN[:\s]*)?\b\d{11}\b|\b\d{3}-\d{2}-\d{4}\b/i', $clean, $matches)) {
            // Filter out common false positives like timestamps
            $isId = false;
            foreach ($matches[0] as $match) {
                if (stripos($clean, 'NIN') !== false || strlen(preg_replace('/\D/', '', $match)) === 11) {
                    $isId = true;
                    break;
                }
            }
            if ($isId) {
                $detectedTypes[] = 'government_id';
                $warnings[] = 'Potential government identity number (e.g., NIN or SSN) detected. High identity theft risk.';
                $masked = preg_replace('/(?:\bNIN[:\s]*)?\b\d{11}\b|\b\d{3}-\d{2}-\d{4}\b/i', '[REDACTED_GOV_ID]', $masked);
            }
        }

        // 4. API Keys & Passwords
        if (preg_match_all('/(?:password|api[_-]?key|secret|token)\s*[:=]\s*[^\s]{6,}/i', $clean, $matches)) {
            $detectedTypes[] = 'credentials';
            $warnings[] = 'Potential software credentials, API keys, or passwords detected in plain text.';
            $masked = preg_replace('/(?:password|api[_-]?key|secret|token)\s*[:=]\s*[^\s]{6,}/i', '[REDACTED_CREDENTIALS]', $masked);
        }

        // 5. Credit/Debit Card Numbers
        if (preg_match_all('/\b(?:\d{4}[-\s]?){3}\d{4}\b/', $clean, $matches)) {
            $detectedTypes[] = 'financial_card';
            $warnings[] = 'Potential credit or payment card number detected. Do not share financial information publicly.';
            $masked = preg_replace('/\b(?:\d{4}[-\s]?){3}\d{4}\b/', '[REDACTED_CARD]', $masked);
        }

        $detectedTypes = array_values(array_unique($detectedTypes));
        $hasPii = count($detectedTypes) > 0;

        $riskLevel = 'low';
        if (in_array('credentials', $detectedTypes, true) || in_array('government_id', $detectedTypes, true) || in_array('financial_card', $detectedTypes, true)) {
            $riskLevel = 'high';
        } elseif ($hasPii) {
            $riskLevel = 'medium';
        }

        $recommendation = $hasPii
            ? 'We recommend removing or masking the flagged private details before sharing.'
            : 'Safe for public sharing.';

        return [
            'has_pii' => $hasPii,
            'risk_level' => $riskLevel,
            'pii_types' => $detectedTypes,
            'detected_types' => $detectedTypes,
            'masked_text' => $masked,
            'masked_preview' => $masked,
            'warnings' => $warnings,
            'recommendation' => $recommendation,
            'advice' => $recommendation,
        ];
    }
}
