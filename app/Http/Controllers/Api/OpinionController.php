<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpinionResource;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use App\Models\Opinion;
use Illuminate\Support\Facades\Validator;

class OpinionController extends Controller
{
    use GeneralTrait;
        

   public function opinion(Request $request){
    $opinions=opinion::create([
        'opinion'=>$request->opinion,
    ]);

    return $this->responseWithJson('شكرا لاضافة رأيك') ;
   }





    public function show(){
        $opinions= Opinion::all();
        return $this->responseWithJson(
            new OpinionResource($opinions),
            true
        );

    }
}
