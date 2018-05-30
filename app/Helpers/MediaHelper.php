<?php namespace App\Helpers;

use App\Models\Media;
use App\Helpers\SystemSettingHelper;

use Carbon\Carbon;

class MediaHelper
{
  public static function deleteMedia($param)
  {
    $media = is_numeric($param) ? Media::find($param) : $param;
    if (!is_null($media)) {
      if ($media->type == 'temp') {
        self::deleteMediaFiles('temp/' . $media->path, $media->filename);
      } else {
        self::deleteMediaFiles('image/' . $media->path, $media->filename);
        self::deleteMediaFiles('image_xs/' . $media->path, $media->filename);
        self::deleteMediaFiles('image_sm/' . $media->path, $media->filename);
      }
      $media->delete();
    }
  }

  public static function isImage( $mediaObj )
  {
    $ext = strtolower(self::getMediaExt($mediaObj));
    return in_array($ext, ['png', 'jpg', 'jpeg', 'gif']);
  }

  public static function getMediaExt( $mediaObj )
  {
    $media = is_int($mediaObj) ?
      Media::find($mediaId) :
      $mediaObj;

    $filename = $media->filename;
    return pathinfo( $filename, PATHINFO_EXTENSION );
  }

  public static function createTempMedia($file) {
    self::createMedia($file, true /* temp */);
  }

  public static function createMedia($file, $isTemp=false) {
    $targetFolder = $isTemp ? 'temp' : DocumentHelper::getFileTypeFolder($file);

    $outputDir = base_path('storage/app/'.$targetFolder); //"uploads/";

    $filename = $file->getFilename();
    $partialPath = self::createPartialPath($filename);
    $outputPath = $outputDir . '/' . $partialPath . '/' . $filename;
    if(!file_exists($outputDir.'/'.$partialPath)) {
      mkdir($outputDir . '/' . $partialPath, 0777, true);
    }
    rename( $file->getPathname(), $outputPath );

    $media = new Media();
    $media->is_temp = 0;
    $media->path = $partialPath;
    $media->filename = $filename;
    $media->user_id = 0;
    $media->save();

    self::createThumbnail( $media, 'image_xs');
    self::createThumbnail( $media, 'image_sm');
    return $media;
  }

  public static function createPartialPath($filename)
  {
    $md5 = md5($filename);
    return substr($md5, 0, 2) . '/' . substr($md5, 2, 2);
  }

  public static function getFullPath( $mediaId ) {
    $media = Media::find($mediaId);
     $pathPrefix = $media->is_image ? 'image' : 'doc';
    return storage_path('app/'.$pathPrefix.'/'.$media->path.'/'.$media->filename );
  }

