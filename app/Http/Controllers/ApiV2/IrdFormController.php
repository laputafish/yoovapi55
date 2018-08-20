<?php namespace App\Http\Controllers\ApiV2;

class IrdFormController extends BaseAuthController
{
  protected static $BLANK_FORM_ICON = '/public/dist/img/forms/blank.png';
  protected static $SAMPLE_FORM_ICON = '/public/dist/img/forms/ir56b_pc_e.gif';

  protected $modelName = 'IrdForm';
  protected $esentialWith = 'IrdFormType';
  protected $requireTeamFilter = false;

  protected function addSortOrder($query, $mapping=[]) {
    return parent::addSortOrder($query, ['ird_form_type_name'=>'name']);
  }

  protected function addJoins($query) {
    $query->leftjoin('ird_form_types as ift', 'ift.id', '=', 'ird_forms.ird_form_type_id');
    return $query;
  }
  public function showFormIcon($employeeFormId)
  {
    $imagePath = '';
    switch ($employeeFormId) {
      case '0':
        $imagePath = base_path() . self::$BLANK_FORM_ICON;
        break;
      case '1':
        $imagePath = base_path() . self::$SAMPLE_FORM_ICON;
        break;
      default:
        $formEmployee = $this->getFormEmmployee($employeeFormId);
        $imagePath = $formEmployee->status === 'ready' ?
          base_path() . self::$SAMPLE_FORM_ICON :
          base_path() . self::$BLANK_FORM_ICON;
    }
    $fileContent = file_get_contents($imagePath);
    $imageExt = pathinfo($imagePath, PATHINFO_EXTENSION);
    return response()->make($fileContent, 200)->header('Content-Type', 'image/' . $imageExt);
  }

  public function showFormPdf($employeeFormId)
  {
    $imagePath = '';
    switch ($employeeFormId) {
      case '0':
        $imagePath = base_path() . '/public/dist/img/forms/blank.png';
        break;
      case '1':
        $imagePath = base_path() . '/public/dist/img/forms/ir56b_pc_e.gif';
        break;
      default:
        $imagePath = $this->getFromEmployeeFormId($employeeFormId);
    }
    if (empty($imagePath)) {
      $imagePath = base_path() . '/public/dist/img/forms/blank_dot.gif';
    }
    $fileContent = file_get_contents($imagePath);
    $imageExt = pathinfo($imagePath, PATHINFO_EXTENSION);
    return response()->make($fileContent, 200)->header('Content-Type', 'image/' . $imageExt);
  }

  public function getPathFromEmployeeFormId($employeeFormId)
  {
    $segs = explode(',', $employeeFormId);
    $formId = (int) $segs[0];
    $form = Form::whereFormId($formId)->first();
    $team = $form->team;
    $employeeId = (int) $segs[1];

    $formEmployee = FormEmployee::whereFormId($formId)->whereEmployeeId($employeeId)->first();
    if($formEmployee->status === 'ready') {
      $file = storage_path('app/teams/'.$team->oa_team_id.'/'.$form->id.'/'.$formEmployee->file);
    } else {
      $file = '';
    }
    return $file;
  }
}
