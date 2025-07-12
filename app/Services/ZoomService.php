<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ZoomService
{
    protected $accessToken;

    public function __construct(ZoomAccessTokenService $authService)
    {
        $this->accessToken = $authService->getAccessToken();
    }

    public function createMeeting($userEmail, $data)
    {
        $response = Http::withToken($this->accessToken)
            ->post(config('zoom.base_url') . "users/me/meetings", [
                'topic' => $data['topic'],
                'type' => 2, // scheduled
                'start_time' => Carbon::parse($data['start_time'])->toIso8601String(),
                'duration' => $data['duration'],
                'timezone' => 'UTC',
                'agenda' => $data['agenda'] ?? '',
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'waiting_room' => false,
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception('Zoom API failed: ' . $response->body());
        }

        return $response->json();
    }
}