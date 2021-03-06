<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\TaxFormHelper;
use App\Helpers\EventHelper;
use App\Helpers\OA\OAHelper;
use App\Helpers\FormHelper;
use App\Helpers\SampleNameHelper;
use App\Helpers\SampleHelper;
use App\Helpers\ZipHelper;
use App\Helpers\TempFileHelper;

use App\Models\FormEmployee;
use App\Models\IrdForm;
use App\Models\SampleForm;
use App\Models\Lang;
use App\Models\SampleFormEmployee;

class SampleFormController extends BaseAuthController
{
  protected $modelName = 'SampleForm';

  public function show($teamId) {
    $sampleForm = $this->team->sampleForm;
    if(is_null($sampleForm)) {
      $sampleForm = $this->team->sampleForm()->save(new SampleForm([
        'status' => 'pending',
        'lang_id' => 'en-us',
        'application_date' => date('yyyy-mm-dd'),
        'apply_printed_forms' => '',
        'apply_softcopies' => '',
        'processed_printed_forms' => '',
        'processed_softcopies' => '',
        'company_file_no' => '',
        'company_name' => '',
        'tel_no' => '',
        'signature_name' => '',
        'designation' => '',
        'fiscal_start_year' => 0,
        'is_update' => 0,
        'remark' => ''
      ]));
    } else {
      // Check printed forms
      $items = explode(',', $sampleForm->processed_printed_forms);
      $actualItems = [];
      foreach($items as $item) {
        switch($item) {
          case 'letter':
            if(file_exists($sampleForm->folder.'/letter.pdf')) {
              $actualItems[] = $item;
            }
            break;
          default:
            if(file_exists($sampleForm->folder.'/'.$item.'_sample.pdf')) {
              $actualItems[] = $item;
            }
        }
      }
      $sampleForm->processed_printed_forms = implode(',', $actualItems);
      $sampleForm->save();

      // Check softcopies
      $items = explode(',', $sampleForm->processed_softcopies);
      $actualItems = [];
      foreach($items as $item) {
        if(
          file_exists($sampleForm->folder.'/'.$item.'.xml') &&
          file_exists($sampleForm->folder.'/'.$item.'_sample.pdf') &&
          file_exists( $sampleForm->folder.'/'.$item.'_control_list.pdf')) {
          $actualItems[] = $item;
        }
      }
      $sampleForm->processed_softcopies = implode(',', $actualItems);
      $sampleForm->save();
    }

    return response()->json([
      'status'=>true,
      'result'=>$sampleForm
    ]);
  }

  public function store() {
    $command = \Input::get('command', '');
    switch($command) {
      case 'generate':
        $this->saveSampleForm();
        $this->generateSampleForm();
        break;
      case 'terminate':
        $this->terminateGeneration();
        break;
      default:
        $this->saveSampleForm();
        break;
    }
    return response()->json([
      'status'=>true
    ]);
  }

  public function saveSampleForm() {
    $sampleForm = $this->team->sampleForm;
    $data = [
      'lang_id' => \Input::get('lang_id'),
      'application_date' => \Input::get('application_date'),
      'apply_printed_forms' => \Input::get('apply_printed_forms', ''),
      'apply_softcopies' => \Input::get('apply_softcopies', ''),
      'company_file_no' => \Input::get('company_file_no'),
      'company_name' => \Input::get('company_name'),
      'tel_no' => \Input::get('tel_no'),
      'signature_name' => \Input::get('signature_name'),
      'designation' => \Input::get('designation'),
      'fiscal_start_year' => \Input::get('fiscal_start_year'),
      'is_update' => \Input::get('is_update'),
      'remark' => \Input::get('remark', '')
    ];
    if(is_null($sampleForm)) {
      $data['status'] = 'pending';
      $sampleForm = $this->team->sampleForm()->save(new SampleForm($data));
    } else {
      $this->team->sampleForm()->update($data);
    }
    return response()->json([
      'status'=>true
    ]);
  }

  public function terminateGeneration() {
    $sampleForm = $this->team->sampleForm;
    $sampleForm->status = 'terminated';
    $sampleForm->save();

    EventHelper::send('requestForm', ['sampleForm'=>$sampleForm]);

    return response()->json([
      'status'=>true
    ]);
  }

  public function generateSampleForm() {
    $sampleForm = $this->team->sampleForm;
    $sampleForm->status = 'ready_for_processing';
    $sampleForm->processed_printed_forms = '';
    $sampleForm->processed_softcopies = '';
    $sampleForm->save();

    // Clear folder
    emptyFolder($sampleForm->folder);

    // Push event
    EventHelper::send('requestForm', ['sampleForm'=>$sampleForm]);

    // Create sample employees
    $this->createSampleFormEmployees($sampleForm);
    return response()->json([
      'status'=>true
    ]);
  }

