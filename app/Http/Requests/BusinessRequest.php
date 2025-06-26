<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->user_type === 'owner';
    }

    public function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'business_name' => 'required|string|max:255|unique:businesses,business_name,' . $this->business?->id,
            'description' => 'required|string|min:100',
            'business_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'valuation' => 'required|numeric|min:1000',
            'money_needed' => 'required|numeric|min:1000|lt:valuation',
            'percentage_offered' => 'required|numeric|min:0.01|max:100',
            'location' => 'required|string|max:255',
            'employees_count' => 'required|integer|min:1',
            'founded_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'business_model' => 'nullable|string|max:1000',
            'target_market' => 'nullable|string|max:1000',
            'competitive_advantages' => 'nullable|string|max:1000',
            'financial_highlights' => 'nullable|array',
            'financial_highlights.revenue' => 'nullable|numeric|min:0',
            'financial_highlights.profit' => 'nullable|numeric',
            'financial_highlights.growth_rate' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'money_needed.lt' => 'Money needed must be less than the business valuation.',
            'description.min' => 'Business description must be at least 100 characters.',
            'valuation.min' => 'Business valuation must be at least $1,000.',
            'money_needed.min' => 'Money needed must be at least $1,000.',
            'percentage_offered.min' => 'Percentage offered must be at least 0.01%.',
            'employees_count.min' => 'Employee count must be at least 1.',
        ];
    }
}
