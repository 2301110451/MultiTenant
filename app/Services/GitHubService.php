<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    /**
     * @return array{success: bool, data: array<int, array<string, mixed>>, error: string|null}
     */
    public function getReleases(): array
    {
        $response = $this->requestWithRetry('get', $this->releasesEndpoint());
        if (! $response['success']) {
            return [
                'success' => false,
                'data' => [],
                'error' => $response['error'],
            ];
        }

        return [
            'success' => true,
            'data' => $response['data'],
            'error' => null,
        ];
    }

    /**
     * @return array{success: bool, data: array<string, mixed>|null, error: string|null}
     */
    public function createRelease(string $version, string $title, string $body): array
    {
        $payload = [
            'tag_name' => $version,
            'name' => $title,
            'body' => $body,
            'draft' => false,
            'prerelease' => false,
            'generate_release_notes' => false,
        ];

        $response = $this->requestWithRetry('post', $this->releasesEndpoint(), $payload);
        if (! $response['success']) {
            return [
                'success' => false,
                'data' => null,
                'error' => $response['error'],
            ];
        }

        return [
            'success' => true,
            'data' => $response['data'],
            'error' => null,
        ];
    }

    /**
     * @return array{success: bool, data: array<string, mixed>|null, error: string|null}
     */
    public function getLatestRelease(): array
    {
        $releases = $this->getReleases();
        if (! $releases['success']) {
            return [
                'success' => false,
                'data' => null,
                'error' => $releases['error'],
            ];
        }

        $latest = $releases['data'][0] ?? null;
        if (! is_array($latest)) {
            return [
                'success' => true,
                'data' => null,
                'error' => null,
            ];
        }

        return [
            'success' => true,
            'data' => $latest,
            'error' => null,
        ];
    }

    /**
     * @return array{success: bool, data: array<string, mixed>|null, error: string|null}
     */
    public function getLatestCommitOnDefaultBranch(): array
    {
        $response = $this->requestWithRetry('get', $this->commitsEndpoint(['per_page' => 1]));
        if (! $response['success']) {
            return [
                'success' => false,
                'data' => null,
                'error' => $response['error'],
            ];
        }

        $first = $response['data'][0] ?? null;
        if (! is_array($first)) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'No commit found on default branch.',
            ];
        }

        return [
            'success' => true,
            'data' => $first,
            'error' => null,
        ];
    }

    /**
     * @return array{success: bool, data: array<string, mixed>|null, error: string|null}
     */
    public function getCommit(string $sha): array
    {
        $sha = trim($sha);
        if ($sha === '') {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Commit SHA is required.',
            ];
        }

        $response = $this->requestWithRetry('get', $this->commitEndpoint($sha));
        if (! $response['success']) {
            return [
                'success' => false,
                'data' => null,
                'error' => $response['error'],
            ];
        }

        if (! is_array($response['data'])) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Commit response is invalid.',
            ];
        }

        return [
            'success' => true,
            'data' => $response['data'],
            'error' => null,
        ];
    }

    private function releasesEndpoint(): string
    {
        $owner = trim((string) config('services.github.owner'));
        $repo = trim((string) config('services.github.repo'));

        return "https://api.github.com/repos/{$owner}/{$repo}/releases";
    }

    /**
     * @param  array<string, scalar>  $query
     */
    private function commitsEndpoint(array $query = []): string
    {
        $owner = trim((string) config('services.github.owner'));
        $repo = trim((string) config('services.github.repo'));
        $base = "https://api.github.com/repos/{$owner}/{$repo}/commits";

        if ($query === []) {
            return $base;
        }

        return $base.'?'.http_build_query($query);
    }

    private function commitEndpoint(string $sha): string
    {
        $owner = trim((string) config('services.github.owner'));
        $repo = trim((string) config('services.github.repo'));

        return "https://api.github.com/repos/{$owner}/{$repo}/commits/{$sha}";
    }

    /**
     * @param  'get'|'post'  $method
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, data: array<string, mixed>|array<int, array<string, mixed>>, error: string|null}
     */
    private function requestWithRetry(string $method, string $url, array $payload = []): array
    {
        $token = trim((string) config('services.github.token'));
        if ($token === '') {
            Log::warning('GitHub API token is missing.');

            return [
                'success' => false,
                'data' => [],
                'error' => 'GitHub token is missing.',
            ];
        }

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $caBundle = trim((string) config('services.github.ca_bundle', ''));
                $sslVerify = (bool) config('services.github.http_ssl_verify', true);
                $verifyOption = $caBundle !== '' ? $caBundle : $sslVerify;

                $request = Http::withToken($token)
                    ->withHeaders([
                        'Accept' => 'application/vnd.github+json',
                        'X-GitHub-Api-Version' => '2022-11-28',
                        'User-Agent' => config('app.name', 'Laravel'),
                    ])
                    ->withOptions([
                        'verify' => $verifyOption,
                    ])
                    ->timeout(12);

                $response = $method === 'post'
                    ? $request->post($url, $payload)
                    : $request->get($url);

                if ($response->successful()) {
                    $json = $response->json();

                    return [
                        'success' => true,
                        'data' => is_array($json) ? $json : [],
                        'error' => null,
                    ];
                }

                $status = $response->status();
                $error = "GitHub request failed with status {$status}.";
                Log::warning('GitHub API request failed.', [
                    'status' => $status,
                    'attempt' => $attempt,
                    'response' => $response->body(),
                ]);

                if ($attempt === 2 || ! in_array($status, [401, 403, 408, 429, 500, 502, 503, 504], true)) {
                    return [
                        'success' => false,
                        'data' => [],
                        'error' => $error,
                    ];
                }
            } catch (ConnectionException|RequestException $exception) {
                Log::error('GitHub API connection/request exception.', [
                    'attempt' => $attempt,
                    'message' => $exception->getMessage(),
                ]);

                if ($attempt === 2) {
                    return [
                        'success' => false,
                        'data' => [],
                        'error' => 'GitHub API is temporarily unavailable.',
                    ];
                }
            } catch (\Throwable $exception) {
                Log::error('Unexpected GitHub API error.', [
                    'attempt' => $attempt,
                    'message' => $exception->getMessage(),
                ]);

                return [
                    'success' => false,
                    'data' => [],
                    'error' => 'Unexpected GitHub integration error.',
                ];
            }
        }

        return [
            'success' => false,
            'data' => [],
            'error' => 'GitHub request could not be completed.',
        ];
    }
}
