<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZoomAccessTokenService
{
    public function getAccessToken()
    {
        return Cache::remember('zoom_access_token', 3500, function () {
            $response = Http::asForm()
                ->withBasicAuth(
                    config('zoom.client_id'),
                    config('zoom.client_secret')
                )
                ->post('https://zoom.us/oauth/token', [
                    'grant_type' => 'account_credentials',
                    'account_id' => config('zoom.account_id'),
                ]);

            if ($response->failed()) {
                // Log the actual response for debugging
                \Log::error('Zoom token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                // Throw full error
                throw new \Exception('Failed to get Zoom access token: ' . $response->body());
            }

            return $response->json()['access_token'];
        });
    }

}