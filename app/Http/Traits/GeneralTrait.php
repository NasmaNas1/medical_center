<?php

namespace App\Http\Traits;

use App\Models\ContactDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

trait GeneralTrait
{
    public function responseWithJson($data = null, bool $status = true, $error = null, $statusCode = 200)
    {
        $array = [
            'data' => $data,
            'status' => $status ,
            'error' => $error,
            'statusCode' => $statusCode
        ];
        return response($array, $statusCode);

    }
    public function unAuthorizeResponse(){

        return $this->responseWithJson(null, false ,'unauthorize', 401);
    }

    public function notFoundResponse($more){

        return $this->responseWithJson(null , false , $more , 404);
    }

    public function requiredField($message = null){

        return $this->responseWithJson(null , false , $message , 400);
    }

    public function forbiddenResponse(){
        
        return $this->responseWithJson(null , false ,'forbidden',403);
    }


 
}
