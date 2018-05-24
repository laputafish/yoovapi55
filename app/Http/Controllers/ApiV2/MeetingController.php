<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Meeting;
use Illuminate\Support\Facades\Input;

class MeetingController extends BaseController
{
    public function rules() {
      return [
        'user_id' => 'required',
        'meeting_room_booking_id' => 'optional|integer',
        'started_at' => '',
        'ended_at' => '',
        'subject' => '',
        'venue_type' => 'in:conference_room,else',
        'venue' => 'optional',
        'remark' => 'String'
      ];
    }

    public function index()
    {
        $rows = Meeting::all();
//        foreach( $rows as $row ) {
//          if($row->venue_type == 'conference_room') {
//            if(isset($row->room_booking)) {
//              $row->room_booking->meeting_room = $row->roomBooking->meeting_room;
//              // $row->roomBooking->meetingRoom()->first();
////              print_r( $room );
////              $roomBooking = $row->room_booking;
////              $meetingRoom = $roomBooking->meetingRoom->toArray();
////              print_r( $meetingRoom );
//              // print_r( $roomBooking->meetingRoom()->firsttoArray() );
////              print_r( $row->room_booking->toArray() );
////              dd('ok');
////              print_r( $row->room_booking->toArray());
////              dd('ok');
////              $row->room_booking->meetingRoom();
//            }
//          }
//        }
//        print_r( $rows->toArray() );
//        dd('ok');
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

    public function update($id) {
      $data = \Input::only(array_keys($this->rules));

      $validator = Validator::make( $data, $this->rules() );

      if($validator->fails()) {
        return response()->json([
          'status'=>'fails',
          'messages'=>$validator->errors()
        ]);
      }

      $meeting = Meeting:;find($id);
      if(isset($meeting)) {
        if($meeting->venue_type == 'conference_room') {
          $roomBooking = \Input::get('room_booking');
          if (is_null($roomBooking)) {
            return response()->json([
              'status'=>'fails',
              'message'=>'No room booking is made!'
            ]);
          }
          if($roomBooking->id == 0) {
            if ($meeting->meeting_room_booking_id != 0) {
              $this->removeBooking($meeting->meeting_room_booking_id);
            }
            $booking = $this->createBooking( $roomBooking);
            $meeting->booking()->save($booking);
          }
          else {
            // assume the roombooking id follows the meeting without change
            $booking = MeetingRoomBooking::find($roomBooking->id);
            $booking->update($roomBooking);
          }
        }
        $meeting->update($data);
      }
      else {
        // new meeting

      }
    }

    public function store() {

    }

}