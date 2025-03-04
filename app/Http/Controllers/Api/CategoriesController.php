<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\First_category;
use App\Models\Second_category;
use App\Models\Third_category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriesController extends Controller
{
    public function childCategories(Request $request, $id)
    {
        $type = intval($request->query('type'));
        if (!$type) {
            return response()->json([
                'error' => 'Type of category is missing. Example: put [type=1] for first category in the url.'
            ], 400);
        }
        if ($type > 3 || $type < 1) {
            return response()->json(['error' => 'invalid type number'], 400);
        }
        $category = match ($type) {
            1 => First_category::find($id),
            2 => Second_category::find($id),
            3 => Third_category::find($id),
            default => null
        };

        if (!$category) {
            return response()->json([
                'error' => 'Category not found'
            ], 404);
        }
        $childCategories = match ($type) {
            2 => Second_category::where('parent_id', $id)->get(),
            3 => Third_category::where('parent_id', $id)->get(),
            default => null
        };
        return response()->json([
            'success' => true,
            'data' => $childCategories
        ], 200);
    }
    public function getCategories(Request $request, $id = null)
    {
        $type = intval($request->query('type'));
        if (!$type) {
            return response()->json([
                'error' => 'Type of category is missing. Example: put [type=1] for first category in the url.'
            ], 400);
        }
        if ($type > 3 || $type < 1) {
            return response()->json(['error' => 'invalid type number'], 400);
        }

        if (!$id) {
            $category = match ($type) {
                1 => First_category::all(),
                2 => Second_category::all(),
                3 => Third_category::all(),
                default => null
            };
        } else {
            $category = match ($type) {
                1 => First_category::find($id),
                2 => Second_category::find($id),
                3 => Third_category::find($id),
                default => null
            };

            if (!$category) {
                return response()->json([
                    'error' => 'Category not found'
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }


    public function addCategories(Request $request)
    {
        $type = intval($request->query('type'));
        if (!$type) {
            return response()->json([
                'error' => 'Type of category is missing. Example: put [type=1] for first category in the url.'
            ], 400);
        }
        if ($type > 3 || $type < 1) {
            return response()->json(['error' => 'invalid type number'], 400);
        }
        $validatedData = $request->validate([
            'name' => 'required|string',
            'parent_id' => [
                Rule::requiredIf(in_array($type, [2, 3])),
                'integer',
            ],
        ]);
        $updatedCategory = [
            'name' => $validatedData['name'],
        ];

        if ($type !== 1) {
            $updatedCategory['parent_id'] = $validatedData['parent_id'];
        }
        $categoryModel = match ($type) {
            1 => First_category::create($updatedCategory),
            2 => Second_category::create($updatedCategory),
            3 => Third_category::create($updatedCategory),
            default => null,
        };
        return response()->json([
            'success' => true,
            'message' => 'Category record added successfully',
            'data' => $categoryModel,
        ], 201);
    }


    public function updateCategories(Request $request, $id)
    {
        $type = intval($request->query('type'));
        if (!$type) {
            return response()->json([
                'error' => 'Type of category is missing. Example: put [type=1] for first category in the url.'
            ], 400);
        }
        if ($type > 3 || $type < 1) {
            return response()->json(['error' => 'invalid type number'], 400);
        }
        $validatedData = $request->validate([
            'name' => 'required|string',
            'parent_id' => [
                Rule::requiredIf(in_array($type, [2, 3])),
                'integer',
            ],
        ]);

        $updatedCategory = [
            'name' => $validatedData['name'],
        ];

        if ($type !== 1) {
            $updatedCategory['parent_id'] = $validatedData['parent_id'];
        }

        $categoryModel = match ($type) {
            1 => First_category::find($id),
            2 => Second_category::find($id),
            3 => Third_category::find($id),
            default => null,
        };

        if (!$categoryModel) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $categoryModel->update($updatedCategory);

        return response()->json([
            'success' => true,
            'message' => 'Category record updated successfully',
            'data' => $categoryModel,
        ], 200);
    }

    public function deleteCategory(Request $request, $id)
    {
        $type = intval($request->query('type'));
        if (!$type) {
            return response()->json([
                'error' => 'Type of category is missing. Example: put [type=1] for first category in the url.'
            ], 400);
        }
        if ($type > 3 || $type < 1) {
            return response()->json(['error' => 'invalid type number'], 400);
        }


        $category = match ($type) {
            1 => First_category::find($id),
            2 => Second_category::find($id),
            3 => Third_category::find($id),
            default => null,
        };

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted'
        ]);
    }

    public function categoryBooks(Request $request, $id)
    {
        $type = intval($request->query('type'));
        if (!$type) {
            return response()->json([
                'error' => 'Type of category is missing. Example: put [type=1] for first category in the url.'
            ], 400);
        }
        if ($type > 3 || $type < 1) {
            return response()->json(['error' => 'invalid type number'], 400);
        }


        $categoryBooks = match ($type) {
            1 => Book::with('first_category', 'second_category', 'third_category')->where('first_category_id', $id)->get(),
            2 => Book::with('first_category', 'second_category', 'third_category')->where('second_category_id', $id)->get(),
            3 => Book::with('first_category', 'second_category', 'third_category')->where('third_category_id', $id)->get(),
            default => null,
        };

        if (!$categoryBooks) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $categoryBooks
        ]);
    }
}
