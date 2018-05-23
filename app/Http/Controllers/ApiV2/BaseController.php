<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;

class BaseController extends Controller {
  public function destroy($record) {
    $record->delete();
  }
}