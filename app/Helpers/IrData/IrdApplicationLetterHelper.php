<?php namespace App\Helpers\IrData;

use App\Models\IrdForm;
use App\Helpers\IrdFormHelper;

class IrdApplicationLetterHelper {
  public static function build($outputFolder, $team, $sampleForm) {
    $applySoftcopiesStr = $sampleForm->apply_softcopies;
    $applySoftcopies = explode(',',trim($applySoftcopiesStr));

    $applyPrintedFormsStr = $sampleForm->apply_printed_forms;
    $applyPrintedForms = explode(',', trim($applyPrintedFormsStr));

    $data = [
      'ApplySoftcopy' => tickIfExists($applySoftcopiesStr),
      'ApplySoftcopyIR56B' => tickIfExists('ir56b', $applySoftcopies),
      'ApplySoftcopyIR56M' => tickIfExists( 'ir56m', $applySoftcopies),
      'UpdatePreviouslyApproval' => tickIfTrue($sampleForm->is_update),

      'ApplyPrintedForms' => tickIfExists($applyPrintedFormsStr),
      'ApplyPrintedFormsIR56E' => tickIfExists('ir56e', $applyPrintedForms),
      'ApplyPrintedFormsIR56F' => tickIfExists( 'ir56f', $applyPrintedForms),
      'ApplyPrintedFormsIR56G' => tickIfExists('ir56g', $applyPrintedForms),
      'ApplyPrintedFormsIR56M' => tickIfExists('ir56m', $applyPrintedForms),

      'CompanyFileNo' => $sampleForm->company_file_no,
      'SignatureName' => $sampleForm->signature_name,
      'Designation' => $sampleForm->designation,
      'ApplicationDate' => $sampleForm->application_date,
      'CompanyName' => $sampleForm->company_name,
      'TelNo' => $sampleForm->tel_no
    ];

    $irdForm = IrdForm::whereFormCode('Letter')->first();
    $langCode = $sampleForm->lang->code;
    $irdFormFile = $irdForm->getFile($langCode);
    $fields = $irdFormFile->fields;
    $templateFile = storage_path('forms/'.$irdFormFile->file);
    $outputFile = $outputFolder.'/letter.pdf';

    IrdFormHelper::buildPdf([
      'title'=>'Application Letter',
      'data'=>$data,
      'fields'=>$fields,
      'templateFile'=>$templateFile,
      'outputFile'=>$outputFile,

      'topOffset'=>$irdFormFile->top_offset,
      'rightMargin'=>$irdFormFile->right_margin
    ]);
  }

  public static function getPath($sampleForm, $file)
  {
    $team = $sampleForm->team;

    $path = storage_path(
      'app/teams/' .
      $team->oa_team_id .
      '/application_letters/' .
      $sampleForm->id . '/' .
      $file);
    checkCreateFolder($path);
    return $path;
  }
}