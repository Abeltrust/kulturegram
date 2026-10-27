<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MetaAI\SystemPrivacyAuditService;
use App\Services\MetaAI\ContentPrivacyProtectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrivacyAuditController extends Controller
{
    /**
     * Display the System Privacy Audit Dashboard
     */
    public function index(SystemPrivacyAuditService $auditService)
    {
        // Cache audit for 15 minutes unless force-refresh requested
        $audit = Cache::remember('kulturegram_system_privacy_audit', 900, function () use ($auditService) {
            return $auditService->runFullAudit();
        });

        return view('admin.privacy-audit', compact('audit'));
    }

    /**
     * Force re-run the System Privacy Audit
     */
    public function run(Request $request, SystemPrivacyAuditService $auditService)
    {
        Cache::forget('kulturegram_system_privacy_audit');
        $audit = $auditService->runFullAudit();
        Cache::put('kulturegram_system_privacy_audit', $audit, 900);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'audit' => $audit,
            ]);
        }

        return redirect()->route('admin.privacy-audit.index')
            ->with('success', 'System privacy audit executed successfully.');
    }

    /**
     * Content Privacy Pre-Scan API
     * Allows front-end content creation forms (posts, comments, streams) to warn
     * users before publishing private PII.
     */
    public function scanContent(Request $request, ContentPrivacyProtectionService $scanner)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $scan = $scanner->scanContent($request->input('text'));

        return response()->json([
            'success' => true,
            'data' => $scan,
        ]);
    }
}
