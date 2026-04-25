<?php

namespace App\Support;

class GitHubFeaturePingV1
{
    public static function info(): array
    {
        return [
            'feature' => 'github-detection-test',
            'version' => 'v1',
            'message' => 'New feature file created from GitHub for pipeline detection.',
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
