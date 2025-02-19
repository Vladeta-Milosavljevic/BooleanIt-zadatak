<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }


    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $categoryUpdate = ['name' => $request->name];
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $validator = Validator::make($categoryUpdate, [
            'name' => ['required', 'string', 'max:255', 'min:3'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        };
        $category->update($categoryUpdate);
        return response()->json($category);
    }


    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 204);
    }
}
