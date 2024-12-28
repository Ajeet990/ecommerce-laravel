<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function getCategories(Request $request)
    {
        try {
            $allCategories = Category::orderBy('name', 'ASC')->get();
            $rst = [
                'success' => true,
                'message' => 'No categories found.',
                'data' => []
            ];
            if ($allCategories) {
                $rst = [
                    'success' => true,
                    'message' => 'Category list.',
                    'data' => $allCategories
                ];
            }
            return response()->json($rst);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ]); 
        }
    }

    public function addCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:5'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }
            $validated = $validator->validated();
            $categoryAlreadyExist = Category::where('name', $validated['name'])->first();
            if ($categoryAlreadyExist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category already exists.'
                ], 409);
            }


            Category::create([
                'name' => $validated['name']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category Added'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ]); 
        }
    }
}
