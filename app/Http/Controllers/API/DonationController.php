<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class DonationController extends Controller
{
    public function getAll() {
        return response()->json([
            'message' => 'Get all donations'
        ]);
    }

    public function store(Request $request) {

        return response()->json([
            'message' => 'Store'
        ]);

    }

    public function edit($id, Request $request) {
        return response()->json([
            'message' => 'Edit'
        ]);
    }

    public function destroy($id) {
        return response()->json([
            'message' => 'Destroy'
        ]);
    }
}
