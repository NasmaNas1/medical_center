<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubSpecialization;
use App\Models\Specialization;
use App\Http\Resources\SubSpecializationResource;
use App\Http\Traits\GeneralTrait;

class SubSpecializationController extends Controller
{
    use GeneralTrait;

    public function getBySpecialization($id){
            $specialization = Specialization::find($id);

             if (!$specialization) {
            return $this->notFoundResponse('لم يتم العثور على التخصص المطلوب.');
                }

                $subSpecializations = $specialization->subSpecializations;

        if ($subSpecializations->isEmpty()) {
            return $this->responseWithJson([], true, null, 200); // يرجع قائمة فاضية
        }

        return $this->responseWithJson(
            SubSpecializationResource::collection($subSpecializations),
            true,
            null,
            200
        );
    
    }
}
