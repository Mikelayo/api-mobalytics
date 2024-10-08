<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Item;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $typeObject = $request->input('type_object');

        $items = Item::getItemsWithRequiredItems($typeObject);

        return response()->json($items);
    }
}