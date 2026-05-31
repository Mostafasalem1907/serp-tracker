<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Services\AiAnalysisService;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    /**
     * تشغيل تحليل الذكاء الاصطناعي لمشروع معين
     */
    public function analyze(Request $request, Project $project, AiAnalysisService $aiService)
    {
        try {
            $analysis = $aiService->analyzeProject($project);

            ActivityLog::record('ai.analysis_run', $project, [], [
                'project'       => $project->name,
                'tokens_input'  => $analysis->tokens_input,
                'tokens_output' => $analysis->tokens_output,
            ]);

            return response()->json([
                'success'  => true,
                'analysis' => $analysis->response,
                'tokens'   => $analysis->total_tokens,
                'id'       => $analysis->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * عرض تاريخ التحليلات لمشروع معين
     */
    public function history(Project $project)
    {
        $analyses = $project->aiAnalyses()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('ai.history', compact('project', 'analyses'));
    }
}
