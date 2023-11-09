<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TypeOfActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TypeOfActivityController extends Controller
{
    public function getAll() {
        $allTypes = TypeOfActivity::all();
        return response()->json($allTypes);

    }

    public function store(Request  $request) {
       $validator = Validator::make($request->all(), [
           'name' => 'required|string|unique:types_of_activity,name',
           'description' => 'string'
       ]);

       if($validator->fails()) {
           return response()->json([
               'success'=>'false',
               'message'=>'Store fail',
               'errors' => $validator->errors()
           ], 400);
       }

       $data = $validator->validated();
       $formData['name'] = $data['name'];
       $slug = Str::slug($data['name']);
       $formData['slug'] = $slug;

       if(array_key_exists('description', $data)) {
           $formData['description'] = $data('description');
       }

       $typeOfActivity =  TypeOfActivity::create($formData);
       $typeOfActivity->save();

       return response()->json([
           'success' => 'true',
           'message' => 'Store  success',
           'data' => $typeOfActivity
       ], 201);


    }

    public function edit($id, Request $request){
        $typeOfActivity = TypeOfActivity::find($id);

        if(!$typeOfActivity) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|unique:types_of_activity,name',
            'description' => 'string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Request fail',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();
        $formData = [];
        if(array_key_exists('name', $data)) {
            $formData['name'] = $data['name'];
            $slug = Str::slug($data['name']);
            $formData['slug'] = $slug;
        }

        if(array_key_exists('description', $data)) {
            $formData['description'] = $data['description'];
        }

        $typeOfActivity->update($formData);

        return response()->json([

            'success' => 'true',
            'message' => 'Edit success',
            'data' => $typeOfActivity
        ], 201);

    }

    public function destroy($id) {

        $typeOfActivity = TypeOfActivity::find($id);

        if(!$typeOfActivity) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not found'
            ], 400);
        }

        $typeOfActivity->delete();

        return response()->json([
            'success' => 'true',
            'message' => 'Destroy success'
        ], 201);

    }
}