  public static function createThumbnail( $media, $mediaPath)
  {
    $sizeW = SystemSettingHelper::get( $mediaPath.'_size_w', 320 );
    $sizeH = SystemSettingHelper::get( $mediaPath.'_size_h', 320 );
    $targetFolder = storage_path('app/' . $mediaPath .'/'.$media->path );

    if(!file_exists( $targetFolder )) {
      mkdir($targetFolder, 0755, true);
    }
    $targetPath = $targetFolder . '/' . $media->filename;
    if(!file_exists( $targetPath )) {
      $imagePath = storage_path('app/image/' . $media->path . '/' . $media->filename);
      if(file_exists( $imagePath) ) {
        $imageDetails = getimagesize($imagePath);
        $originalWidth = $imageDetails[0];
        $originalHeight = $imageDetails[1];

        if ($originalWidth > $originalHeight) {
          $newWidth = $sizeW;
          $newHeight = intval($originalHeight * $newWidth / $originalWidth);
        } else {
          $newHeight = $sizeH;
          $newWidth = intval($originalWidth * $newHeight / $originalHeight);
        }

        if ($imageDetails[2] == IMAGETYPE_GIF) {
          $imgt = "imagegif";
          $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($imageDetails[2] == IMAGETYPE_JPEG) {
          $imgt = "imagejpeg";
          $imgcreatefrom = "ImageCreateFromJPEG";
        }
        if ($imageDetails[2] == IMAGETYPE_PNG) {
          $imgt = "imagepng";
          $imgcreatefrom = "ImageCreateFromPNG";
        }

        if ($imgt) {
          $oldImage = $imgcreatefrom($imagePath);
          $newImage = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresized($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
          $imgt($newImage, $targetPath);
        }
      }
      return true;
    }
    else
      return false;
  }

  public static function deleteMediaFiles($mediaPath, $filename)
  {
    $path = storage_path('app/' . $mediaPath);
    $filePath = $path . '/' . $filename;
    if (file_exists($filePath)) {
      unlink($filePath);
    }
    self::removeMediaPathIfEmpty($path);
  }

  private static function removeMediaPathIfEmpty($path)
  {
    if (file_exists($path)) {
      if (self::is_dir_empty($path)) {
        rmdir($path);
      }
    }
    $parentPath = dirname($path);
    if (file_exists($parentPath)) {
      if (self::is_dir_empty($parentPath)) {
        rmdir($parentPath);
      }
    }
  }

  public static function changeImageType($param, $type)
  {
    $media = is_numeric($param) ? Media::find($param) : $param;

    if (!is_null($media)) {
      $oldMediaType = $media->type;
      if ($oldMediaType != $type) {
        self::moveFile($media, $type);
        $media->type = $type;
        $media->save();
        if($media->is_image) {
          self::createThumbnail($media, 'image_sm');
          self::createThumbnail($media, 'image_xs');
        }
      }
    }
  }

  // remove temp image over 1 day
  public static function removeExpiredUserTempImages($user)
  {
    $now = date('Y-m-d H:i:s');
    $oneDayAgo = date('Y-m-d H:i:s', strtotime($now) - 60 * 60 * 24);

    $mediaIds = Media::whereType('temp')->whereUserId($user->id)->where('created_at', '<', $oneDayAgo)->lists('id')->toArray();
    foreach ($mediaIds as $mediaId) {
      self::deleteMedia($mediaId);
    }
  }

  public static function getMediaPath($id)
  {
    $result = '';
    $media = Media::find($id);

    if (!is_null($media)) {
      $result = $media->getPath();
    }
    return $result;
  }

  public static function is_dir_empty($dir)
  {
    if (!is_readable($dir)) {
      return NULL;
    } else {
      return (count(scandir($dir)) == 2);
    }
  }

  public static function cascadePurgeMedia($param)
  {
    $media = is_numeric($param) ? Media::find($param) : $param;

    self::moveFile($media, 'image');
  }

  public static function getMediaFiles( $folder, $partialPath = '' ) {
    $result = [];
    $dir = $folder.'/*';
    $files = glob( $dir );
    foreach( $files as $file ) {
      $filename = pathinfo( $file, PATHINFO_FILENAME );
      $extension = pathinfo( $file, PATHINFO_EXTENSION );
      if(is_dir($file)) {
        $result = array_merge( $result, self::getMediaFiles( $file, $partialPath.$filename.'/' ));
      }
      else {
        $result[] = $partialPath.$filename.'.'.$extension;
      }
    }
    return $result;
  }


  private static function purgeMediaPath( $mediaPath, $existingFilePaths, &$fileList ) {
    $allFiles = self::getMediaFiles( storage_path( 'app/'.$mediaPath ) );
    foreach( $allFiles as $file ) {
      if(!in_array($file, $existingFilePaths )) {
        $filename = basename( $file );
        $path = $mediaPath.'/'.dirname( $file );
        self::deleteMediaFiles( $path, $filename );
        $fileList[] = $path.'/'.$filename;
      };
    }
  }

  public static function purge() {
    $summary = [
      'orphan_records'=>[
        'caption'=>'Removed orphans media records',
        'items'=>[]
      ],
      'orphan_files'=>[
        'caption'=>'Removed orphan files',
        'items'=>[]
      ],
      'thumbnail_creation'=>[
        'caption'=>'Thumbnail Creation',
        'items'=>[]
      ]
    ];

    // clear orphans records with files
    $orphans = Media::has( 'candidate', '=', 0)
      ->has('material','=', 0)
      ->has('contractDocument', '=', 0)
      ->has('company', '=', 0)
      ->get();
    foreach( $orphans as $orphan ) {
      MediaHelper::deleteMedia( $orphan );
      $summary['orphan_records']['items'][] = 'Media#' . $orphan->id .
        ' ['.$orphan->type.'] '.
        $orphan->path.'/'.$orphan->filename.' ('.$orphan->created_at->toDateTimeString().')';
    }

    // remove files without media record
    $medias = Media::all();
    $records = [];
    foreach( $medias as $media ) {
      $records[] = $media->path .'/'.$media->filename;
    }
    foreach( ['temp','image','image_sm','image_xs'] as $mediaPath ) {
      self::purgeMediaPath($mediaPath, $records, $summary['orphan_files']['items']);
    }

    // create thumbnails image into image_xs, image_sm
    foreach( $medias as $media ) {
      $str = '';
      if($media->type=='temp') {
        MediaHelper::changeImageType( $media, 'image' );
        $str .= '[TEMP > MEDIA] ';
      }
      if($media->is_image) {
        if(MediaHelper::createThumbnail( $media, 'image_sm', 320, 320 )) {
          $str .= 'image(sm)created ';
        }

        if(MediaHelper::createThumbnail( $media, 'image_xs', 80, 80 )) {
          $str .= 'image(xs)created ';
        }
      }
      if(!empty($str)) {
        $summary['thumbnail_creation']['items'][] = 'Media#'.$media->id.': '.$str;
      }
    }
    return $summary;
  }

  public static function formatPurgeSummary( $summary ) {
    $output = '';

    foreach( $summary as $section=>$info ) {
      $output .= '<h4>'.$info['caption'].' ('.count($info['items']).')</h4>';
      $output .= '<ol>';
      foreach( $info['items'] as $item ) {
        $output .= '<li>'.$item.'</li>';
      }
      $output .= '</ol>';
    }
    return $output;
  }

  public static function checkMediaPath( $path ) {
    // path = 'temp/sd/sd/sldkfjsdlfjkds.jpg'
    //
    $targetPath = storage_path('app/' . $path );
    return file_exists( $targetPath );
  }

  public static function check() {
    $result = [
      'records'=>[
        'count'=>0,
        'items'=>[]
      ],
      'missingFileGroups'=>[
        'temp'=>[],
        'image'=>[],
        'image_sm'=>[],
        'image_xs'=>[]
      ],
      'fileGroups'=>[
        'temp'=>[],
        'image'=>[],
        'image_sm'=>[],
        'image_xs'=>[]
      ]
    ];

    // Check orphan records
    $orphanRecords = Media::has( 'candidate', '=', 0)
      ->has('material','=', 0)
      ->has('contractDocument', '=', 0)
      ->has('company', '=', 0)
      ->get();
    foreach( $orphanRecords as $orphanRecord ) {
      $result['records']['count']++;
      $result['records']['items'][] =
        '#'.$orphanRecord->id.': '.
        $orphanRecord->path.'/'.$orphanRecord->filename.
        ' ('.$orphanRecord->created_at->toDateTimeString().')';
    }

    // Check missing files
    $medias = Media::all();
    $allValidPaths = [];
    foreach( $medias as $media ) {
      $partialPath = $media->path.'/'.$media->filename;
//			if($media->id==15) {
//				dd( $media->type );
//			}
      if($media->type=='temp') {
        $allValidPaths[] = 'temp/'.$partialPath;

        if(!self::checkMediaPath( 'temp/'.$partialPath)) {
          $result['missingFileGroups']['temp'][] = '#'.$media->id.': temp/'.$partialPath.
            ' ('.$media->created_at->toDateTimeString().') '.
            self::getRelatedModuleRecord( $media->id );
        };
      }
      else { // image (non-temp)
        if($media->is_image) {

          foreach( $result['missingFileGroups'] as $mediaFolder=>$files ) {
            if($mediaFolder=='temp') {
              continue;
            }
            $allValidPaths[] = $mediaFolder.'/'.$partialPath;
            if(!self::checkMediaPath( $mediaFolder.'/'.$partialPath )) {
              $result['missingFileGroups'][$mediaFolder][] =
                '#'.$media->id.': '.$partialPath.
                ' ('.$media->created_at->toDateTimeString().') '.
                self::getRelatedModuleRecord( $media->id );
            };
          }
        }
        else {
          $allValidPaths[] = 'image/'.$partialPath;
          if(!self::checkMediaPath( 'image/'.$partialPath )) {
            $result['missingFileGroups']['image'][] =
              '#'.$media->id.': '.$partialPath.
              ' ('.$media->created_at->toDateTimeString().') '.
              self::getRelatedModuleRecord( $media->id );
          };
        }
      }
    }

    // Check files without records
    foreach( $result['fileGroups'] as $mediaFolder=>$files ) {
      $allFiles = self::getMediaFiles( storage_path( 'app/'.$mediaFolder ) );
      foreach( $allFiles as $file ) {
        if(!in_array($mediaFolder.'/'.$file, $allValidPaths )) {
          $result['fileGroups'][$mediaFolder][] =
            $mediaFolder.'/'.$file;
        };
      }
    }

    return $result;
  }

  public static function getRelatedModuleRecord( $mediaId ) {
    $result = [];

    // Material
    $materials = Material::whereImageId( $mediaId )->get();
    foreach( $materials as $material ) {
      $result[] = 'Material#'.$material->id.' '.$material->name;
    }

    // Contract Document
    $documents = ContractDocument::whereMediaId( $mediaId )->get();
    foreach( $documents as $document ) {
      $contract = $document->contract;
      $result[] = 'Contract#'.$contract->id.' '.$contract->candidate->name;
    }

    // Company Logo
    $companies = Company::whereLogoImageId( $mediaId )->get();
    foreach( $companies as $company ) {
      $result[] = 'Company#'.$company->id.' '.$company->short_name;
    }


    $output = '';
    if(count($result)==1) {
      $output = $result[0];
    }
    elseif( count($result)>1) {
      $output = '<ol>';
      foreach( $result as $item ) {
        $output .= '<li>'.$item.'</li>';
      }
      $output .= '</ol>';
    }

    return $output;
  }

  public static function formatMediaCheckSummary( $result ) {
    // Orphan media records without links to modules
    $output = '<h4>Orphan Records (without link to modules): ('.$result['records']['count'].')</h4>';
    $output .= '<ol>';
    foreach( $result['records']['items'] as $item ) {
      $output .= '<li>'.$item.'</li>';
    }
    $output .= '</ol>';

    // Files missing for media records
    $output .= '<h4>Media Records Missing Files:</h4>';
    $output .= '<ol>';
    foreach( $result['missingFileGroups'] as $mediaFolder=>$files ) {
      $output .= '<li>'.$mediaFolder;
      $output .= '<ol>';
      for($i=0; $i<count( $files ); $i++) {
        $output .= '<li>'.$files[$i].'</li>';
      }
      $output .= '</ol>';
      $output .= '</li>';
    }
    $output .= '</ol>';

    // Files without media records
    $output .= '<h4>Files without media records:</h4>';
    $output .= '<ol>';
    foreach( $result['fileGroups'] as $mediaFolder=>$files ) {
      $output .= '<li>'.$mediaFolder;
      $output .= '<ol>';
      for($i=0; $i<count( $files ); $i++) {
        $output .= '<li>'.$files[$i].'</li>';
      }
      $output .= '</ol>';
      $output .= '</li>';
    }
    $output .= '</ol>';

    return $output;
  }

  private static function moveFile($param, $type)
  {
    $media = is_numeric($param) ? Media::find($param) : $param;

    $prefixPath = $media->type == 'temp' ?
      'temp' :
      'image';
    $newPrefixPath = $type == 'temp' ?
      'temp' :
      'image';

    $oldFolder = base_path('storage/app/' . $prefixPath . '/' . $media->path);
    $newFolder = base_path('storage/app/' . $newPrefixPath . '/' . $media->path);
    $oldPath = AppHelper::platformSlashes($oldFolder . '/' . $media->filename);
    $newPath = AppHelper::platformSlashes($newFolder . '/' . $media->filename);

    if (is_writable($oldPath)) {
      if (!file_exists(dirname($newPath))) {
        mkdir(dirname($newPath), 0770, true);
      }
      rename($oldPath, $newPath);
    }

    cascadePurgeFolders($oldFolder, base_path('storage/app/' . $prefixPath));
  }


  public static function rotateImage( $imageId, $angle ) {
    // Degree = number of degree to rotate the image anticlockwise
    $media = Media::find( $imageId );
    if(self::isImage($media)) {
      if ($media->type == 'temp') {
        self::rotateImageFile('temp/' . $media->path . '/' . $media->filename, $angle );
      } else {
        self::rotateImageFile('image/'.$media->path.'/'.$media->filename, $angle );
        self::rotateImageFile('image_sm/'.$media->path.'/'.$media->filename, $angle );
        self::rotateImageFile('image_xs/'.$media->path.'/'.$media->filename, $angle );
      }
    }
  }

  public static function rotateImageFile( $partialPath, $angle ) {
    $originalPath = storage_path( 'app/'.$partialPath );
    $ext = pathinfo( $originalPath, PATHINFO_EXTENSION );
    $tempPath = storage_path( 'app/temp/'.uniqid().'.'.$ext );

    self::createRotatedImageFile( $originalPath, $tempPath, $angle );
    unlink( $originalPath );
    rename( $tempPath, $originalPath );
  }

  public static function createRotatedImageFile( $sourcePath, $targetPath, $angle ) {
    $ext = pathinfo( $sourcePath, PATHINFO_EXTENSION );
    switch( $ext ) {
      case 'png':
        $source = imagecreatefrompng($sourcePath);
        $rotated = imagerotate( $source, $angle, 0 );
        imagepng( $rotated, $targetPath );
        //file_put_contents( $targetPath, $rotated );
        break;
      case 'jpg':
      case 'jpeg':
        $source = imagecreatefromjpeg($sourcePath);
        $rotated = imagerotate( $source, $angle, 0 );
        imagejpeg( $rotated, $targetPath );
        //file_put_contents( $targetPath, $rotated );
        break;
      case 'gif':
        $source = imagecreatefromgif($sourcePath);
        $rotated = imagerotate( $source, $angle, 0 );
        imagegif( $rotated, $targetPath );
        //file_put_contents( $targetPath, $rotated );
        break;
    }
  }

  public static function getIconPath( $type ) {
    $mediaTypeIconFolder = base_path().'/public/dist/img/icons/media_types/';
    switch (strtolower($type)) {
      case 'jpg':
      case 'png':
      case 'gif':
      case 'jpeg':
        $filename = 'image.png';
        break;
      case 'pdf':
        $filename = 'pdf.png';
        break;
      case 'docx':
      case 'doc':
        $filename = 'doc.png';
        break;
      case 'xls':
      case 'xlsx':
        $filename = 'xls.png';
        break;
      case 'txt':
        $filename = 'txt.png';
        break;
      case 'mp3':
        $filename = 'mp3.png';
        break;
      case 'ppt':
        $filename = 'ppt.png';
        break;
      case 'zip':
        $filename='zip.png';
        break;
      default:
        $filename = 'unknown.png';
    }
    return $mediaTypeIconFolder.$filename;
  }
}
