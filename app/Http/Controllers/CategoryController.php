<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('businesses')->get();
        return response()->json($categories);
    }

    public function show(Category $category)
    {
        $category->load([
            'businesses' => function ($query) {
                $query->active()->with(['user', 'category'])->latest();
            }
        ]);

        return response()->json($category);
    }
}
