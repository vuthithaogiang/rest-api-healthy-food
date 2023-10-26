<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryProductController extends Controller
{
    public function getAll() {
        return CategoryProduct::all();
    }

    public function show($slug) {
       $category = CategoryProduct::where("slug", $slug)->first();

       if( !$category) {
           return response()->json([
               'success'=> 'false',
               'message'=>'Not Found',
               'data'=> []
           ], 400);
       }
       else{
           return response()->json([
               'success'=> 'true',
               'message'=>'Successfully',
               'data'=> $category
           ], 200);
       }
    }

    public function store(Request $request) {
        $validator =  Validator::make($request->all(), [
            'name'=> 'required|string|unique:category_products,name',
            'description' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success'=>'false',
                'message' => 'Store category fail',
                $validator->errors()
                ]
                , 400);
        }

        $formData = $validator->validated();
        $formData['slug'] = Str::slug($formData['name']);

        $category = CategoryProduct::create($formData);

        return response()->json([
            'success' => 'true',
            'message' => 'Store category successfully',
            'data' =>$category
        ], 201);
    }

    public function edit($id, Request $request) {
        $category = CategoryProduct::find($id);

        if(!$category) {
            return response()->json([
                    'success'=>'false',
                    'message' => 'Not Found'
                ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:category_products,name',
            'description' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                    'success'=>'false',
                    'message' => 'Request fail',
                    'errors' =>  $validator->errors()
                ]
                , 400);
        }

        $formData = $validator->validated();
        $formData['slug'] = Str::slug($formData['name']);

        $category->update($formData);


        return response()->json([
            'success' => 'true',
            'message' => "Update successfully",
            'data' => $category
        ], 201);
    }

    public function inAvailable($id) {
        $category = CategoryProduct::find($id);

        if(!$category) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }

        $category['status'] = 0;
        $category->update();

        return response()->json([
            'success' => 'true',
            'message' => 'In active Category Success',
            'data' => $category
        ], 201);
    }

    public function restore($id) {
        $category = CategoryProduct::find($id);

        if(!$category){
            return  response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }

        $category['status'] = 1;
        $category->update();

        return response()->json([
            'success' => 'true',
            'message' => 'Active Category success',
            'data' => $category
        ], 201);
    }

    public function destroy($id) {
        $category = CategoryProduct::find($id);

        if(!$category) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }
        $category->delete();
        return response()->json([
            'success' => 'true',
            'message' => 'Destroy Success'

        ], 201);
    }
}
