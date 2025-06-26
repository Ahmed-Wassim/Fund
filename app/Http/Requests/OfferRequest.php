<?php

namespace App\Http\Requests;

use App\Models\Business;
use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{

    public function authorize()
    {
        if (auth()->user()->user_type !== 'investor') {
            return false;
        }

        $business = Business::find($this->business_id);
        return $business && $business->user_id !== auth()->id() && $business->is_active;
    }

    public function rules()
    {
        return [
            'business_id' => 'required|exists:businesses,id',
            'offered_amount' => 'required|numeric|min:1000',
            'requested_percentage' => 'required|numeric|min:0.01|max:100',
            'message' => 'nullable|string|max:1000',
            'parent_offer_id' => 'nullable|exists:offers,id',
        ];
    }

    public function messages()
    {
        return [
            'offered_amount.min' => 'Minimum offer amount is $1,000.',
            'requested_percentage.min' => 'Minimum percentage is 0.01%.',
            'requested_percentage.max' => 'Maximum percentage is 100%.',
        ];
    }
}

