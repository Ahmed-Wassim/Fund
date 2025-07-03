<?php
namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $query = Business::with(['user', 'category'])
            ->active()
            ->latest();

        // Apply filters
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->has('min_amount') && $request->has('max_amount')) {
            $query->byMoneyRange($request->min_amount, $request->max_amount);
        }

        if ($request->has('min_percentage') && $request->has('max_percentage')) {
            $query->byPercentageRange($request->min_percentage, $request->max_percentage);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $businesses = $query->paginate(12);

        return response()->json($businesses);
    }

    public function store(Request $request)
    {
        if (Auth::user()->type !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id'            => 'required|exists:categories,id',
            'business_name'          => 'required|string|max:255',
            'description'            => 'required|string',
            'business_photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'valuation'              => 'required|numeric|min:0',
            'money_needed'           => 'required|numeric|min:0',
            'percentage_offered'     => 'required|numeric|min:0|max:100',
            'location'               => 'required|string|max:255',
            'employees_count'        => 'required|integer|min:0',
            'founded_year'           => 'nullable|integer|min:1900|max:' . date('Y'),
            'business_model'         => 'nullable|string',
            'target_market'          => 'nullable|string',
            'competitive_advantages' => 'nullable|string',
            'financial_highlights'   => 'nullable|array',
            'status'                 => 'required|in:active,pending,closed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $businessData            = $request->except(['business_photo']);
        $businessData['user_id'] = Auth::id();

        if ($request->hasFile('business_photo')) {
            $businessData['business_photo'] = $request->file('business_photo')->store('businesses', 'public');
        }

        $business = Business::create($businessData);
        $business->load(['user', 'category']);

        return response()->json([
            'message'  => 'Business created successfully',
            'business' => $business,
        ], 201);
    }

    public function show(Business $business)
    {
        $business->load(['user', 'category', 'offers.investor']);
        return response()->json($business);
    }

    public function update(Request $request, Business $business)
    {
        if ($business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id'            => 'sometimes|exists:categories,id',
            'business_name'          => 'sometimes|string|max:255',
            'description'            => 'sometimes|string',
            'business_photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'valuation'              => 'sometimes|numeric|min:0',
            'money_needed'           => 'sometimes|numeric|min:0',
            'percentage_offered'     => 'sometimes|numeric|min:0|max:100',
            'location'               => 'sometimes|string|max:255',
            'employees_count'        => 'sometimes|integer|min:0',
            'founded_year'           => 'nullable|integer|min:1900|max:' . date('Y'),
            'business_model'         => 'nullable|string',
            'target_market'          => 'nullable|string',
            'competitive_advantages' => 'nullable|string',
            'financial_highlights'   => 'nullable|array',
            'status'                 => 'nullable|in:active,pending,closed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $businessData = $request->except(['business_photo']);

        if ($request->hasFile('business_photo')) {
            $businessData['business_photo'] = $request->file('business_photo')->store('businesses', 'public');
        }

        $business->update($businessData);
        $business->load(['user', 'category']);

        return response()->json([
            'message'  => 'Business updated successfully',
            'business' => $business,
        ]);
    }

    public function destroy(Business $business)
    {
        if ($business->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $business->delete();
        return response()->json(['message' => 'Business deleted successfully']);
    }

    public function myBusinesses()
    {
        $businesses = Business::where('user_id', Auth::id())
            ->with(['category', 'offers.investor'])
            ->latest()
            ->get();

        return response()->json($businesses);
    }
}
