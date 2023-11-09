<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    public function getAll() {
         return response()->json([
             'message' => 'Get all'
         ]);
    }

    public function store(Request  $request) {
       $validator = Validator::make($request->all(), [
           'name'
       ]);
    }

    public function edit($id, Request  $request){
        return response()->json([
            'message' => 'Edit' .$id
        ]);
    }

    public function destroy($id) {
        return response()->json([
            'message' => 'Delete' .$id
        ]);
    }
}
