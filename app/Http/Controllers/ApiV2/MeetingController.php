<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Support\Facades\Input;

class MeetingController extends Controller
{
    public function index()
    {
        $rows = Meeting::all();
        return response()->json($rows);
    }
}