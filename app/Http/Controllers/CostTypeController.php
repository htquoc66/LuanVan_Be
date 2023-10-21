<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CostType;

class CostTypeController extends Controller
{
    public function index()
    {
        $costType = CostType::all();
        return response()->json($costType);
    }
}
