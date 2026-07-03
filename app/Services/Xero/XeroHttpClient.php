<?php

namespace App\Services\Xero;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XeroHttpClient
{
    private const BASE = 'https://api.xero.com';

    public function __construct(private XeroTokenService $tokenService) {}

    // ─────────────────────────────────────────────────────────────────
    // GET — no retry, throw immediately on any error
    // ─────────────────────────────────────────────────────────────────
    public function get(string $path, array $query = []): array
    {
        Log::debug("[XeroHttp] GET {$path}", ['query' => $query]);

        $res = $this->request()->get(self::BASE . $path, $query);

        $this->assertOk($res, 'GET', $path);

        return $res->json();
    }

    // ─────────────────────────────────────────────────────────────────
    // POST — no retry, throw immediately on any error
    // ─────────────────────────────────────────────────────────────────
    public function post(string $path, array $payload): array
    {
        Log::debug("[XeroHttp] POST {$path}", ['payload' => $payload]);

        $res = $this->request()->asJson()->post(self::BASE . $path, $payload);

        $this->assertOk($res, 'POST', $path);

        return $res->json();
    }

    // ─────────────────────────────────────────────────────────────────
    // POST — returns data + rate limit headers together
    // Used by ProcessXeroPayrollJob to track limits without extra calls
    // ─────────────────────────────────────────────────────────────────
    public function postWithHeaders(string $path, array $payload): array
    {
        Log::debug("[XeroHttp] POST {$path}", ['payload' => $payload]);

        $res = $this->request()->asJson()->post(self::BASE . $path, $payload);

        $this->assertOk($res, 'POST', $path);

        return [
            'data'          => $res->json(),
            'day_remaining' => (int) ($res->header('X-DayLimit-Remaining')    ?? 9999),
            'min_remaining' => (int) ($res->header('X-MinLimit-Remaining')    ?? 60),
            'problem'       => $res->header('X-Rate-Limit-Problem')           ?? null,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // GET with extra headers
    // ─────────────────────────────────────────────────────────────────
    public function getWithHeaders(string $path, array $query = [], array $headers = []): array
    {
        Log::debug("[XeroHttp] GET (with headers) {$path}", ['query' => $query, 'headers' => $headers]);

        $res = $this->request()
            ->withHeaders($headers)
            ->get(self::BASE . $path, $query);

        $this->assertOk($res, 'GET', $path);

        return $res->json();
    }

    // ─────────────────────────────────────────────────────────────────
    // PUT
    // ─────────────────────────────────────────────────────────────────
    public function put(string $path, array $payload): array
    {
        Log::debug("[XeroHttp] PUT {$path}", ['payload' => $payload]);

        $res = $this->request()->asJson()->put(self::BASE . $path, $payload);

        $this->assertOk($res, 'PUT', $path);

        return $res->json();
    }

    // ─────────────────────────────────────────────────────────────────
    // DELETE — no retry, throw immediately on any error
    // ─────────────────────────────────────────────────────────────────
    public function delete(string $path): array
    {
        Log::debug("[XeroHttp] DELETE {$path}");

        $res = $this->request()->delete(self::BASE . $path);

        $this->assertOk($res, 'DELETE', $path);

        return $res->json() ?? [];
    }

    // ─────────────────────────────────────────────────────────────────
    // Raw response — used to read rate limit headers without parsing
    // ─────────────────────────────────────────────────────────────────
    public function getRawResponse(string $path, array $query = []): Response
    {
        return $this->request()->get(self::BASE . $path, $query);
    }

    // ─────────────────────────────────────────────────────────────────
    // Internal: attach auth + tenant headers to every request
    // ─────────────────────────────────────────────────────────────────
    private function request(): PendingRequest
    {
        return Http::withToken($this->tokenService->getValidToken())
            ->withHeaders([
                'Xero-Tenant-Id' => $this->tokenService->getTenantId(),
                'Accept'         => 'application/json',
            ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Throw a clear exception on any non-2xx response
    // ─────────────────────────────────────────────────────────────────
    private function assertOk(Response $res, string $method, string $path): void
    {
        if ($res->failed()) {
            $body = $res->body();
            Log::error("[XeroHttp] {$method} {$path} → HTTP {$res->status()}", ['body' => $body]);
            throw new \RuntimeException(
                "Xero API [{$method} {$path}] HTTP {$res->status()}: {$body}"
            );
        }
    }
}