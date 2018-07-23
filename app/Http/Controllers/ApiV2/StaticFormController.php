<?php namespace App\Http\Controllers\ApiV2;

use App\User;

use App\Models\Folder;
use App\Models\Media;
use App\Models\Document;
use App\Models\TaxForm;
use App\Models\FormCommencement;
use App\Models\FormTermination;
use App\Models\FormDeparture;
use App\Models\FormSalary;

use App\Helpers\FolderHelper;
use App\Helpers\MediaHelper;

use ZipArchive;
use Storage;
use Response;


class StaticFormController extends BaseController
{
  public function getFormImage($name)
  {
    $imagePath = '';
    switch($name) {
      case '0':
        $imagePath = base_path() . '/public/dist/img/forms/blank.png';
        break;
      case '1':
        $imagePath = base_path() . '/public/dist/img/forms/ir56b_pc_e.gif';
        break;
      default:
        $imagePath = $this->getFromFilename($name);
    }
    if (empty($imagePath)) {
      $imagePath = base_path() . '/public/dist/img/forms/blank_dot.gif';
    }
    $fileContent = file_get_contents($imagePath);
    $imageExt = pathinfo($imagePath, PATHINFO_EXTENSION);
    return response()->make($fileContent, 200)->header('Content-Type', 'image/' . $imageExt);
  }

  public function getFromFilename($name) {
    $exts = ['png', 'jpg', 'gif', 'jpeg'];
    $imagePath = '';
    foreach ($exts as $ext) {
      $fullPath = base_path() . '/public/dist/img/forms/' . $name . '.' . $ext;
      if (file_exists($fullPath)) {
        $imagePath = $fullPath;
        break;
      }
    }
    return $imagePath;
  }

}
