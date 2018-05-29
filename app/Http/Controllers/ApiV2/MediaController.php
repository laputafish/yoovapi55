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

  public function getImage($id) {
    return $this->showImage($id);
  }

  public function showImage($id, $size = 'normal')
  {
    $defaultImageFolder = 'image';
    $media = Media::find($id);

    if (!is_null($media)) {
      $ext = pathinfo($media->filename, PATHINFO_EXTENSION);
      $pathPrefix = $media->is_temp ? 'temp' : $defaultImageFolder;
      switch (strtolower($ext)) {
        case 'jpg':
        case 'png':
        case 'gif':
        case 'jpeg':
          if ($media->type == 'image') {
            if (is_file(storage_path('app/image_' . $size . '/' . $media->path . '/' . $media->filename))) {
              $pathPrefix = 'image_' . $size;
            }
          }
          $fileContent = \Storage::get($pathPrefix . '/' . $media->path . '/' . $media->filename);
          return Response($fileContent, 200)->header('Content-Type', 'image/' . $ext);
        case 'pdf':
        case 'docx':
        case 'doc':
        case 'xls':
        case 'xlsx':
        case 'mp4':
        case 'txt':
          $filename = \Config::get('content_types')[$ext]['icon'];
          $fullPath = base_path() . '/public/dist/img/icons/' . $filename;
          $fileContent = file_get_contents($fullPath);
          $iconExt = pathinfo($filename, PATHINFO_EXTENSION);
          return Response::make($fileContent, 200)->header('Content-Type', 'image/' . $iconExt);
      }
    } else {
      return $this->imageResponse(storage_path('images/monkey.png'));
    }
  }

}