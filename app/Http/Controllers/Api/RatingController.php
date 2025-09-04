<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Appointment;
use App\Models\Rating;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Http\Resources\RatingResource;
use App\Http\Resources\DoctorAverageRatingResource;


use Illuminate\Support\Facades\Validator;


class RatingController extends Controller
{
    use GeneralTrait;

    public function rateAppointment(Request $request, $appointmentId)
    {
        $validator = Validator::make($request->all(), [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->responseWithJson(null, false, $validator->errors()->first(), 400);
        }

        $appointment = Appointment::find($appointmentId);
        if (!$appointment) {
            return $this->responseWithJson(null, false, 'الموعد غير موجود', 404);
        }

        
        // تأكد أن الموعد مكتمل
        if ($appointment->status !== 'completed') {
            return $this->responseWithJson(null, false, 'لا يمكنك التقييم إلا بعد إتمام الموعد', 403);
        }

        // تحقق إذا تم تقييم الموعد سابقاً
        $rating =Rating::updateOrCreate(
            ['appointment_id' => $appointmentId],
            [
                'rating'  => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return $this->responseWithJson(new RatingResource($rating),
                                           true);
    }

    public function getDoctorAverageRating($doctorId)
    {
        $doctor =Doctor::find($doctorId);
        if (!$doctor) {
            return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
        }

        $avgRating = $doctor->Ratings()->avg('rating');

        $doctor->average_rating = $avgRating ?: 0;

    return $this->responseWithJson(
        new DoctorAverageRatingResource($doctor),
        true,
    );
    }

}
