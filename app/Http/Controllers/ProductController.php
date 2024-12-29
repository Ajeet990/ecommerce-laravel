<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProductController extends Controller
{
    public function AddProduct(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|integer',
                'category' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form validation failed.',
                    'errors' => $validator->errors()
                ], 200);
            }
            $validated = $validator->validated();
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product-images', 'public');
                $imageUrl = asset('storage/' . $imagePath);
            }

            // Save data to database
            $product = new Product();
            $product->name = $validated['name'];
            $product->description = $validated['description'];
            $product->price = $validated['price'];
            $product->image = $imageUrl ?? '';
            $product->category_id = $validated['category'];
            $product->user_id = $userId;
            $product->save();
            
            $rst = [
                'success' => false,
                'message' => 'Unable to add product.'
            ];
            if ($product) {
                $rst = [
                    'success' => true,
                    'message' => 'Product added successfully'
                ];
            }
            return response()->json($rst);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getProducts(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');
            $name = $request->query('name');
            $category = $request->query('category');
            
            $productLists = Product::with('category')
                            ->where('user_id', $userId)
                            ->orderBy('created_at', 'DESC')
                            ->when($name, function ($query, $name) {
                                $query->where('name', 'LIKE', "%{$name}%")->orWhere('description', 'LIKE', "%{$name}%");
                            })
                            ->when($category, function ($query, $category) {
                                $query->where('category_id', $category);
                            })
                            ->get();
            $formattedResponse = $productLists->mapToGroups(function ($product) {
                return [
                    $product->category->name => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'image' => $product->image,
                        // 'user' => [
                        //     'id' => $product->user->id,
                        //     'name' => $product->user->name,
                        // ],
                    ],
                ];
            });
            $rst = [
                'success' => false,
                'message' => 'Not able to find products',
                'data' => []
            ];

            if ($productLists) {
                $rst = [
                    'success' => true,
                    'message' => 'Products found.',
                    'data' => $formattedResponse
                ];
            }
            return response()->json($rst);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getProductForHome(Request $request)
    {
        try {
            $rst = [
                'success' => true,
                'message' => 'No products found.',
                'data' => []
            ];

            $productList = Product::all();
            if ($productList) {
                $rst = [
                    'success' => true,
                    'message' => 'Products found.',
                    'data' => $productList
                ];
            }
            return response()->json($rst);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ]);
        }
    }
}
