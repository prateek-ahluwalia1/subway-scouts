<?php

namespace App\Http\Controllers;

use App\Models\XeroConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class XeroAuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // GET /xero/connect  →  redirect user to Xero login page
    // ─────────────────────────────────────────────────────────────────
    public function redirect()
    {
        $url = 'https://login.xero.com/identity/connect/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.xero.client_id'),
            'redirect_uri'  => config('services.xero.redirect_uri'),
            'scope'         => implode(' ', [
                'openid',
                'profile',
                'email',
                'offline_access',
                'payroll.employees',
                'payroll.payruns',
                'payroll.payslip',
                'payroll.timesheets',
                'payroll.settings',
            ]),
            'state' => csrf_token(),
        ]);

        return redirect($url);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /xero/callback  →  exchange code for tokens, store them
    // ─────────────────────────────────────────────────────────────────
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/settings/integrations')
                ->with('error', 'Xero connection was cancelled.');
        }

        // Exchange code for tokens
        $tokenRes = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
            'redirect_uri'  => config('services.xero.redirect_uri'),
            'client_id'     => config('services.xero.client_id'),
            'client_secret' => config('services.xero.client_secret'),
        ]);

        if ($tokenRes->failed()) {
            return redirect('/settings/integrations')
                ->with('error', 'Failed to get Xero tokens: ' . $tokenRes->body());
        }

        $tokens = $tokenRes->json();

        // Get the Xero organisation (tenant) this token belongs to
        $tenantsRes = Http::withToken($tokens['access_token'])
            ->get('https://api.xero.com/connections');

        if ($tenantsRes->failed() || empty($tenantsRes->json())) {
            return redirect('/settings/integrations')
                ->with('error', 'No Xero organisation found. Please connect a Xero organisation.');
        }

        $tenant = $tenantsRes->json()[0];

        XeroConnection::updateOrCreate(
            ['tenant_id' => $tenant['tenantId']],
            [
                'tenant_name'   => $tenant['tenantName'],
                'access_token'  => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_at'    => now()->addSeconds($tokens['expires_in'] - 60),
            ]
        );

        return redirect('/settings/integrations')
            ->with('success', 'Xero connected: ' . $tenant['tenantName']);
    }

    // ─────────────────────────────────────────────────────────────────
    // DELETE /xero/disconnect
    // ─────────────────────────────────────────────────────────────────
    public function disconnect()
    {
        XeroConnection::truncate();

        return redirect('/settings/integrations')
            ->with('success', 'Xero disconnected successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /api/xero/status  — Angular calls this on page load
    // ─────────────────────────────────────────────────────────────────
    public function status()
    {
        $conn = XeroConnection::first();

        return response()->json([
            'connected'   => (bool) $conn,
            'tenant_name' => $conn?->tenant_name,
            'expires_at'  => $conn?->expires_at,
        ]);
    }
}