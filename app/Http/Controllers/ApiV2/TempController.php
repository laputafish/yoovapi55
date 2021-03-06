<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TempFile;

use App\Helpers\DownloadHelper;

class TempController extends BaseController {
  public function download($key)
  {
    $tempFile = TempFile::where('key', $key)->first();
    $path = storage_path('app/temp/'.$tempFile->filename);
    $filename = $tempFile->label.'.'.pathinfo($path, PATHINFO_EXTENSION);
    DownloadHelper::download($path, $filename);
    unlink($path);
    TempFile::where('key', $key)->delete();
  }

  public function show($key)
  {
    $tempFile = TempFile::where('key', $key)->first();
    if(isset($tempFile)) {
      $path = storage_path('app/temp/' . $tempFile->filename);
      $filename = $tempFile->label . '.' . pathinfo($path, PATHINFO_EXTENSION);
      DownloadHelper::show($path, $filename);
      unlink($path);
      TempFile::where('key', $key)->delete();
    } else {
      return response()->view('blank');
    }
  }
}