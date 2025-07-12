<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('businesses')->get();
        return response()->json($categories);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        $category->load('businesses');
        return response()->json($category);
    }

    public function create()
    {
        $fields = request()->all();
        $fields['slug'] = Str::slug($fields['name']);
        $categorey = Category::create($fields);
        return response()->json($categorey);
    }
}
