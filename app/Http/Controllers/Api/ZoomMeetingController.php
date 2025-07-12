<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZoomMeeting;
use App\Services\ZoomService;
use Illuminate\Http\Request;

class ZoomMeetingController extends Controller
{
    protected $zoom;

    public function __construct(ZoomService $zoom)
    {
        $this->zoom = $zoom;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'topic' => 'required|string',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
            'agenda' => 'nullable|string',
        ]);

        $user = auth()->user();

        $zoomMeeting = $this->zoom->createMeeting($user->email, $data);

        $meeting = ZoomMeeting::create([
            'user_id' => $user->id,
            'topic' => $data['topic'],
            'agenda' => $data['agenda'] ?? '',
            'zoom_meeting_id' => $zoomMeeting['id'],
            'zoom_start_url' => $zoomMeeting['start_url'],
            'zoom_join_url' => $zoomMeeting['join_url'],
            'start_time' => $data['start_time'],
            'duration' => $data['duration'],
        ]);

        return response()->json([
            'message' => 'Zoom meeting created successfully',
            'data' => $meeting
        ]);
    }
}
