<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    public function getAll() {
        $activities = Activity::with('TypeOfActivity')
            ->orderBy('campaign_id', 'asc')->get();

        return response()->json($activities);
    }

    public function store(Request  $request) {
       $validator = Validator::make($request->all(), [
           'name' => 'required|string|unique:activities,name',
           'description' => 'string',
           'campaignId' => 'required|exists:campaigns,id',
           'typeOfActivityId' => 'required|exists:types_of_activity,id'
       ]);

       if($validator->fails()) {
           return response()->json([
               'success' => 'false',
               'message' => 'Request Fail',
               'errors' => $validator->errors()
           ], 400);
       }

       $data = $validator->validated();
       $formData['name'] = $data['name'];
       $formData['slug'] = Str::slug($formData['name']);
       $formData['campaign_id'] = $data['campaignId'];
       $formData['type_of_activity_id'] = $data['typeOfActivityId'];

       if(array_key_exists('description', $data)) {
           $formData['description'] = $data['description'];
       }

       $activity = Activity::create($formData);
       $activity->save();

       return response()->json([
           'success' => 'true',
           'message' => 'Store Activity success',
           'data' => $activity->with('Campaign')->with('TypeOfActivity')->get()
       ], 201);
    }

    public function checkNameActivityIsExisted(Request  $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:activities,name'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'The name is conflict with another Activity'
            ], 400);
        }
        else{
            return response()->json([
                'success' => 'true',
                'message' => 'Name is valid.'
            ], 200);
        }
    }

    public function switchStatus($id, Request  $request) {
        $activity = Activity::find($id);

        if(!$activity) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not found Activity'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in([0, 1, 2, 3])]
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Request failure',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();

        $activity->update([
            'status' => $data['status']
        ]);


        return response()->json([
            'success' => 'true',
            'message' => 'Update status success'
        ], 200);
    }

    public function storeMultiData(Request $request){
        $validator = Validator::make($request->all(), [
            'multiActivity.*' => 'distinct_entries:multiActivity|array|required',
            'multiActivity.*.name' => 'required|string|distinct|unique:activities,name',
            'multiActivity.*.description' => 'string',
            'multiActivity.*.campaignId' => 'required|exists:campaigns,id',
            'multiActivity.*.typeOfActivityId' => 'required|exists:types_of_activity,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Request Fail',
                'errors' => $validator->errors()
            ]);

        }

       // $multiData = $validator->validated();

        $multiData = $request->input('multiActivity');

        foreach ($multiData as $data) {

          $formData['name'] = $data['name'];
          $formData['slug'] = Str::slug($formData['name']);
          $formData['campaign_id']  = $data['campaignId'];
          $formData['type_of_activity_id'] = $data['typeOfActivityId'];

          if(array_key_exists('description', $data)) {
              $formData['description'] = $data['description'];
          }

          Activity::create($formData);

        }

        return response()->json([
            'success' => 'true',
            'message' => 'Store multi data complete',

        ], 201);

    }

    public function edit($id, Request  $request){
      $activity = Activity::find($id);

      if(!$activity) {
          return response()->json([
              'success' => 'false',
              'message' => 'Not found Activity'
          ], 400);
      }

      $validator = Validator::make($request->all(), [
          'name' =>'string|unique:activities,name',
          'description' => 'string',
          'status' => Rule::in([0, 1, 2, 3]),
      ]);

      if($validator->fails()) {
          return response()->json([
              'success' => 'false',
              'message' => 'Request Fail',
              'errors' => $validator->errors()
          ]);
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

      if(array_key_exists('status', $data)) {
          $formData['status'] = $data['status'];
      }

      $activity->update($formData);
      return response()->json([
          'success' => 'true',
          'message' => 'Edit success',
          'data' => $activity->with('TypeOfActivity')->with('Campaign')->get()
      ], 201);
    }

    public function destroy($id) {
       $activity = Activity::find($id);

       if(!$activity) {
           return response()->json([
               'success' => 'false',
               'message' => 'Not Found Activity'
           ], 400);
       }

       $activities_schedule = DB::table('activity_schedule_campaign')->where('activity_id', $id)->first();

       if(!$activities_schedule ) {
           $activity->delete();
           return response()->json([
               'success' => 'true',
               'message' => 'Destroy complete'
           ], 201);
       }
       else{
           return response()->json([
               'success' => 'false',
               'message' => 'Can not Destroy This activity has existed in Schedule Campaign'
           ], 400);
       }
    }
}
