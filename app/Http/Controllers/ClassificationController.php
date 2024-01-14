<?php

namespace App\Http\Controllers;

use App\Http\Resources\classificationResource;
use App\Models\Classification;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return classificationResource::collection(Classification::all());
    }
}
