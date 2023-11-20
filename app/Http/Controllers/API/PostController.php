<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    //

    public function getAll () {
        return response()->json([
            'message' => 'Get all Posts'
        ]);
    }

    public function store(Request  $request) {

        $user = auth()->user();

        return response()->json([
            'message' => 'Store Post'
            ]);
    }

    public function edit($id, Request  $request) {
        return response()->json([
            'message' => 'Edit Post'
        ]);
    }

    public function  destroy($id) {
        return response()->json([
            'message' => 'Destroy'
        ]);
    }
}
