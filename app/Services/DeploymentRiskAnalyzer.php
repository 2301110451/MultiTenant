<?php

namespace App\Services;

class DeploymentRiskAnalyzer
{
    /**
     * @param  list<string>  $files
     * @return array{
     *   risk_level: string,
     *   risk_score: int,
     *   change_summary: string,
     *   affected_modules: list<string>,
     *   blast_radius: string
     * }
     */
    public function analyze(array $files): array
    {
        $score = 0;
        $moduleFlags = [];
        $reasons = [];

        foreach ($files as $file) {
            $file = trim((string) $file);
            if ($file === '') {
                continue;
            }

            if ($this->startsWith($file, 'database/migrations/')) {
                $score += 45;
                $reasons[] = 'Database migration changes detected.';
                $moduleFlags['database'] = true;
            } elseif ($file === 'composer.lock' || $file === 'package-lock.json') {
                $score += 40;
                $reasons[] = 'Dependency lockfile changes detected.';
                $moduleFlags['dependencies'] = true;
            } elseif ($this->startsWith($file, 'app/Http/Middleware/')) {
                $score += 30;
                $reasons[] = 'Request middleware flow changed.';
                $moduleFlags['middleware'] = true;
            } elseif ($this->startsWith($file, 'app/Models/')) {
                $score += 22;
                $moduleFlags['models'] = true;
            } elseif ($this->startsWith($file, 'app/Http/Controllers/')) {
                $score += 18;
                $moduleFlags['controllers'] = true;
            } elseif ($this->startsWith($file, 'app/Services/')) {
                $score += 18;
                $moduleFlags['services'] = true;
            } elseif ($this->startsWith($file, 'routes/')) {
                $score += 15;
                $moduleFlags['routing'] = true;
            } elseif ($this->startsWith($file, 'resources/views/') || $this->startsWith($file, 'resources/css/')) {
                $score += 5;
                $moduleFlags['ui'] = true;
            } else {
                $score += 8;
            }

            if (str_contains($file, '/Central/')) {
                $moduleFlags['central'] = true;
            }
            if (str_contains($file, '/Tenant/')) {
                $moduleFlags['tenant'] = true;
            }
        }

        $riskScore = max(0, min(100, $score));
        $riskLevel = $riskScore >= 60 ? 'high' : ($riskScore >= 20 ? 'medium' : 'low');
        $affectedModules = array_values(array_keys($moduleFlags));

        $changeSummary = $reasons !== []
            ? implode(' ', array_values(array_unique($reasons)))
            : 'General code changes detected.';

        return [
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'change_summary' => $changeSummary,
            'affected_modules' => $affectedModules,
            'blast_radius' => $this->blastRadius($riskLevel, $affectedModules),
        ];
    }

    /**
     * @param  list<string>  $modules
     */
    private function blastRadius(string $riskLevel, array $modules): string
    {
        if ($riskLevel === 'high') {
            return 'system_wide';
        }

        if (in_array('central', $modules, true) && in_array('tenant', $modules, true)) {
            return 'cross_context';
        }

        if ($riskLevel === 'medium') {
            return 'module_scoped';
        }

        return 'limited';
    }

    private function startsWith(string $value, string $prefix): bool
    {
        return str_starts_with($value, $prefix);
    }
}