  private function createSampleFormEmployees($sampleForm) {
    // clear
    $sampleForm->employees()->delete();
    $isEnglish = $sampleForm->lang->code == 'en-us';

    for($i=0; $i<30; $i++) {
      $index = $i + 1;
      $nameInfo = SampleNameHelper::get();
      $maritalStatus = SampleHelper::getMaritalStatus($nameInfo['sex']);
      $employmentPeriod = SampleHelper::getEmploymentPeriod($sampleForm->fiscal_start_year);
      $period = phpDateFormat($employmentPeriod['startDate'], 'Ymd').'-'.
        phpDateFormat($employmentPeriod['endDate'], 'Ymd');

      $otherRaps = SampleHelper::getOtherRaps();

      $incomes = [
        'salary'=>rand(10,50)*1000, // 1
        'leavePay'=>getRandomForProbability(rand(1,10)*100, 0,0.1), // 2
        'directorFee'=>getRandomForProbability(rand(1,10)*10000, 0,0.05), // 3
        'commFee'=>getRandomForProbability(rand(1,10)*1000, 0,0.05), // 4
        'bonus'=>getRandomForProbability(rand(1,10)*1000, 0,0.05), // 5
        'bpEtc'=>getRandomForProbability(rand(1,10)*1000,0, 0.01), // 6
        'payRetire'=>getRandomForProbability(rand(1,10)*1000,0, 0.001), // 7
        'salTaxPaid'=>getRandomForProbability(rand(1,10)*1000, 0,0.001), // 8
        'eduBen'=>getRandomForProbability(rand(1,10)*1000,0, 0.001), // 9
        'gainShareOption'=>getRandomForProbability(rand(1,10)*1000,0, 0.001), // 10
        'pension'=>getRandomForProbability(rand(1,10)*1000, 0,0.001), // 10
        'otherRap1'=>$otherRaps[0]['amt'],
        'otherRap2'=>$otherRaps[1]['amt'],
        'otherRap3'=>$otherRaps[2]['amt'],

        // IR56E
        'monthlyFixedIncome'=>rand(10,50)*1000,
        'monthlyAllowance'=>rand(10,30)*1000,
        'fluctuatingIncome'=>rand(10,50)*1000,
        'shareBeforeEmp'=>rand(0,1)
      ];

      // IR56F
      $cessationReason = ['Resignation', 'Retirement', 'Dismissal', 'Death'][rand(0,3)];

      $resPlaceInfo = SampleHelper::getResPlaceInfo();
      // {
      //  placeOfResInd = 0 | 1
      //  places => [
      //     [
      //        addr_of_place
      //        nature_of_place
      //        per_of_place
      //        rent_paid_er
      //        rent_paid_ee
      //        rent_refund
      //        rent_paid_er_by_ee
      //     ],
      //     [
      //        addr_of_place
      //        nature_of_place
      //        per_of_place
      //        rent_paid_er
      //        rent_paid_ee
      //        rent_refund
      //        rent_paid_er_by_ee
      //     ]
      //  ]

      $employeeInfo = [
        'employee_id'=> $index,
        'sheet_no' => $index,
        'file' => '',
        'status' => 'pending',
        'hkid' => SampleHelper::getHkid(),
        'type_of_form'=>'O',
        'phone_num'=>rand(20000000,99999999),
        'surname' => $nameInfo['surname'],
        'given_name' => $nameInfo['givenName'],
        'name_in_chinese'=> $nameInfo['nameInChinese'],
        'sex'=>$nameInfo['sex'],
        'marital_status'=>$maritalStatus['married'] ? 2 : 1,
        'spouse_name'=>isset($maritalStatus['spouse']) ? $maritalStatus['spouse']['name'] : '',
        'spouse_hkid'=>isset($maritalStatus['spouse']) ? $maritalStatus['spouse']['hkid'] : '',
        'spouse_pp_num'=>isset($maritalStatus['spouse']) ? $maritalStatus['spouse']['ppNum'] : '',
        'res_addr'=>'Flat A, 1/F., First Bldg., 1st Street, Central.',
        'area_code_res_addr'=>['H','K','N','F'][rand(0,3)],
        'pos_addr'=>$isEnglish ? 'Same as Above' : '同上',
        'area_code_pos_addr'=>['H','K','N','F'][rand(0,3)],
        'capacity'=>SampleHelper::getCapacity(),
        'pt_prin_emp'=>getRandomForProbability('Main Company', 0,.1),
        'start_date_of_emp'=>phpDateFormat($employmentPeriod['startDate'], 'Ymd'),
        'end_date_of_emp'=>phpDateFormat($employmentPeriod['endDate'], 'Ymd'),
        // 1
        'per_of_salary'=>$period,
        'amt_of_salary'=>$incomes['salary'],
        // 2
        'per_of_leave_pay'=>$period,
        'amt_of_leave_pay'=>$incomes['leavePay'],
        // 3
        'per_of_director_fee'=>$period,
        'amt_of_director_fee'=>$incomes['directorFee'],
        // 4
        'per_of_comm_fee'=>$period,
        'amt_of_comm_fee'=>$incomes['commFee'],
        // 5
        'per_of_bonus'=>$period,
        'amt_of_bonus'=>$incomes['bonus'],
        // 6
        'per_of_bp_etc'=>$period,
        'amt_of_bp_etc'=>$incomes['bpEtc'],
        // 7
        'per_of_pay_retire'=>$period,
        'amt_of_pay_retire'=>$incomes['payRetire'],
        // 8
        'per_of_sal_tax_paid'=>$period,
        'amt_of_sal_tax_paid'=>$incomes['salTaxPaid'],
        // 9
        'per_of_edu_ben'=>$period,
        'amt_of_edu_ben'=>$incomes['eduBen'],
        // 10
        'per_of_gain_share_option'=>$period,
        'amt_of_gain_share_option'=>$incomes['gainShareOption'],
        // 11.1
        'per_of_other_rap1' => $period,
        'amt_of_other_rap1' => $incomes['otherRap1'],
        'nature_of_other_rap1' => $otherRaps[0]['nature'],
        // 11.2
        'per_of_other_rap2' => $period,
        'amt_of_other_rap2' => $incomes['otherRap2'],
        'nature_of_other_rap2' => $otherRaps[1]['nature'],
        // 11.3
        'per_of_other_rap3' => $period,
        'amt_of_other_rap3' => $incomes['otherRap3'],
        'nature_of_other_rap3' => $otherRaps[2]['nature'],
        // 12
        'per_of_pension'=>$period,
        'amt_of_pension'=>$incomes['pension'],

        'total_income'=>array_sum(array_values($incomes)),

        'place_of_res_ind'=>$resPlaceInfo['placeOfResInd'],

        'addr_of_place1'=>$resPlaceInfo['places'][0]['addr_of_place'],
        'nature_of_place1'=>$resPlaceInfo['places'][0]['nature_of_place'],
        'per_of_place1'=>$resPlaceInfo['places'][0]['per_of_place'],
        'rent_paid_er1'=>$resPlaceInfo['places'][0]['rent_paid_er'],
        'rent_paid_ee1'=>$resPlaceInfo['places'][0]['rent_paid_ee'],
        'rent_refund1'=>$resPlaceInfo['places'][0]['rent_refund'],
        'rent_paid_er_by_ee1'=>$resPlaceInfo['places'][0]['rent_paid_er_by_ee'],

        'addr_of_place2'=>$resPlaceInfo['places'][1]['addr_of_place'],
        'nature_of_place2'=>$resPlaceInfo['places'][1]['nature_of_place'],
        'per_of_place2'=>$resPlaceInfo['places'][1]['per_of_place'],
        'rent_paid_er2'=>$resPlaceInfo['places'][1]['rent_paid_er'],
        'rent_paid_ee2'=>$resPlaceInfo['places'][1]['rent_paid_ee'],
        'rent_refund2'=>$resPlaceInfo['places'][1]['rent_refund'],
        'rent_paid_er_by_ee2'=>$resPlaceInfo['places'][1]['rent_paid_er_by_ee'],

        'oversea_inc_ind'=>0,
        'amt_paid_oversea_co'=>0,
        'name_of_oversea_co'=>'',
        'addr_of_oversea_co'=>'',

        'amt_of_sum_withheld' => rand(0,100)*100,

        // IR56E
        'monthly_fixed_income' => $incomes['monthlyFixedIncome'],
        'monthly_allowance' => $incomes['monthlyAllowance'],
        'fluctuating_income' => $incomes['fluctuatingIncome'],
        'share_before_emp' => $incomes['shareBeforeEmp'],

        // IR56F
        'cessation_reason' => $cessationReason,

        'remarks'=>''
      ];

      $sampleForm->employees()->save(new SampleFormEmployee($employeeInfo));
    }
  }

