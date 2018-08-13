<?php namespace App\Models;

class Form extends BaseIRDForm {
  protected $modelName = 'Form';
  protected $employeeModelName = 'FormEmployee';

  protected $fillable = [
    'team_id',
    'form_no',
    'form_date',
    'lang_id',
    'status',
    'subject',
    'published',
    'ird_form_type_id',
    'ird_form_id',
    'fiscal_start_year',
    'remark',
    'signature_name',
    'designation'
  ];

  public function irdFormType() {
    return $this->belongsTo('App\Models\IrdFormType');
  }

  public function irdForm() {
    return $this->belongsTo( 'App\Models\IrdForm');
  }

  public function prepareFolder() {
    $folder = storage_path('app/teams/'.$this->team->oa_team_id.'/'.$this->id );
    if(!file_exists($folder)) {
      mkdir($folder, 0777, TRUE);
    }
    emptyFolder($folder);
  }

  public function getFolderAttribute() {
    return storage_path('app/teams/'.$this->team->oa_team_id.'/'.$this->id );
  }

  public function getAllFilesAttribute() {
    $result = [];
    $formEmployees = $this->employees;
    $folderPath = storage_path('app/teams/'.$this->team->oa_team_id.'/'.$this->id.'/');
    foreach ($formEmployees as $employee) {
      $path = $folderPath.$employee->file;
      $result[] = [
        'source' => $path,
        'custom' => pathinfo($path, PATHINFO_BASENAME)
      ];
    }
    if(isset($this->irdForm)) {
      $irdForm = $this->irdForm;
      if($irdForm->requires_control_list) {
        $this->appendAttachment($result, $folderPath, 'control_list.pdf');
      }
      if($irdForm->can_use_softcopy) {
        $this->appendAttachment($result, $folderPath,strtolower($this->irdForm->ird_code).'.xml');
        $this->appendAttachment($result, $folderPath,strtolower($this->irdForm->ird_code).'.xsd');
      }

    }
    return $result;
  }

  private function appendAttachment(&$result, $folderPath, $filename) {
    $filePath = $folderPath.$filename;
    if(file_exists($filePath)) {
      $result[] = [
        'source' => $filePath,
        'custom' => $filename
      ];
    }
  }

  public function getAttachments() {
    $result = [];
    if(isset($this->irdForm)) {
      $folderPath = storage_path(
          'app/teams/' .
          $this->team->oa_team_id . '/' .
          $this->id) . '/';

      if ($this->irdForm->requires_control_list) {
        $controlListFilePath = $folderPath . 'control_list.pdf';
        if (file_exists($controlListFilePath)) {
          $result[] = [
            'labelTag' => 'control_list',
//            'url' => '/media/ird_forms/' . $this->id . '/control_list',
            'url' => '/forms/' . $this->id . '/control_list/prepare',
            'iconType' => 'pdf'
          ];
        }
      }
      if ($this->irdForm->can_use_softcopy) {
        $dataFile = $folderPath . strtolower($this->irdForm->ird_code) . '.xml';
        if (file_exists($dataFile)) {
          $result[] = [
            'labelTag' => 'xml_data_file',
  //          'url' => '/media/ird_forms/' . $this->id . '/data_file',
            'url' => '/forms/' . $this->id . '/data_file/prepare',
            'iconType' => 'xml'
          ];
        }
        $xsdFile = $folderPath . strtolower($this->irdForm->ird_code) . '.xsd';
        if (file_exists($dataFile)) {
          $result[] = [
            'labelTag' => 'xsd_file',
//            'url' => '/media/ird_forms/' . $this->id . '/schema_file',
            'url' => '/forms/' . $this->id . '/scheme_file/prepare',
            'iconType' => 'xsd'
          ];
        }

      }
    }
    return $result;
  }
}