<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Campaign;
use App\Models\CampaignThumbnails;
use App\Models\ScheduleCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use  Illuminate\Support\Facades;

class CampaignController extends Controller
{
    public function getAll() {
       $campaigns = Campaign::with('Thumbnails')
           ->with('TypeOfCampaign')
           ->with('Activities')
           ->orderBy('id','asc')
           ->get();

       return response()->json($campaigns);
    }

    public function getBySlug($slug ) {
        $campaign = Campaign::with('Thumbnails')
            ->with('TypeOfCampaign')
            ->where('slug', '=' , $slug)
            ->first();

        if(!$campaign) {
            return response()->json([
                'success' =>'false',
                'message' => 'Not Found'
            ], 400);
        }

        return response()->json([
            'success' => 'true',
            'message' => 'Get Campaign success',
            'data' => $campaign
        ], 200);
    }

    public function getFilter(Request  $request) {

        $validator = Validator::make($request->all(), [
            'status' => Rule::in([0, 1, 2, 3]),
            'fromDate' =>'date|date_format:d-m-Y',
            'toDate' =>'date|date_format:d-m-Y',
            'typeOfCampaignId' => 'exists:types_of_campaign,id',
            'key'=>'string'
        ]) ;

        if($validator->fails()) {
            return  response()->json([
                'success' => 'false',
                'message' => 'Request fail',
                'errors' =>  $validator->errors()
            ], 400);
        }

        $data = $validator->validated();

        $query = Campaign::with('Thumbnails')
            ->with('TypeOfCampaign')
            ->with('Activities');

        if(array_key_exists('status', $data)) {
          $query->where('status', '=', $data['status']);
        }

        if(array_key_exists('typeOfCampaignId', $data)) {
            $query->where('type_of_campaign_id', '=', $data['typeOfCampaignId']);
        }

        if(array_key_exists('fromDate', $data)){

            $startDateSchedule = date('Y-m-d', strtotime($data['fromDate']));
            $query->where('start_date' , '<=' , $startDateSchedule)
                ->where('end_date', '>=', $startDateSchedule);
        }

        if(array_key_exists('toDate', $data)) {
            $endDateSchedule = date('Y-m-d', strtotime($data['toDate']));
            $query->where('start_date' , '<=' , $endDateSchedule)
                ->where('end_date', '>=', $endDateSchedule);
        }

        if(array_key_exists('key', $data)) {

            $query->where('name', 'like', '%'. $data['key'].'%');

        }

        $campaigns =  $query->get();

            return response()->json([
                'success' => 'true',
                'message' => 'Get Campaign success',
                'data' => $campaigns
            ], 200);



    }

