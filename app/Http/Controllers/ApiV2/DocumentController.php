<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Document;
use App\Helpers\MediaHelper;

class DocumentController extends BaseController
{
  public function destroy($id)
  {
    $document = Document::find($id);
    MediaHelper::deleteMedia($document->media_id);
    $document->delete();
    return response()->json([
      'status' => 'ok'
    ]);
  }

  public function store() {
    if(\Input::has('command')) {
      return $this->processCommand();
    }
  }

  public function processCommand() {
    $command = \Input::get('command');
    switch($command) {
      case 'DELETE':
        $ids = \Input::get('ids');
        foreach($ids as $i=>$id) {
          $document = Document::find($id);
          MediaHelper::deleteMedia($document->media_id);
          $document->delete();
        }
        break;
    }
    return response()->json([
      'status'=>'ok'
    ]);
  }
}