<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use  Illuminate\Support\Facades;

class CampaignController extends Controller
{
    public function getAll() {
       $campaigns = Campaign::with('Thumbnails')
           ->with('TypeOf')
           ->orderBy('id','asc')
           ->get();

       return response()->json($campaigns);
    }

    public function store(Request $request) {

       $validator = Validator::make($request->all(), [
           'name' => 'required|string|unique:campaigns,name',
           'objective' => 'required|string',
           'typeCampaignId' => 'required|exists:types_of_campaign,id',
           'description' => 'required|string',
           'channel' => 'string',
           'startDate'=> 'required|date|date_format:d-m-Y|after_or_equal:today',
           'endDate'=>'required|date|date_format:d-m-Y|after_or_equal:today',
           'thumb'=> 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
           'status'=>Rule::in([0, 1, 2, 3]),
           'budget' => 'numeric|min:0.01',
           'dailyBudget' => 'numeric|min:0.01'
       ]);

       if($validator->fails()){
           return response()->json([
               'success' => 'false',
               'message' => 'Request fail',
               'errors' => $validator->errors()
           ]);
       }

        $slug = Str::slug($validator->validated()['name']);
        // If validation passes, insert the date into the database
        $startDate = date('Y-m-d', strtotime($request->get('startDate')));
        $endDate = date('Y-m-d', strtotime($request->get('endDate')));

        $data = $validator->validated();

        $formData['name'] = $data['name'];
        $formData['description'] = $data['description'];
        $formData['objective'] = $data['objective'];
        $formData['type_campaign_id'] = $data['typeCampaignId'];
        $formData['start_date'] = $startDate;
        $formData['end_date']= $endDate;
        $formData['slug'] = $slug;

        if(array_key_exists('budget', $data)){
            $formData['budget'] = $data['budget'];
        }

        if(array_key_exists('dailyBudget', $data)){
            $formData['daily_budget'] = $data['dailyBudget'];
        }

        if(array_key_exists('status', $data)){
            $formData['status'] = $data['status'];
        }

        if(array_key_exists('channel', $data)){
            $formData['channel'] = $data['channel'];
        }

        $campaign = Campaign::create($formData);

        $campaign->save();


        if(array_key_exists('thumb', $data)){

                $imageName = time() . '_' . $data['thumb']->getClientOriginalName();
                Facades\Storage::putFileAs('', $data['thumb'], $imageName);
                $campaign->Thumbnails()->create([
                    'path' => $imageName
                ]);
        }

        // GET LIST THUMBS OF PRODUCT
        $thumbnails = Facades\DB::table('campaigns')
            ->join('campaign_thumbnails', 'campaigns.id', '=', 'campaign_thumbnails.campaign_id')
            ->where('campaign_thumbnails.campaign_id','=', $campaign->id)
            ->select( 'campaign_thumbnails.*')
            ->get();

        return response()->json([
            'success' => 'true',
            'message' => 'Store Campaign Successfully',
            'data' => $campaign,
            'thumbs' => $thumbnails,
        ]);

    }
    public function edit($id, Request  $request) {
        return response()->json([
            'message' => 'Edit ' . $id
        ]);
    }

    public function destroy($id ) {
        return response()->json([
            'message' => 'Delete ' . $id
        ]);
    }
}
