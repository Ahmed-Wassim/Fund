<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Offer;
use App\Models\Business;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $user = auth()->user();
        $stats = [];

        if ($user->user_type === 'owner') {
            $stats = [
                'total_businesses' => Business::where('user_id', $user->id)->count(),
                'active_businesses' => Business::where('user_id', $user->id)->active()->count(),
                'total_offers_received' => Offer::whereHas('business', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
                'pending_offers' => Offer::whereHas('business', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->pending()->count(),
                'completed_deals' => Deal::whereHas('business', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
                'total_funding_received' => Deal::whereHas('business', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->sum('final_amount'),
            ];
        } else {
            $stats = [
                'total_offers_made' => Offer::where('investor_id', $user->id)->count(),
                'pending_offers' => Offer::where('investor_id', $user->id)->pending()->count(),
                'accepted_offers' => Offer::where('investor_id', $user->id)->accepted()->count(),
                'wishlist_count' => Wishlist::where('user_id', $user->id)->count(),
                'completed_investments' => Deal::where('investor_id', $user->id)->count(),
                'total_invested' => Deal::where('investor_id', $user->id)->sum('final_amount'),
            ];
        }

        return response()->json($stats);
    }

    public function recentActivity()
    {
        $user = auth()->user();
        $activities = [];

        if ($user->user_type === 'owner') {
            // Recent offers on user's businesses
            $recentOffers = Offer::with(['investor', 'business'])
                ->whereHas('business', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->latest()
                ->limit(10)
                ->get();

            foreach ($recentOffers as $offer) {
                $activities[] = [
                    'type' => 'offer_received',
                    'message' => "{$offer->investor->name} made an offer on {$offer->business->business_name}",
                    'amount' => $offer->offered_amount,
                    'percentage' => $offer->requested_percentage,
                    'created_at' => $offer->created_at,
                    'offer_id' => $offer->id,
                ];
            }
        } else {
            // Recent offers made by investor
            $recentOffers = Offer::with(['business.user'])
                ->where('investor_id', $user->id)
                ->latest()
                ->limit(10)
                ->get();

            foreach ($recentOffers as $offer) {
                $activities[] = [
                    'type' => 'offer_made',
                    'message' => "You made an offer on {$offer->business->business_name}",
                    'amount' => $offer->offered_amount,
                    'percentage' => $offer->requested_percentage,
                    'status' => $offer->status,
                    'created_at' => $offer->created_at,
                    'offer_id' => $offer->id,
                ];
            }
        }

        // Sort activities by date
        usort($activities, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return response()->json(array_slice($activities, 0, 10));
    }
}
