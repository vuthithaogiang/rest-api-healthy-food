<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductThumbnails;
use Illuminate\Http\Request;
use  Illuminate\Support\Facades;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('Thumbnails')
            ->with('Category')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($products);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|unique:products,name',
            'brand' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0.01',
            'quantity' => 'required|integer|min:1',
            'thumbs'=>'array|required',
            'thumbs.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'categoryId' => 'required|exists:category_products,id',
            'status' => Rule::in([0, 1, 2, 3])
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Request not valid',
                'errors' => $validator->errors(),
                'data' => []
            ]);
        }

        $slug = Str::slug($validator->validated()['name']);


        $product = Product::create([
            'name' => $request->get('name'),
            'slug' => $slug,
            'brand' => $request->get('brand'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'quantity' => $request->get('quantity'),
            'status' => $request->get('status'),
            'category_id' => $request->get('categoryId')
        ]);

        $product->save();

        $data = $validator->validated();


        if(array_key_exists('thumbs', $data)){

            $imageHashes = [];

            $uniqueImages = array_filter($data['thumbs'] , function ($image) use (&$imageHashes) {
                $imageHash = md5(file_get_contents($image->path()));

                if(in_array($imageHash, $imageHashes)) {
                    return false;
                }
                else{
                    $imageHashes[] = $imageHash;
                    return true;
                }
            });


            foreach ($uniqueImages as $thumb) {
                $imageName = time() . '_' . $thumb->getClientOriginalName();
                Facades\Storage::putFileAs('', $thumb, $imageName);

                $product->Thumbnails()->create([
                    'path' => $imageName
                ]);
            }

        }

        // GET LIST THUMBS OF PRODUCT
        $thumbnails = Facades\DB::table('products')
            ->join('product_thumbnails', 'products.id', '=', 'product_thumbnails.product_id')
            ->where('product_thumbnails.product_id','=', $product->id)
            ->select( 'product_thumbnails.*')
            ->get();

     return response()->json([
            'success' => 'true',
            'message' => 'Store Product Successfully',
            'data' => $product,
            'thumbs' => $thumbnails,
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {

       $product= Product::where("slug", $slug) ->first();

        if( !$product) {
            return response()->json([
                'success'=> 'false',
                'message'=>'Not Found',
                'data'=> []
            ], 400);
        }
        else{
            $thumbnails = Facades\DB::table('products')
                ->join('product_thumbnails', 'products.id', '=', 'product_thumbnails.product_id')
                ->where('product_thumbnails.product_id', '=', $product->id)
                ->select( 'product_thumbnails.*')
                ->get();
            return response()->json([
                'success'=> 'true',
                'message'=>'Successfully',
                'data'=> $product,
                'thumbnails' => $thumbnails

            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request  $request)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ]);
        }

        $validator = Facades\Validator::make($request->all(), [
            'name' => 'string|unique:category_products,name',
            'brand' => 'string',
            'price' => 'numeric|min:0.01',
            'quantity' => 'integer|min:1',
            'description' => 'string',
            'categoryId' => 'exists:category_products,id',
            'uploadThumbs' => 'array',
            'uploadThumbs.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleteThumbs' => 'array',
            'deleteThumbs.*' => 'integer|exists:product_thumbnails,id',
            'status' => Rule::in([0, 1, 2, 3, 4, 5]) // InAvailable, Available, Upcoming, NewArrival, Sold Out, Best Seller
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'message' => 'Request Fail',
                'errors' => $validator->errors()
            ]);
        }

        $data = $validator->validated();

        //HANDLE UPLOAD IMAGES
        if (array_key_exists('uploadThumbs', $data)) {

            $imageHashes = [];
            $uniqueImages = array_filter($data['uploadThumbs'], function ($image) use (&$imageHashes) {
                $imageHash = md5(file_get_contents($image->path()));

                if (in_array($imageHash, $imageHashes)) {
                    return false;
                } else {
                    $imageHashes[] = $imageHash;
                    return true;
                }
            });

            foreach ($uniqueImages as $thumb) {
                $imageName = time() . '_' . $thumb->getClientOriginalName();
                Facades\Storage::putFileAs('', $thumb, $imageName);

                $newThumb = ProductThumbnails::create([
                    'product_id' => $product->id,
                    'path' => $imageName
                ]);
                $newThumb->save();
            }
        }
        //HANDLE DELETE IMAGES with id
        if (array_key_exists('deleteThumbs', $data)) {
            foreach ($data['deleteThumbs'] as $thumbId) {
                $imageToDelete = ProductThumbnails::find($thumbId);

                if(!$imageToDelete) {
                    return response()->json([
                        'success' => 'false',
                        'message' => 'Not found image to delete'
                    ]);
                }

                if($imageToDelete->product_id == $product->id) {
                    Facades\Storage::disk('')->delete($imageToDelete->path);
                    $imageToDelete->delete();
                }
                else{
                    return response()->json([
                        'success' => 'false',
                        'message' => 'This image not belong to Product'
                    ]);
                }

            }

        }


        $slug = Str::slug($data['name']);

        $product->update([
            'name' => $request->get('name'),
            'slug' => $slug,
            'brand' => $request->get('brand'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'quantity' => $request->get('quantity'),
            'category_id' => $request->get('categoryId')
        ]);

        // GET LIST THUMBS OF PRODUCT
        $thumbnails = Facades\DB::table('products')
            ->join('product_thumbnails', 'products.id', '=', 'product_thumbnails.product_id')
            ->select( 'product_thumbnails.path')
            ->where('product_thumbnails.product_id', '=', $product->id)
            ->get();

        return response()->json([
            'success' => 'true',
            'message' => 'Update Product Successfully',
            'data' => $product,
            'thumbs' => $thumbnails,
        ]);

    }

    public function filter(Request  $request) {
         $validator = Facades\Validator::make($request->all(), [
             'brand' => 'string',
             'status' => Rule::in([0, 1, 2, 3, 4, 5]),
             'categoryId' => 'exists:category_products,id',
             'minPrice' => 'numeric',
             'maxPrice' => 'numeric'
         ]);

         if($validator->fails()) {
             return response()->json([
                 'success' => 'false',
                 'message' => 'Get fail',
                 'errors' => $validator->errors(),
                 'data' => []
             ]);
         }
         $data = $validator->validated();
         $query = Product::with('Thumbnails')
             ->with('Category');

         if(array_key_exists('brand', $data)) {
             $query->where('brand', 'like', '%'. $data['brand'].'%');
         }

         if(array_key_exists('minPrice', $data)){
             $query->where('price' , '>=' , $data['minPrice']);
         }

         if(array_key_exists('maxPrice', $data)) {
             $query->where('price', '<=', $data['maxPrice']);
         }

         if(array_key_exists('categoryId', $data)) {
             $query->where('category_id', $data['categoryId']);
         }

         if(array_key_exists('status', $data)) {
             $query->where('status', $data['status']);
         }

         $products = $query->get();

         return response()->json([
             'success' => 'true',
             'message' => 'Get success',
             'data' => $products
         ]);
    }

    public function searchByName(Request  $request) {
        $validator = Facades\Validator::make($request->all(), [
            'key' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => 'false',
                'message' => 'Key must be string and required',
                'data' => []
            ]);
        }

        $products = Product::with('Thumbnails')
            ->with('Category')->where('name', 'like', '%'. $request->get('key') .'%')->get();

        return response()->json([
            'success' => "true",
            "message" => "Get success",
            'data' => $products
        ]);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        $thumbs = Product::with('Thumbnails');
        foreach ($thumbs as $thumb) {
            Facades\Storage::disk('')->delete($thumb->path);
            $thumb->delete();
        }

        if(!$product) {
            return response()->json([
                'success' => 'false',
                'message' => 'Not Found'
            ], 400);
        }
        $product->delete();
        return response()->json([
            'success' => 'true',
            'message' => 'Destroy Success'

        ], 201);
    }
}
