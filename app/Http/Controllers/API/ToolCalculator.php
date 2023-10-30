<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ToolCalculator extends Controller
{
    public function getBMI(Request  $request) {

        $validator = Validator::make($request->all(), [
            'weight' => 'required|numeric', // Weight in kilograms
            'height' => 'required|numeric' // Height in centimeters
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' =>'false',
                'message' => 'Get BMI fail.',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();

        $height = $data['height'] / 100;
        $bmi = $data['weight'] / ($height * $height);

        $bmi = number_format($bmi, 2);

        // Define BMI categories and messages
        $bmiCategories = [
            ['min' => 0, 'max' => 18.5, 'message' => 'Slim'],
            ['min' => 18.5, 'max' => 24.9, 'message' => 'Normal'],
            ['min' => 25, 'max' => 29.9, 'message' => 'Overweight'],
            ['min' => 30, 'max' => 34.9, 'message' => 'Obese (Class 1)'],
            ['min' => 35, 'max' => 39.9, 'message' => 'Obese (Class 2)'],
            ['min' => 40, 'max' => PHP_INT_MAX, 'message' => 'Morbidly Obese'],
        ];
        // Determine the BMI category based on the calculated BMI
        $bmiCategory = null;
        foreach ($bmiCategories as $category) {
            if ($bmi >= $category['min'] && $bmi <= $category['max']) {
                $bmiCategory = $category['message'];
                break;
            }
        }

        $response['bmi'] = $bmi;
        $response['bmiCategory']  = $bmiCategory;


        return response()->json([
            'success' => 'true',
            'message' => 'Get BMI success',
            'data' => $response
        ], 201);
    }

    public function getCaloriesInDay(Request  $request){

        $validator = Validator::make($request->all(), [
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'age' => 'required|integer',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'activity' => ['required', Rule::in(['sedentary',  'light', 'moderate', 'active' ,  'veryActive'])]

        ]);
        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Get Calories in day fail',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();


        $weight = $data['weight'];
        $age = $data['age'];
        $height = $data['height'];
        $calories = 0;

        if(array_key_exists('gender', $data)) {
            if($data['gender'] == 'male') {
                $calories = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
            }
            else if($data['gender'] == 'female') {
                $calories = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
            }
        }

        // Adjust for activity level
        $activityLevels = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very active' => 1.9,
        ];

        $activityLevel = $data['activity'];
        $calories *= $activityLevels[$activityLevel];

        $response['caloInDay'] = $calories;
        $response['goalIncrease'] = $calories + 500;
        $response['goalDecrease'] = $calories - 500;

        return response()->json([
            'success' => 'true',
            'message' => 'Get Calories in day success',
            'data' => $response
        ], 201);
    }
}
