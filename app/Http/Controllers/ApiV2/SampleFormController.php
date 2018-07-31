<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\TaxFormHelper;
use App\Helpers\EventHelper;
use App\Helpers\OA\OAHelper;
use App\Helpers\FormHelper;
use App\Helpers\SampleNameHelper;
use App\Helpers\SampleHelper;

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
        'company_file_no' => '',
        'company_name' => '',
        'tel_no' => '',
        'signature_name' => '',
        'designation' => '',
        'fiscal_start_year' => 0,
        'is_update' => 0,
        'remark' => ''
      ]));
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
      'apply_printed_forms' => \Input::get('apply_printed_forms'),
      'apply_softcopies' => \Input::get('apply_softcopies'),
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

  public function generateSampleForm() {
    $sampleForm = $this->team->sampleForm;
    $sampleForm->status = 'ready_for_processing';
    $sampleForm->processed_printed_forms = '';
    $sampleForm->processed_softcopies = '';
    $sampleForm->save();

    EventHelper::send('requestForm', ['sampleForm'=>$sampleForm]);

    $this->createSampleFormEmployees($sampleForm);
    return response()->json([
      'status'=>true
    ]);
  }

  private function createSampleFormEmployees($sampleForm) {
    // clear
    $sampleForm->employees()->delete();

    for($i=0; $i<30; $i++) {
      $index = $i + 1;
      $nameInfo = SampleNameHelper::get();
      $martialStatus = SampleHelper::getMartialStatus($nameInfo['sex']);
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
      ];

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
        'surname' => $nameInfo['surname'],
        'given_name' => $nameInfo['givenName'],
        'name_in_chinese'=> $nameInfo['nameInChinese'],
        'sex'=>$nameInfo['sex'],
        'martial_status'=>$martialStatus['married'] ? 2 : 1,
        'spouse_name'=>isset($martialStatus['spouse']) ? $martialStatus['spouse']['name'] : '',
        'spouse_hkid'=>isset($martialStatus['spouse']) ? $martialStatus['spouse']['hkid'] : '',
        'spouse_pp_num'=>isset($martialStatus['spouse']) ? $martialStatus['spouse']['ppNum'] : '',
        'res_addr'=>'Flat A, 1/F., First Bldg., 1st Street, Central.',
        'area_code_res_addr'=>['H','K','N','F'][rand(0,3)],
        'pos_addr'=>'',
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
        'remarks'=>''
      ];

      $sampleForm->employees()->save(new SampleFormEmployee($employeeInfo));
    }
  }
}
