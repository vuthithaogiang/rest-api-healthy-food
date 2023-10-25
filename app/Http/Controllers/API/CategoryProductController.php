<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;

class CategoryProductController extends Controller
{
    public function getAll() {
        return CategoryProduct::all();
    }

    public function store() {
        return 'Store';
    }

    public function edit() {
        return "Edit";
    }

    public function inAvailable() {
        return "Delete soft";
    }

    public function restore() {
        return "Restore";
    }

    public function destroy() {
        return "Destroy";
    }
}
