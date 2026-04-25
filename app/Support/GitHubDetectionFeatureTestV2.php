<?php

namespace App\Support;

class GitHubDetectionFeatureTestV2
{
    public static function payload(): array
    {
        return [
            'feature' => 'github-create-file-detection',
            'version' => 'v2',
            'status' => 'testing',
            'message' => 'If this file appears in Deployments changed files list, detection works.',
        ];
    }
}
