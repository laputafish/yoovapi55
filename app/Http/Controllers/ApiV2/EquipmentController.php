<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Equipment;
use Illuminate\Support\Facades\Input;

class EquipmentController extends BaseController
{
    public function rules()
    {
        return [
        ];
    }

    public function index() {
        $rows = Equipment::all();
        return response()->json($rows);
    }

    public function update($id) {
        $equipment = Equipment::find($id);
        if (\Input::has('occupied_by')) {
            $equipment->occupied_by = \Input::get('occupied_by');
            $equipment->save();
        }
        return response()->json([
            'status'=>'ok'
        ]);
    }

}