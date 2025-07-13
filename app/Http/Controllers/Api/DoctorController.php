<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Http\Resources\DoctorResource;
use App\Http\Traits\GeneralTrait;

class DoctorController extends Controller
{
    use GeneralTrait;
    
    public function index($id){
      try {
        $Doctor = Doctor::findOrFail($id);
        return $this->responseWithJson(
            new DoctorResource($Doctor),  
            true
        );
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return $this->responseWithJson(
            null,
            false,
            ['message' => 'المريض غير موجود'],
            404
        );
    } catch (\Exception $e) {
        return $this->responseWithJson(
            null,
            false,
            ['message' => 'حدث خطأ غير متوقع'],
            500
        );
    }
    }

    public function getBySpecialization($specialization_id)
    {
        $doctors = Doctor::where('specialization_id', $specialization_id)
            ->with('specialization')
            ->get();

        if ($doctors->isEmpty()) {
           return $this->responseWithJson(
            null,
            false,
            ['message' => 'لا يوجد اطباء لهذا الاختصاص'],
            404
        );
        }

        return   $this->responseWithJson(
             DoctorResource::collection($doctors),  
            true
        );
    }

    
}
