<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    public function index()
    {
        $deals = Deal::with(['business.user', 'business.category', 'investor', 'acceptedOffer'])
            ->where(function ($query) {
                $query->whereHas('business', function ($q) {
                    $q->where('user_id', Auth::id());
                })->orWhere('investor_id', Auth::id());
            })
            ->latest('deal_date')
            ->get();

        return response()->json($deals);
    }

    public function recent()
    {
        $deals = Deal::with(['business.user', 'business.category', 'investor'])
            ->recent(30)
            ->active()
            ->latest('deal_date')
            ->limit(10)
            ->get();

        return response()->json($deals);
    }

    public function highest()
    {
        $deals = Deal::with(['business.user', 'business.category', 'investor'])
            ->active()
            ->highestAmount()
            ->limit(10)
            ->get();

        return response()->json($deals);
    }

    public function show(Deal $deal)
    {
        // Check if user is involved in this deal
        if ($deal->business->user_id !== Auth::id() && $deal->investor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deal->load(['business.user', 'business.category', 'investor', 'acceptedOffer']);
        return response()->json($deal);
    }
}
