<?php

namespace App\Services\Xero;
 
use App\Models\XeroConnection;
use Illuminate\Support\Facades\Http;
 
class XeroTokenService
{
    // Returns a valid access_token, refreshing if expired
    public function getValidToken(): string
    {
        $conn = XeroConnection::first();
        if (!$conn) {
            throw new \RuntimeException('Xero is not connected. Go to Settings > Integrations > Connect Xero.');
        }
        if ($conn->isExpired()) {
            $this->refresh($conn);
            $conn->refresh();
        }
        return $conn->access_token;
    }
 
    public function getTenantId(): string
    {
        $conn = XeroConnection::first();
        if (!$conn) throw new \RuntimeException('Xero is not connected.');
        return $conn->tenant_id;
    }
 
    private function refresh(XeroConnection $conn): void
    {
        $res = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $conn->refresh_token,
            'client_id'     => config('services.xero.client_id'),
            'client_secret' => config('services.xero.client_secret'),
        ]);
 
        if ($res->failed()) {
            throw new \RuntimeException('Failed to refresh Xero token: ' . $res->body());
        }
 
        $data = $res->json();
        $conn->update([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'], // Xero rotates refresh tokens
            'expires_at'    => now()->addSeconds($data['expires_in']),
        ]);
    }
}
 
