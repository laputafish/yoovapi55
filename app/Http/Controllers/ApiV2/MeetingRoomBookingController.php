<?php namespace App\Http\Controllers\ApiV2;

use App\Models\MeetingRoom;
use App\Models\MeetingRoomBooking;
use Illuminate\Support\Facades\Input;

class MeetingRoomBookingController extends BaseController
{
  protected $rules = [
    'applicant_name'=>'String',
    'description'=>'String',
    'id'=>'String',
    'started_at'=>'String',
    'ended_at'=>'String'
  ];

  public function index()
  {
    $rows = MeetingRoomBooking::all();
    return response()->json($rows);
  }

  public function update($id) {
    $booking = MeetingRoomBooking::find($id);
    $data = \Input::get('booking');
    if(isset($booking)) {
      $booking->update([
        'started_at' => $data['started_at'],
        'ended_at' => $data['ended_at'],
        'meeting_room_id'=>$data['meeting_room_id'],
        'description' => $data['description']
      ]);
    }
    return response()->json([
      'status'=>'ok'
    ]);
  }

  public function store() {
    if (\Input::has('booking')) {
      $data = \Input::get('booking');
      $booking = MeetingRoomBooking::create([
        'applicant_id'=>$data['applicant_id'],
        'meeting_room_id'=>$data['meeting_room_id'],
        'started_at'=>$data['started_at'],
        'ended_at'=>$data['ended_at'],
        'description'=>$data['description']
      ]);
    }
    return response()->json([
      'status'=>'ok'
    ]);
  }

  public function destroy($id)
  {
    MeetingRoomBooking::whereId($id)->delete();
    return response()->json([
      'status'=>'ok'
    ]);
  }
}
