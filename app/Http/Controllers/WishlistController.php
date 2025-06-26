<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::with(['business.user', 'business.category'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($wishlists);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);

        $business = Business::findOrFail($request->business_id);

        // Check if user is trying to wishlist their own business
        if ($business->user_id === Auth::id()) {
            return response()->json(['error' => 'Cannot add your own business to wishlist'], 403);
        }

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'business_id' => $request->business_id,
        ]);

        $wishlist->load(['business.user', 'business.category']);

        return response()->json([
            'message' => 'Business added to wishlist',
            'wishlist' => $wishlist
        ], 201);
    }

    public function destroy(Business $business)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('business_id', $business->id)
            ->first();

        if (!$wishlist) {
            return response()->json(['error' => 'Business not in wishlist'], 404);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Business removed from wishlist']);
    }

    public function check(Business $business)
    {
        $exists = Wishlist::where('user_id', Auth::id())
            ->where('business_id', $business->id)
            ->exists();

        return response()->json(['in_wishlist' => $exists]);
    }
}
