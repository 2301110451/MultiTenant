<?php

namespace App\Services;

use App\Models\CentralUser;
use App\Models\DeploymentSnapshot;

class DeploymentSnapshotService
{
    public function createSnapshot(
        string $version,
        ?CentralUser $actor,
        ?string $artifactDigest = null,
        ?string $artifactUri = null,
        ?string $codeReference = null,
        ?string $lockfileHash = null,
        ?string $configHash = null,
        array $metadata = []
    ): DeploymentSnapshot {
        return DeploymentSnapshot::query()->create([
            'version' => $version,
            'artifact_digest' => $artifactDigest,
            'artifact_uri' => $artifactUri,
            'code_reference' => $codeReference,
            'lockfile_hash' => $lockfileHash,
            'config_hash' => $configHash,
            'metadata' => $metadata,
            'created_by' => $actor?->id,
            'created_at_snapshot' => now(),
            'is_stable' => false,
        ]);
    }
}
