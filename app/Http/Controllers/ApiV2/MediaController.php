<?php namespace App\Http\Controllers\ApiV2;

use App\User;
use App\Models\Folder;
use App\Models\Media;

use App\Helpers\FolderHelper;

class MediaController extends BaseController {
  public function getIcon($id) {
    $media = Media::find($id);
    if ($media->is_image) {
      $iconPath = $media->getPath('xs');
      // if image, use thumbnails
    } else {
      // if not image, use document type icon
      $iconPath = MediaHelper::getIconPath($media->file_type);
    }
    $fileContent = file_get_contents( $iconPath );
    $iconExt = pathinfo( $iconPath, PATHINFO_EXTENSION );
    return response()->make($fileContent, 200)->header('Content-Type', 'image/' . $iconExt);
  }
}