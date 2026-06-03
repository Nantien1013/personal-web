<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = CollectionCategory::orderBy('group')
            ->orderBy('display_order')
            ->get();
        return response()->json($categories);
    }
}