    public function switchStatus($id, Request  $request) {
        $campaign = Campaign::find($id);

        if(!$campaign) {
            return response()->json([
                'success' => 'false',
                'message' =>'Not found Campaign'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'status' => [ 'required' ,Rule::in([0, 1, 2, 3])]
        ]);


        if($validator->fails()) {
            return response()->json([
                'success' =>'false',
                'message' =>'Request Fail',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();


        $campaign->update([
            'status' => $data['status']
        ]);

        return response()->json([
            'success' => 'true',
            'message' => 'Switch status Campaign success',
            'data' => $campaign
        ], 200);

    }

    public function store(Request $request) {

       $validator = Validator::make($request->all(), [
           'name' => 'required|string|unique:campaigns,name',
           'objective' => 'required|string',
           'typeCampaignId' => 'required|exists:types_of_campaign,id',
           'description' => 'string',
           'channel' => 'string',
           'startDate'=> 'required|date|date_format:d-m-Y|after_or_equal:today',
           'endDate'=>'required|date|date_format:d-m-Y|after_or_equal:startDate',
           'thumb'=> 'image|mimes:jpeg,png,jpg,gif|max:2048',
           'status'=>Rule::in([0, 1, 2, 3]),
           'budget' => 'numeric|min:0.01',
           'dailyBudget' => 'numeric|min:0.01'
       ]);

       if($validator->fails()){
           return response()->json([
               'success' => 'false',
               'message' => 'Request fail',
               'errors' => $validator->errors()
           ], 400);
       }

        $slug = Str::slug($validator->validated()['name']);
        // If validation passes, insert the date into the database
        $startDate = date('Y-m-d', strtotime($request->get('startDate')));
        $endDate = date('Y-m-d', strtotime($request->get('endDate')));

        $data = $validator->validated();

        $formData['name'] = $data['name'];
        $formData['objective'] = $data['objective'];
        $formData['type_of_campaign_id'] = $data['typeCampaignId'];
        $formData['start_date'] = $startDate;
        $formData['end_date']= $endDate;
        $formData['slug'] = $slug;

        if(array_key_exists('budget', $data)){
            $formData['budget'] = $data['budget'];
        }

        if(array_key_exists('description', $data)) {
            $formData['description'] = $data['description'];
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
        ], 200);

    }
    public function edit($id, Request  $request) {
       $campaign = Campaign::find($id);

       if(!$campaign) {
           return response()->json([
               'success' => 'false',
               'message' => 'Not found that Campaign to edit.'
           ], 400);
       }

        $validator = Validator::make($request->all(), [
            'status' => Rule::in([0, 1, 2, 3]),
            'thumb' =>'image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'string|unique:campaigns,name',
            'description' => 'string',
            'objective' => 'string',
            'budget' =>'numeric|min:0.01',
            'dailyBudget' => 'numeric|min:0.01',
            'startDate' =>'date|date_format:d-m-Y',
            'endDate' =>'date|date_format:d-m-Y',
            'channel' =>'string',
            'typeOfCampaignId' => 'exists:types_of_campaign,id'
        ]) ;

       if($validator->fails()) {
           return response()->json([
               'success'=>'false',
               'message' => 'Request fail',
               'errors' => $validator->errors()
           ], 400);
       }

       $data = $validator->validated();

       if(array_key_exists('thumb', $data)) {

           $thumbToDelete = CampaignThumbnails::where('campaign_id', $id)->first();


           if(!$thumbToDelete) {

               $imageName = time() . '_' . $data['thumb']->getClientOriginalName();
               Facades\Storage::putFileAs('', $data['thumb'], $imageName);

               $newThumb = CampaignThumbnails::create([
                   'campaign_id' => $campaign->id,
                   'path' => $imageName
               ]);
               $newThumb->save();


           }
           //Remove old thumb
           else{
               Facades\Storage::disk('')->delete($thumbToDelete->path);
               $thumbToDelete->delete();

               //create
               $imageName = time() . '_' . $data['thumb']->getClientOriginalName();
               Facades\Storage::putFileAs('', $data['thumb'], $imageName);

               $newThumb = CampaignThumbnails::create([
                   'campaign_id' => $campaign->id,
                   'path' => $imageName
               ]);
               $newThumb->save();

           }


        }

       $formData = [];

       if(array_key_exists('name', $data)) {
           $formData['name'] = $data['name'];
           $slug = Str::slug($data['name']);
           $formData['slug'] = $slug;
       }

       if(array_key_exists('objective', $data)) {
           $formData['objective'] = $data['objective'];
       }

       if(array_key_exists('description', $data)) {
           $formData['description'] = $data['description'];
       }

       if(array_key_exists('budget', $data)) {
           $formData['budget'] = $data['budget'];
       }
       if(array_key_exists('dailyBudget', $data)) {
           $formData['daily_budget'] = $data['dailyBudget'];
       }

       if(array_key_exists('channel', $data)) {
           $formData['channel'] = $data['channel'];
       }

       if(array_key_exists('startDate', $data)) {
           $startDate = date('Y-m-d', strtotime($data['startDate']));
           $formData['start_date'] = $startDate;

       }
       if(array_key_exists('endDate', $data)) {
           $endDate = date('Y-m-d', strtotime($data['endDate']));
           $formData['end_date'] = $endDate;

       }

       if(array_key_exists('status', $data)) {
           $formData['status'] = $data['status'];

        }

       if(array_key_exists('typeOfCampaignId', $data)) {
           $formData['type_of_campaign_id'] = $data['typeOfCampaignId'];
       }

       $campaign->update($formData);

        // GET LIST THUMBS OF PRODUCT
        $thumbnails = Facades\DB::table('campaigns')
            ->join('campaign_thumbnails', 'campaigns.id', '=', 'campaign_thumbnails.campaign_id')
            ->where('campaign_thumbnails.campaign_id','=', $campaign->id)
            ->select( 'campaign_thumbnails.*')
            ->get();

       return response()->json([
           'success' => 'true',
           'message' => 'Edit success',
           'data' => $campaign,
           'thumbs' => $thumbnails
       ], 201);
    }

    public function getScheduleActivities($id) {
        $campaign = Campaign::find($id);

        if(!$campaign) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found Campaign'
            ], 400);
        }


        $schedules =  ScheduleCampaign::where('campaign_id', '=', $campaign->id)->get();
        $data  = [];
        foreach ($schedules as $time) {
            $timetable = $time;

            $activities = Facades\DB::table('activities')
                ->join('activity_schedule_campaigns', 'activities.id', '=', 'activity_schedule_campaigns.activity_id')
                ->join('schedules_campaign', 'activity_schedule_campaigns.schedule_campaign_id', '=', 'schedules_campaign.id')
                ->where('schedules_campaign.id','=', $time->id)
                ->where('activities.campaign_id', '=', $campaign->id)
                ->select( 'activities.*')
                ->get();

            $timetable['activities'] = $activities;

           $data[] = $timetable;
        }

        return response()->json([
            'success' => 'true',
            'message' => 'Get data success',
            'data' => $data
        ], 200);

    }

