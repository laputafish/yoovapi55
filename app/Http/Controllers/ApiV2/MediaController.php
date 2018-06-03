<?php namespace App\Http\Controllers\ApiV2;

use App\User;
use App\Models\Folder;
use App\Models\Media;
use App\Models\Document;
use Storage;
use Response;
use App\Helpers\FolderHelper;
use App\Helpers\MediaHelper;
use ZipArchive;

class MediaController extends BaseController
{

  public function getDefaultIcon($name) {
    $iconPath = MediaHelper::getIconPathByName($name);
    $fileContent = file_get_contents($iconPath);
    $iconExt = pathinfo($iconPath, PATHINFO_EXTENSION);
    return response()->make($fileContent, 200)->header('Content-Type', 'image/' . $iconExt);
  }

  public function getIcon($id)
  {
    $media = Media::find($id);
    if ($media->is_image) {
      $iconPath = $media->getPath('xs');
      // if image, use thumbnails
    } else {
      // if not image, use document type icon
      $iconPath = MediaHelper::getIconPath($media->file_type);
    }
    $fileContent = file_get_contents($iconPath);
    $iconExt = pathinfo($iconPath, PATHINFO_EXTENSION);
    return response()->make($fileContent, 200)->header('Content-Type', 'image/' . $iconExt);
  }

  public function getImage($id)
  {
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

  public function downloadDocumentsInZip($documentIdsStr) {
    $ids = explode(',', $documentIdsStr);
    $tempFolder = storage_path( 'app/temp' );
    if(!is_dir($tempFolder)) {
      mkdir($tempFolder, 0777, true);
    }
    $zipname = date('Ymd_His').'.zip';
    $zipFilePath = $tempFolder.'/'.$zipname;
    $zip = new ZipArchive;
    $zip->open($zipFilePath, ZipArchive::CREATE);
    foreach ($ids as $id) {
  //    echo 'id = '.$id; nl();
      $document = Document::find($id);
      $filePath = MediaHelper::getFullPath($document->media_id);
      $basename = pathinfo($filePath, PATHINFO_BASENAME);
      $zip->addFile($filePath, $basename);
//      echo 'basename = '.$basename; nl();
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename="'.$zipname.'"');
    header('Content-Length: ' . filesize($zipFilePath));
    readfile($zipFilePath);
    unlink($zipFilePath);
  }

  public function downloadDocument($id, $filename='') {
    $ids = explode(',',$id);
    if(count($ids)>1) {
      return $this->zipAndDownload($ids);
    }
    $media = Media::find($id);
    if (!is_null($media)) {
      $ext = pathinfo($media->filename, PATHINFO_EXTENSION);
      $pathPrefix = '';
      switch (strtolower($ext)) {
        case 'jpg':
        case 'png':
        case 'gif':
        case 'jpeg':
          $pathPrefix .= $media->is_temp ? 'temp' : 'image';
          $fileContent = Storage::get($pathPrefix . '/' . $media->path . '/' . $media->filename);
          return Response::make($fileContent, 200, [
            'Content-Type' => \Config::get('content_types')[$ext]['type'],
            'Content-Disposition' => 'attachment;filename="' . $media->filename.'"'
          ]);
//          return Response($fileContent, 200)->header('Content-Type', 'image/' . $ext);
        case 'pdf':
        case 'docx':
        case 'doc':
        case 'xls':
        case 'xlsx':
        case 'txt':
          $path = $pathPrefix . 'doc/' . $media->path . '/' . $media->filename;
          $fileContent = Storage::get($path);
          $contentType = \Config::get('content_types')[$ext]['type'];
          return Response::make($fileContent, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment;filename="' . $media->filename.'"'
          ]);
          break;
      }
    }
  }

  function showDocument($id)
  {
    $media = Media::find($id);
    if (!is_null($media)) {
      $ext = pathinfo($media->filename, PATHINFO_EXTENSION);
      $pathPrefix = '';
      switch (strtolower($ext)) {
        case 'jpg':
        case 'png':
        case 'gif':
        case 'jpeg':
          $pathPrefix .= $media->is_temp ? 'temp' : 'image';
          $fileContent = Storage::get($pathPrefix . '/' . $media->path . '/' . $media->filename);
          return Response($fileContent, 200)->header('Content-Type', 'image/' . $ext);
        case 'pdf':
        case 'docx':
        case 'doc':
        case 'xls':
        case 'xlsx':
        case 'txt':
          $path = $pathPrefix . 'doc/' . $media->path . '/' . $media->filename;
          $fileContent = Storage::get($path);
          return Response::make($fileContent, 200, [
            'Content-Type' => \Config::get('content_types')[$ext]['type'],
            'Content-Disposition' => 'inline; filename="' . $media->file
          ]);
          break;
      }
    } else {
      return $this->imageResponse(storage_path('images/monkey.png'));
    }
  }

}