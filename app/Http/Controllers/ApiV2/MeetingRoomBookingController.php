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
    $room = MeetingRoom::find($id);
    $input = Input::all();
    $room->update( $input );
    return response()->json([
      'status'=>'ok'
    ]);
  }

  public function store() {
    if (\Input::has('booking')) {
      $data = \Input::get('booking');
      $booking = MeetingRoomBooking::create([
        'applicant_name'=>$data['applicant_name'],
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
    MeetingRoom::whereId($id)->delete();
    return response()->jsoN([
      'status'=>'ok'
    ]);
  }
}
