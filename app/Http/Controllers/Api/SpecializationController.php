<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use App\Http\Resources\SpecializationResource;
use App\Http\Traits\GeneralTrait;

class SpecializationController extends Controller
{
    use GeneralTrait;

    public function index()
    {
        try {
            $specializations = Specialization::all();
            
            
            $data = class_exists(SpecializationResource::class) 
                    ? SpecializationResource::collection($specializations)
                    : $specializations;
            
            return $this->responseWithJson(
                data: $data,
                status: true,
                error: null,
                statusCode: 200
            );
            
        } catch (\Exception $e) {
            return $this->responseWithJson(
                data: null,
                status: false,
                error: 'حدث خطأ أثناء جلب الاختصاصات',
                statusCode: 500
            );
        }
    }
}