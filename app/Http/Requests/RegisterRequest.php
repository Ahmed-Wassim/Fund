<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'user_type' => 'required|in:investor,owner',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Add conditional validation based on user type
        if ($this->user_type === 'investor') {
            $rules['title'] = 'required|string|max:100';
            $rules['bio'] = 'required|string|max:1000';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.required_if' => 'Title is required for investors.',
            'bio.required_if' => 'Bio is required for investors.',
            'birth_date.before' => 'You must be at least 18 years old to register.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
