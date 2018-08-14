<?php namespace App\Models;

class SampleForm extends BaseIRDForm {
  protected $modelName = 'SampleForm';
  protected $employeeModelName = 'SampleFormEmployee';

  protected $fillable = [
    'team_id',
    'status',
    'message',
    'lang_id',
    'application_date',
    'apply_printed_forms',
    'apply_softcopies',
    'processed_apply_printed_forms',
    'processed_apply_softcopies',
    'company_file_no',
    'company_name',
    'tel_no',
    'signature_name',
    'designation',
    'fiscal_start_year',
    'is_update',
    'remark'
  ];

  public function irdFormType() {
    return $this->belongsTo('App\Models\IrdFormType');
  }

  public function irdForm() {
    return $this->belongsTo( 'App\Models\IrdForm');
  }

  public function getFolderAttribute() {
    return $this->getFolder();
  }

  private function getFolder() {
    $team = $this->team;
    return storage_path('app/teams/'.$team->oa_team_id.'/application_letters/'.$this->attributes['id']);
  }

  public function getAllFilesAttribute() {
    $folder = $this->getFolder();
    $result = [];

    $processedSoftcopiesStr = trim($this->attributes['processed_softcopies']);
    if(!empty($processedSoftcopiesStr)) {
      $irdCodes = explode(',', $processedSoftcopiesStr);
      foreach($irdCodes as $irdCode) {
        $result[] = [
          'source' => $folder . '/' . $irdCode . '.xml',
          'custom' => 'to_store/' . $irdCode . '.xml'
        ];
        $result[] = [
          'source' => $folder . '/' . $irdCode . '.xsd',
          'custom' => 'to_store/' . $irdCode . '.xsd'
        ];
        $sampleFileName = $irdCode . '_sample.pdf';
        $result[] = [
          'source' => $folder . '/' . $sampleFileName,
          'custom' => 'to_print/' . $sampleFileName
        ];
        $controlListFileName = $irdCode.'_control_list.pdf';
        $result[] = [
          'source' => $folder . '/' .$controlListFileName,
          'custom' => 'to_print/'.$controlListFileName
        ];
      }
    }

    $processedPrintedFormsStr = trim($this->attributes['processed_printed_forms']);
    if(!empty($processedPrintedFormsStr)) {
      $segs = explode(',', $processedPrintedFormsStr);
      foreach($segs as $seg) {
        switch($seg) {
          case 'letter':
            $result[] = [
              'source' => $folder . '/letter.pdf',
              'custom' => 'to_print/letter.pdf'
            ];
            break;
          default:
            $irdCode = $seg;
            if(!in_array($irdCode, $irdCodes)) {
              $sampleFileName = $irdCode . '_sample.pdf';
              $result[] = [
                'source' => $folder . '/' . $sampleFileName,
                'custom' => 'to_print/' . $sampleFileName
              ];
            }
        }
      }
    }
    return $result;
  }

}