<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class MicrosoftGraphService
{
    protected $tokenUrl;
    protected $clientId;
    protected $clientSecret;
    protected $tenantId;

    public function __construct()
    {
        $this->tokenUrl = 'https://login.microsoftonline.com/' . env('MICROSOFT_TENANT_ID') . '/oauth2/v2.0/token';
        $this->clientId = env('MICROSOFT_CLIENT_ID');
        $this->clientSecret = env('MICROSOFT_CLIENT_SECRET');
        $this->tenantId = env('MICROSOFT_TENANT_ID');
    }

    public function getToken()
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        \Log::error('Token request failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \Exception('Failed to obtain access token');
    }

    public function createSubscription()
    {
        $token = $this->getToken();

        // Your subscription creation logic here using the obtained token
    }
}
