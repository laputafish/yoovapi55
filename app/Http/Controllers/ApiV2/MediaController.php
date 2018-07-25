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
use App\Models\Form;

use App\Helpers\FolderHelper;
use App\Helpers\MediaHelper;

use ZipArchive;
use Storage;
use Response;


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

  public function uploadDocument() {
    $outputDir = base_path('storage/app/temp'); //"uploads/";
    // dd('FILES[file] = ' . $_FILES["file"]);
    if (isset($_FILES["file"])) {
      //Filter the file types , if you want.
      if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "		";
      } else {
        $originalName = $_FILES['file']['name'];
        $filename = $this->createFilename($originalName);
        $partialPath = $this->createPartialPath($filename);
        $outputPath = $outputDir . '/' . $partialPath . '/' . $filename; //$_FILES["file"]["name"];
        mkdir($outputDir . '/' . $partialPath, 0777, true);
        move_uploaded_file($_FILES["file"]["tmp_name"], $outputPath);
        $media = $this->addMedia($filename, $partialPath, 'temp');
        $fileType = pathinfo($originalName, PATHINFO_EXTENSION);

        $tags = [];
        try {
          if ($fileType == 'docx') {
            $systemTagNames = OfferDocumentHelper::getDynamicTags();
            $tagNames = [];
            $tagNames = ConversionHelper::getTags(
              MediaHelper::getMediaPath(
                $media->id));
            foreach ($tagNames as $tagName) {
              $tags[] = [
                'id' => 0,
                'name' => $tagName,
                'default' => '',
                'placeholder' => in_array($tagName, $systemTagNames) ? '(Auto)' : ''
              ];
            }
          }
        } catch( \Exception $e ) {
          abort(500);
          return;
        }

        return Response::json([
          'status' => 'ok',
          'imageId' => $media->id,
          'filename' => pathinfo($originalName, PATHINFO_FILENAME),
          'fileType' => $fileType,
          'tags' => $tags
        ]);
      }
    }
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

  function showTaxForm($id)
  {
    $taxForm = TaxForm::find($id);
    $team = $taxForm->team;
    $user = $taxForm->user;
    $fiscalYear = $taxForm->fiscal_year;
    $employeeId = $taxForm->employee_id;

    $filePath ='/tax_forms/'.$team->oa_team_id.'/'.$fiscalYear.'/'.$employeeId.'.pdf';
    $fileContent = Storage::get($filePath);
    $contentType = \Config::get('content_types')['pdf']['type'];
    $filename = $this->getTaxFormFilename($fiscalYear, $user);
    return response()->make($fileContent, 200, [
      'Content-Type' => $contentType,
      'Content-Disposition' => 'inline; filename="' . $filename.'"'
    ]);
  }

  function showIrdForm($formId, $employeeId) {
    $form = Form::find($formId);
    $team = $form->team;
    $teamEmployee = $team->employees()->whereId($employeeId)->first();
    $irdForm = $form->irdForm;
    $formEmployee = $form->employees()->whereEmployeeId($employeeId)->first();
    $filePath = '/teams/'.$team->oa_team_id.'/'.$form->id.'/'.$formEmployee->file;
    $fileContent = Storage::get($filePath);
    $contentType = \Config::get('content_types')['pdf']['type'];
    $filename = $this->getIrdFormFilename($irdForm, $teamEmployee, startYear2FiscalYearLabel($form->fiscal_start_year));
    return response()->make($fileContent, 200, [
      'Content-Type' => $contentType,
      'Content-Disposition' => 'inline; filename="' . $filename.'"'
    ]);
  }

  private function getIrdFormFilename($irdForm, $teamEmployee, $fiscalYearLabel=null) {
    $segs = [];
    $segs[] = $irdForm->ird_code;
    if($irdForm->requires_fiscal_year && !is_null($fiscalYearLabel)) {
      $segs[] = $fiscalYearLabel;
    }

    $username = 'employee_'.$teamEmployee->id;
    if (!empty($teamEmployee->display_name)) {
      $username = $teamEmployee->display_name;
    } else {
      $firstLastName = $this->firstLastName($teamEmployee);
      if(!empty($firstLastName)) {
        $username = $firstLastName;
      }
    }
    $segs[] = strtolower($username);

    return strtolower(implode('_', $segs)).'.pdf';
  }

  private function getTaxFormFilename($fiscalYear, $user) {
    $segs = [];
    $segs[] = 'ir56b';
    $segs[] = substr($fiscalYear,-2).'-'.substr($fiscalYear + 1,-2);

    $username = $user->name;
    if (!empty($user->display_name)) {
      $username = $user->display_name;
    } else {
      $firstLastName = $this->firstLastName($user);
      if(!empty($firstLastName)) {
        $username = $firstLastName;
      }
    }
    $segs[] = strtolower($username);

    return strtolower(implode('_', $segs)).'.pdf';
  }

  private function firstLastName( $user ) {
    $segs = [];
    if(!empty($user->first_name)) {
      $segs[] = $user->first_name;
    }
    if(!empty($user->last_name)) {
      $segs[] = $user->last_name;
    }
    return count($segs)>0 ? implode('_', $segs) : '';
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
          $contentType = \Config::get('content_types')[$ext]['type'];

          return Response::make($fileContent, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $media->filename.'"'
          ]);
          break;
      }
    } else {
      return $this->imageResponse(storage_path('images/monkey.png'));
    }
  }

  public function showPdfFile( $path, $filename=null ) {
    if(is_null($filename)) {
      $filename = path_info($path, PATHINFO_FILENAME);
    }
    $fileContent = Storage::get($path);
    $contentType = \Config::get('content_types')['pdf']['type'];

    return Response::make($fileContent, 200, [
      'Content-Type' => $contentType,
      'Content-Disposition' => 'inline; filename="' . $filename.'"'
    ]);
  }

  public function showIrdFormFile( $team, $form, $employee ) {
    $storageAppPath = '/teams/'.$team->oa_team_id.'/commencements/'.$form->id.'/'.$employee->file;
    $path = storage_path( '/app'.$storageAppPath );
    if(file_exists($path)) {
      return $this->showPdfFile($storageAppPath, $employee->file);
    } else {
      return $this->getDefaultIcon('pdf');
    }
  }

  public function showCommencementForm($formId, $employeeId) {
    $ok = false;
    $form = FormCommencement::find($formId);
    if(isset($form)) {
      $team = $form->team;
      $employee = $form->employees()->whereEmployeeId($employeeId)->first();
      if(isset($employee)) {
        $fileStatus = $employee->status;
        if ($fileStatus == 'ready') {
          return $this->showIrdFormFile( $team, $form, $employee );
        }
      }
    }
    return $this->getDefaultIcon('pdf');
  }

  public function showSalaryForm($formId, $employeeId) {

  }
  public function showDepartureForm($formId, $employeeId) {

  }
  public function showTerminationForm($formId, $employeeId) {

  }
}