<?php
namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Deal;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id'          => 'required|exists:businesses,id',
            'offered_amount'       => 'required|numeric|min:0',
            'requested_percentage' => 'required|numeric|min:0|max:100',
            'message'              => 'nullable|string|max:1000',
            'parent_offer_id'      => 'nullable|exists:offers,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $business = Business::findOrFail($request->business_id);

        // Check if user is trying to make offer on their own business
        if ($business->user_id === Auth::id()) {
            return response()->json(['error' => 'Cannot make offer on your own business'], 403);
        }

        $offerData                = $request->all();
        $offerData['investor_id'] = Auth::id();

        $offer = Offer::create($offerData);
        $offer->load(['business', 'investor']);

        return response()->json([
            'message' => 'Offer created successfully',
            'offer'   => $offer,
        ], 201);
    }

    public function acceptOffer(Request $request, Offer $offer)
    {
        $business = $offer->business;

        // Check if user owns the business
        if ($business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $offer->canBeAccepted()) {
            return response()->json(['error' => 'Offer cannot be accepted'], 400);
        }

        DB::transaction(function () use ($offer) {
            // Accept the offer
            $offer->update(['status' => 'accepted']);

            // Create the deal
            Deal::create([
                'business_id'       => $offer->business_id,
                'investor_id'       => $offer->investor_id,
                'accepted_offer_id' => $offer->id,
                'final_amount'      => $offer->offered_amount,
                'final_percentage'  => $offer->requested_percentage,
                'deal_date'         => now(),
            ]);

            // Close all other pending offers for this business
            Offer::where('business_id', $offer->business_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update(['status' => 'closed']);

            // Mark business as inactive
            $offer->business->update(['status' => 'closed']);
        });

        return response()->json(['message' => 'Offer accepted successfully']);
    }

    public function counterOffer(Request $request, Offer $offer)
    {
        $validator = Validator::make($request->all(), [
            'offered_amount'       => 'required|numeric|min:0',
            'requested_percentage' => 'required|numeric|min:0|max:100',
            'message'              => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $business = $offer->business;

        // Check if user owns the business
        if ($business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create counter offer
        $counterOffer = Offer::create([
            'business_id'          => $offer->business_id,
            'investor_id'          => $offer->investor_id,
            'offered_amount'       => $request->offered_amount,
            'requested_percentage' => $request->requested_percentage,
            'message'              => $request->message,
            'parent_offer_id'      => $offer->id,
            'status'               => 'pending',
        ]);

        // Update original offer status
        $offer->update(['status' => 'counter_offered']);

        $counterOffer->load(['business', 'investor']);

        return response()->json([
            'message' => 'Counter offer created successfully',
            'offer'   => $counterOffer,
        ], 201);
    }

    public function rejectOffer(Offer $offer)
    {
        $business = $offer->business;

        // Check if user owns the business
        if ($business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $offer->update(['status' => 'rejected']);

        return response()->json(['message' => 'Offer rejected successfully']);
    }

    public function myOffers()
    {
        $offers = Offer::with(['business.user', 'business.category'])
            ->fromInvestor(Auth::id())
            ->latest()
            ->get();

        return response()->json($offers);
    }

    public function businessOffers(Business $business)
    {
        // Check if user owns the business
        if ($business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $offers = $business->offers()
            ->with(['investor', 'parentOffer', 'childOffers'])
            ->latest()
            ->get();

        return response()->json($offers);
    }

    public function show(Offer $offer)
    {
        // Check if user is involved in this offer
        if ($offer->investor_id !== Auth::id() && $offer->business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $offer->load(['business.user', 'investor', 'parentOffer', 'childOffers']);
        return response()->json($offer);
    }
}