  public function showFile($sampleFormId, $fileName, $contentDisposition='inline') {
    $sampleForm = SampleForm::find($sampleFormId);
    $path = $sampleForm->folder.'/'.$fileName;
    $fileContent = file_get_contents($path);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $contentType = \Config::get('content_types')[$ext]['type'];
    return response()->make($fileContent, 200, [
      'Content-Type' => $contentType,
      'Content-Disposition' => $contentDisposition.'; filename="'.$fileName.'"'
    ]);

  }

  public function outputLetter($sampleFormId) {
    $pdfFileName = 'letter.pdf';
    return $this->showFile($sampleFormId, $pdfFileName);
  }

  public function outputControlList($sampleFormId, $irdFormId) {
    $pdfFileName = strtolower($irdFormId).'_control_list.pdf';
    return $this->showFile($sampleFormId, $pdfFileName);
  }

  public function outputSample($sampleFormId, $irdFormId) {
    $pdfFileName = strtolower($irdFormId).'_sample.pdf';
    return $this->showFile($sampleFormId, $pdfFileName);
  }

  public function downloadDataFile($sampleFormId, $irdFormId) {
    $filename = strtolower($irdFormId).'.xml';
    return $this->showFile($sampleFormId, $filename, 'attachment');
  }

  public function downloadSchemeFile($sampleFormId, $irdFormId) {
    $filename = strtolower($irdFormId).'.xsd';
    return $this->showFile($sampleFormId, $filename, 'attachment');
  }