    public function addActivityToScheduleCampaign($id, Request  $request) {
        $campaign = Campaign::find($id);

        if(!$campaign){
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'startDate' => 'required|date|date_format:d-m-Y|before_or_equal:endDate',
            'endDate' => 'required|date|date_format:d-m-Y|after_or_equal:startDate',
            'multiActivity.*' => 'distinct_entries:multiActivity|array|required',
            'multiActivity.*.name' => 'required|string|distinct',
            'multiActivity.*.description' => 'string',
            'multiActivity.*.typeOfActivityId' => 'required|exists:types_of_activity,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => 'false',
                'message' => 'Request Fail',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();

        $startDateCampaign = $campaign->start_date;
        $endDateCampaign = $campaign->end_date;

        $startDateSchedule = date('Y-m-d', strtotime($data['startDate']));
        $endDateSchedule = date('Y-m-d', strtotime($data['endDate']));


        //CHECK DATE FROM - TO IN SCHEDULE
        $error = [];
        if( ($startDateSchedule < $startDateCampaign) | ($startDateSchedule > $endDateCampaign)) {
                $error['start_date'] = 'The start date must be equal or after start date Campaign';
        }

        if( ($endDateSchedule > $endDateCampaign) | ($endDateSchedule < $startDateCampaign)){
                $error['end_date'] = 'The end date must be equal or before end date Campaign';
        }

        if($startDateSchedule > $endDateSchedule) {
                $error['duplicate'] = "Start date must before or equal end date";
        }

        if(array_key_exists('start_date', $error) | array_key_exists('end_date', $error)
        | array_key_exists('duplicate', $error)) {
            return response()->json([
                'success' => 'false',
                'message' => "Date invalid",
                'errors' => $error
            ], 400);
        }

        // GET MULTI ACTIVITY IN REQUEST
        $multiData = $request->input('multiActivity');

        $activitiesExisted = [];
        $activitiesNew = [];

        foreach ($multiData as $activity) {
            $activityExisted = Activity::where('name', $activity['name'])
                ->where('type_of_activity_id', $activity['typeOfActivityId'])
                ->first();

             if(!$activityExisted) {
                 $activitiesNew[] = $activity;
             }
             else{
                 $activitiesExisted[] = $activityExisted;
            }

        }


        // SELECT SCHEDULE EXIST
        $scheduleExisted = ScheduleCampaign::where('campaign_id', $campaign->id)
            ->where('start_date', '=',  $startDateSchedule)
            ->where('end_date', '=' ,  $endDateSchedule)
            ->first();

        if(!$scheduleExisted) {

            // Create new Schedule in Campaign
            $createSchedule  = ScheduleCampaign::create([
                'campaign_id' => $campaign->id,
                'start_date' => $startDateSchedule,
                'end_date' => $endDateSchedule
            ]);

            $createSchedule->save();

            //Store
            if($activitiesNew != []) {
                foreach ($activitiesNew as $newActivity) {
                    $formData['name'] = $newActivity['name'];
                    $formData['slug'] = Str::slug(($formData['name']));
                    $formData['campaign_id'] = $campaign->id;
                    $formData['type_of_activity_id'] = $newActivity['typeOfActivityId'];

                    if(array_key_exists('description', $newActivity)) {
                        $formData['description'] = $newActivity['description'];
                    }

                    $activityCreated =  Activity::create($formData);
                    $activityCreated->save();

                    Facades\DB::table('activity_schedule_campaigns')->insert([
                        'activity_id' => $activityCreated->id,
                        'schedule_campaign_id' => $createSchedule->id
                    ]);


                }

            }

            if($activitiesExisted != []) {
               foreach ($activitiesExisted as $exitActivity) {
                   Facades\DB::table('activity_schedule_campaigns')->insert([
                       'activity_id' => $exitActivity->id,
                       'schedule_campaign_id' => $createSchedule->id
                   ]);
               }
            }

            // GET LIST ACTIVITY IN SCHEDULE
            $getActivities = Facades\DB::table('activities')
                ->join('activity_schedule_campaigns', 'activities.id', '=', 'activity_schedule_campaigns.activity_id')
                ->join('schedules_campaign', 'activity_schedule_campaigns.schedule_campaign_id', '=', 'schedules_campaign.id')
                ->where('schedules_campaign.id','=', $createSchedule->id)
                ->where('activities.campaign_id', '=', $campaign->id)
                ->select( 'activities.*')
                ->get();

            $data = $campaign;
            $data['schedule'] = ScheduleCampaign::find($createSchedule->id);
            $data['schedule']['activities'] = $getActivities;

            return response()->json([
                'success' => 'true',
                'message' => "Create Schedule Activity success",
                'data' => $data,

            ], 201);


        }
        else{
            //Store
            if($activitiesNew != []) {
                foreach ($activitiesNew as $newActivity) {
                    $formData['name'] = $newActivity['name'];
                    $formData['slug'] = Str::slug(($formData['name']));
                    $formData['campaign_id'] = $campaign->id;
                    $formData['type_of_activity_id'] = $newActivity['typeOfActivityId'];

                    if(array_key_exists('description', $newActivity)) {
                        $formData['description'] = $newActivity['description'];
                    }

                    $activityCreated =  Activity::create($formData);
                    $activityCreated->save();


                    Facades\DB::table('activity_schedule_campaigns')->insert([
                        'activity_id' => $activityCreated->id,
                        'schedule_campaign_id' => $scheduleExisted->id
                    ]);

                }

            }

            if($activitiesExisted != []) {
                foreach ($activitiesExisted as $exitActivity) {
                    $existedActivitySchedule = Facades\DB::table('activity_schedule_campaigns')
                        ->where('activity_id', '=', $exitActivity->id)
                        ->where('schedule_campaign_id', '=', $scheduleExisted->id)
                        ->first();

                    if(! $existedActivitySchedule){
                        Facades\DB::table('activity_schedule_campaigns')->insert([
                            'activity_id' => $exitActivity->id,
                            'schedule_campaign_id' => $scheduleExisted->id
                        ]);
                    }
                    else{
                        continue;
                    }
                }
            }

            // GET LIST ACTIVITY IN SCHEDULE
            $getActivities = Facades\DB::table('activities')
                ->join('activity_schedule_campaigns', 'activities.id', '=', 'activity_schedule_campaigns.activity_id')
                ->join('schedules_campaign', 'activity_schedule_campaigns.schedule_campaign_id', '=', 'schedules_campaign.id')
                ->where('schedules_campaign.id','=', $scheduleExisted->id)
                ->where('activities.campaign_id', '=', $campaign->id)
                ->select( 'activities.*')
                ->get();

            $data = $campaign;
            $data['schedule'] = ScheduleCampaign::find($scheduleExisted->id);
            $data['schedule']['activities'] = $getActivities;

            return response()->json([
                'success' => 'true',
                'message' => "Create Schedule Activity success",
                'data' => $data
            ], 201);

        }

    }

    public function checkCampaignNameIsExisted (Request  $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:campaigns,name'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Name is existed.'
            ], 400);
        }
        else{
            return response()->json([
                'success' => 'true',
                'message' => 'Name is validate.'
            ], 200);
        }
    }

    public function destroy($id ) {
        $campaign = Campaign::find($id);

        if(!$campaign) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }

        $activities = Activity::where('campaign_id', '=', $campaign->id)->get();

        if(count($activities) > 0) {
            return response()->json([
                'success'=> 'false',
                'message' => 'Can not delete because exist Activity in this Campaign'
            ], 401);
        }

        $schedule = ScheduleCampaign::where('campaign_id', '=', $campaign->id)->get();

        if(count($schedule) > 0) {
            return response()->json([
                'success' => 'false',
                'message' => 'Can not delete because exist Schedule in this Campaign'
            ], 401);
        }

        $thumbs = Campaign::with('Thumbnails');
        foreach ($thumbs as $thumb) {
            Facades\Storage::disk('')->delete($thumb->path);
            $thumb->delete();
        }

        $campaign->delete();
        return response()->json([
            'success' => 'true',
            'message' => 'Delete Campaign successfully'
        ], 201);
    }
}
