<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ScheduleCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleCampaignController extends Controller
{
    public function getAll() {
        $timetable = ScheduleCampaign::orderBy('campaign_id', 'asc')->get();

        return response()->json($timetable);
    }

    public function store(Request  $request) {
        $validator = Validator::make($request->all(), [
            'campaignId' => 'required|exists:campaigns,id',
            'startDate' => 'required|date|date_format:d-m-Y|before_or_equal:endDate',
            'endDate' => 'required|date|date_format:d-m-Y|after_or_equal:start'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' =>'Request Fail',
                'errors' => $validator->errors()
            ], 400);
        }
        $data = $validator->validated();

        $startDateSchedule = date('Y-m-d', strtotime($data['startDate']));
        $endDateSchedule = date('Y-m-d', strtotime($data['endDate']));

        $campaign = Campaign::find($data['campaignId']);
        $startDateCampaign = $campaign->start_date;
        $endDateCampaign = $campaign->end_date;

        if(( $startDateSchedule >= $startDateCampaign) & ($startDateSchedule <= $endDateCampaign)
            & ($endDateSchedule >= $startDateCampaign ) & ($endDateSchedule <= $endDateCampaign)
            &($startDateSchedule <= $endDateSchedule)) {


            //check start - end is exited in DB?
            $timetableExisted = ScheduleCampaign::where('start_date', $startDateSchedule)
                ->where('end_date', $endDateSchedule)->first();

            if(!$timetableExisted) {

                // create
                $timetable = ScheduleCampaign::create([
                    'campaign_id' => $data['campaignId'],
                    'start_date' => $startDateSchedule,
                    'end_date' => $endDateSchedule
                ]);

                $timetable->save();

                return response()->json([
                    'success'=> 'true',
                    'message' => 'Store Schedule for Campaign success'
                ], 201);

            }
            else{
                return response()->json([
                    'success' => 'false',
                    'message' => 'The Schedule is Exited'
                ], 400);
            }

        }
        else{
            $error = [];
            if( ($startDateSchedule < $startDateCampaign) | ($startDateSchedule > $endDateCampaign)) {
                $error[] = 'The start date must be equal or after start date Campaign';
            }

            if( ($endDateSchedule > $endDateCampaign) | ($endDateSchedule < $startDateCampaign)){
                $error[] = 'The end date must be equal or before end date Campaign';
            }

            if($startDateSchedule > $endDateSchedule) {
                $error[] = "Start date must before or equal end date";
            }


            if($startDateSchedule )
            return response()->json([
              'success' => 'false',
                'message' => "Date invalid",
                'errors' => $error
            ], 400);
        }


    }

    public function edit($id, Request $request) {
        $timetable = ScheduleCampaign::find($id);

        if(!$timetable) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not found'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'startDate' => 'date|date_format:d-m-Y|before_or_equal:endDate',
            'endDate' => 'date|date_format:d-m-Y|after_or_equal:startDate'
        ]);

        if($validator->fails()) {
            return response()->json(
                [
                    'success' => 'false',
                    'message' => 'Request fail',
                    'errors' => $validator->errors()
                ], 400
            );
        }
        $data = $validator->validated();

        $startDateSchedule = date('Y-m-d', strtotime($data['startDate']));
        $endDateSchedule = date('Y-m-d', strtotime($data['endDate']));

        $campaign = Campaign::where('id', $timetable->campaign_id)->first();
        $startDateCampaign = $campaign->start_date;
        $endDateCampaign = $campaign->end_date;

        if(( $startDateSchedule >= $startDateCampaign) & ($startDateSchedule <= $endDateCampaign)
            & ($endDateSchedule >= $startDateCampaign ) & ($endDateSchedule <= $endDateCampaign)
            &($startDateSchedule <= $endDateSchedule)) {


            //check start - end is exited in DB?
            $timetableExisted = ScheduleCampaign::where('start_date', $startDateSchedule)
                ->where('end_date', $endDateSchedule)->first();

            if(!$timetableExisted) {

                $timetable->update([
                    'start_date' => $startDateSchedule,
                    'end_date' => $endDateSchedule
                ]);

                return response()->json([
                    'success'=> 'true',
                    'message' => 'Store Schedule for Campaign success'
                ], 201);

            }
            else{
                return response()->json([
                    'success' => 'false',
                    'message' => 'The Schedule is Duplicate'
                ], 400);
            }

        }
        else{
            $error = [];
            if( ($startDateSchedule < $startDateCampaign) | ($startDateSchedule > $endDateCampaign)) {
                $error[] = 'The start date must be equal or after start date Campaign';
            }

            if( ($endDateSchedule > $endDateCampaign) | ($endDateSchedule < $startDateCampaign)){
                $error[] = 'The end date must be equal or before end date Campaign';
            }

            if($startDateSchedule > $endDateSchedule) {
                $error[] = "Start date must before or equal end date";
            }


            if($startDateSchedule )
                return response()->json([
                    'success' => 'false',
                    'message' => "Date invalid",
                    'errors' => $error
                ], 400);
        }


    }

    public  function destroy($id) {
       $timetable = ScheduleCampaign::find($id);

       if(!$timetable) {
           return response()->json([
               'success' => 'false',
               'message' => 'Not Found'
           ], 400);
       }

       $timetable->delete();

       return response()->json([
           'success' => 'true',
           'message' => 'Destroy success'
       ], 201);
    }
}
