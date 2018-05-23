<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Meeting;
use Illuminate\Support\Facades\Input;

class MeetingController extends BaseController
{
    public function index()
    {
        $rows = Meeting::all();
        return response()->json($rows);
    }

    public function destroy($id)
    {
      $meeting = Meeting::find($id);

      $result = [
        'status' => 'fails',
        'message' => 'No meeting defined!'
      ];

      if(isset($meeting)) {
        $result = [
          'status' => 'ok',
          'message' => ''
        ];
        if ($meeting->venue_type == 'conference_room') {
          if ($meeting->roomBooking->status == 'pending') {
            $meeting->roomBooking->delete();
            parent::destroy($meeting);
          } else {
            $result = [
              'status' => 'fails',
              'message' => 'Meeting Room Booking has been approved. Please consult administator to cancel the booking first!'
            ];
          }
        }
      }

      return response()->json($result);
    }


}