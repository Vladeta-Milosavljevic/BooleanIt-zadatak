<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public function index()
    {
        // query builder is a faster option
        $categories = Db::table('categories')->get();
        // $categories = Category::all();
        return response()->json($categories);
    }


    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $categoryUpdate = ['name' => $request->name];

        // if category is not found
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // validating the input
        $validator = Validator::make($categoryUpdate, [
            'name' => ['required', 'string', 'max:255', 'min:3'],
        ]);

        // returning errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        };

        // updating with new value
        $category->update($categoryUpdate);

        return response()->json($category);
    }


    public function destroy($id)
    {
        $category = Category::find($id);
        // if category is not found
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        // delete the category
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