  public function downloadAllxxx($sampleFormId) {
    $sampleForm = SampleForm::find($sampleFormId);
    $folder = $sampleForm->folder;
    $allFiles = [];

    $processedSoftcopiesStr = trim($sampleForm->processed_softcopies);
    $irdCodes = [];
    if(!empty($processedSoftcopiesStr)) {
      $irdCodes = explode(',', $processedSoftcopiesStr);
      foreach($irdCodes as $irdCode) {
        $allFiles[] = [
          'source' => $folder . '/' . $irdCode . '.xml',
          'custom' => 'to_store/' . $irdCode . '.xml'
        ];
        $allFiles[] = [
          'source' => $folder . '/' . $irdCode . '.xsd',
          'custom' => 'to_store/' . $irdCode . '.xsd'
        ];
        $sampleFileName = $irdCode . '_sample.pdf';
        $allFiles[] = [
          'source' => $folder . '/' . $sampleFileName,
          'custom' => 'to_print/' . $sampleFileName
        ];
        $controlListFileName = $irdCode.'_control_list.pdf';
        $allFiles[] = [
          'source' => $folder . '/' .$controlListFileName,
          'custom' => 'to_print/'.$controlListFileName
        ];
      }
    }

    $processedPrintedFormsStr = trim($sampleForm->processed_printed_forms);
    if(!empty($processedPrintedFormsStr)) {
      $segs = explode(',', $processedPrintedFormsStr);
      foreach($segs as $seg) {
        switch($seg) {
          case 'letter':
            $allFiles[] = [
              'source' => $folder . '/letter.pdf',
              'custom' => 'to_print/letter.pdf'
            ];
            break;
          default:
            $irdCode = $seg;
            if(!in_array($irdCode, $irdCodes)) {
              $sampleFileName = $irdCode . '_sample.pdf';
              $allFiles[] = [
                'source' => $folder . '/' . $sampleFileName,
                'custom' => 'to_print/' . $sampleFileName
              ];
            }
        }
      }
    }
    $outputFileName = storage_path('app/teams/'.$sampleForm->team->oa_team_id.'/application_letters/'.$sampleForm->id.'/zipped.zip');
    ZipHelper::downloadFiles($allFiles, $outputFileName);
    unlink($outputFileName);
  }

  public function downloadAll($sampleFormId) {
    $sampleForm = SampleForm::find($sampleFormId);
    $folder = $sampleForm->folder;
    $allFiles = $sampleForm->allFiles;

    $outputFileName = storage_path('app/teams/'.$sampleForm->team->oa_team_id.'/application_letters/'.$sampleForm->id.'/zipped.zip');
    ZipHelper::downloadFiles($allFiles, $outputFileName);
    unlink($outputFileName);
  }

  public function prepareDownload($formId) {
    $sampleForm = SampleForm::find($formId);
    $filename = 'application_letter.zip';
    $allFiles = $sampleForm->allFiles;

    // Check if all files exists
    $result = true;
    foreach( $allFiles as $fileItem) {
      if(!file_exists($fileItem['source'])) {
        $result = false;
        break;
      }
    }
    if($result) {
      $tempFile = TempFileHelper::new($filename, $this->user->id);
      ZipHelper::createTempFile($allFiles, $tempFile->filename);
    }
    return response()->json([
      'status'=>$result,
      'key'=>$result ? $tempFile->key : 0
    ]);
  }
}
