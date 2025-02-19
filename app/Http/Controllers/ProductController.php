<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function productsPerCategory($id)
    {
        $productsPerCategory = Category::with('product')->where('id', $id)->get();
        if (count($productsPerCategory) === 0) {
            return response()->json(['message' => 'Could not find products for this category'], 404);
        }
        return response()->json($productsPerCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        // if product is not found
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $category= Category::find($request->category_id);
        // if category is not found
        if (!$category) {
            return response()->json(['message' => 'Category for the selected product not found'], 404);
        }
        // getting the data and removing null fields from the update
        $productUpdate = [
            'product_number' => $request->product_number ?? $product->product_number,
            'category_id' => $request->category_id ?? $product->category_id,
            'deparment_name' => $request->deparment_name ?? $product->deparment_name,
            'manufacturer_name' => $request->manufacturer_name ?? $product->manufacturer_name,
            'upc' => $request->upc ?? $product->upc,
            'sku' => $request->sku ?? $product->sku,
            'regular_price' => $request->regular_price ?? $product->regular_price,
            'sale_price' => $request->sale_price ?? $product->sale_price,
            'description' => $request->description ?? $product->description,

        ];
        // validating the input
        $validator = Validator::make($productUpdate, [
            'product_number' => ['nullable','min:2'],
            'category_id' => 'required',
            'deparment_name' => ['nullable','min:2'],
            'manufacturer_name' => ['nullable','min:2'],
            'upc' => ['nullable','min:2'],
            'sku' => ['nullable','min:2'],
            'regular_price' => ['nullable','min:2'],
            'sale_price' => ['nullable','min:2'],
            'description' => ['nullable','min:2'],
        ]);

        // returning errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        };
        // updating the product
        $product->update($productUpdate);

        return response()->json($product);
    }


    public function destroy($id)
    {
        $product = Product::find($id);
        // if product is not found
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
