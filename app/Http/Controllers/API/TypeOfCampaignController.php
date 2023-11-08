<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TypeOfCampaign;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TypeOfCampaignController extends Controller
{
    public function getAll() {
        $typesOfCampaign =  TypeOfCampaign::orderBy('created_at', 'desc')->get();

        if(!$typesOfCampaign) {
            return response()->json([
                'message' => 'No Data',
                "data" => []
            ]);
        }

        return response()->json($typesOfCampaign);
    }


    public function store(Request $request) {
        $validator =  Validator::make($request->all(), [
            'name'=> 'required|string|unique:types_of_campaign,name',
            'state'=>Rule::in(['Public', 'Private']),
            'description' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success'=>'false',
                'message' => 'Store Type of Campaign fail',
                'errors' => $validator->errors()
            ], 400);
        }

        $formData = $validator->validated();
        $formData['slug'] = Str::slug($formData['name']);

        $category = TypeOfCampaign::create($formData);

        return response()->json([
            'success' => 'true',
            'message' => 'Store Type of Campaign successfully',
            'data' =>$category
        ], 201);
    }

    public function edit($id, Request  $request) {
        $typeOfCampaign = TypeOfCampaign::find($id);

        if(!$typeOfCampaign) {
            return response()->json([
                'success'=>'false',
                'message' => 'Not Found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:types_of_campaign,name',
            'state' => Rule::in(['Public', 'Private']),
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

        $typeOfCampaign->update($formData);


        return response()->json([
            'success' => 'true',
            'message' => "Update successfully",
            'data' => $typeOfCampaign
        ], 201);
    }

    public function destroy($id){
        $typeOfCampaign = TypeOfCampaign::find($id);

        if(!$typeOfCampaign) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }
        $typeOfCampaign->delete();
        return response()->json([
            'success' => 'true',
            'message' => 'Destroy Success'

        ], 201);
    }
}
