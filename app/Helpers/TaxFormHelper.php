<?php namespace App\Helpers;

use App\Models\TeamJob;
use App\Models\IrdForm;
use App\Models\Form;
use App\Models\SampleForm;
use App\Models\FormType;

use App\Events\xxxTaxFormStatusUpdatedEvent;

use App\Events\IrdFormStatusUpdatedEvent;
use App\Events\IrdFormEmployeeStatusUpdatedEvent;
use App\Events\IrdRequestFormItemStatusUpdatedEvent;
use App\Events\IrdRequestFormtatusUpdatedEvent;

use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;
use App\Helpers\OA\OAHelper;

use App\Helpers\IrData\IrDataHelper;
use App\Helpers\IrData\Ir56eHelper;

use App\Helpers\Forms\CommencementFormPdfHelper;
use App\Helpers\IrData\IrdApplicationLetterHelper;

class TaxFormHelper
{
  public static $COMMAND_NAME = 'generateTaxForms';

  public static function checkPending()
  {
    CommandHelper::start(self::$COMMAND_NAME, function ($command) {
      return self::handle($command);
    });
  }

  public static function handle($command)
  {
    $jobs = [];

    // IRD Forms
    $forms = Form::whereIn('status', ['processing', 'ready_for_processing'])->select(['id', 'updated_at'])->get();
    self::fillIrdForms($jobs, $forms );

    // IRD Reqest Forms
    $sampleForms = SampleForm::whereIn('status', ['processing', 'ready_for_processing'])->select(['id', 'updated_at'])->get();
    self::fillIrdSampleForm( $jobs, $sampleForms );

    usort($jobs, function ($a, $b) {
      return ($a['updated_at'] > $b['updated_at']) ? 1 : -1;
    });

    foreach ($jobs as $job) {
      self::processJob($job);
    }
    return true;
  }

  public static function fillIrdForms(&$jobs, $forms)
  {
    foreach ($forms as $form) {
      $jobs[] = [
        'form_id' => $form->id,
        'updated_at' => $form->updated_at,
        'form_type' => 'ird_form'
      ];
    }
  }

  public static function fillIrdSampleForm(&$jobs, $sampleForms)
  {
    foreach ($sampleForms as $form) {
      $jobs[] = [
        'form_id' => $form->id,
        'updated_at' => $form->updated_at,
        'form_type' => 'ird_sample_form'
      ];
    }
  }

//  public static function fillFormsWithType(&$jobs, $forms, $formType)
//  {
//    foreach ($forms as $form) {
//      $jobs[] = [
//        'form_type' => $formType,
//        'form_id' => $form->id,
//        'updated_at' => $form->updated_at
//      ];
//    }
//  }

  public static function generateForm($formEmployee, $form, $sheetNo, $irdMaster)
  {
    $team = $form->team;
    $oaAuth = $team->getOaAuth();
    $employeeId = $formEmployee->employee_id;
    $oaEmployee = OAEmployeeHelper::get($oaAuth, $employeeId, $team->oa_team_id);
    if (array_key_exists('code', $oaEmployee)) {
      dd($oaEmployee['message']);
    }

    TeamHelper::updateEmployee($team, $oaEmployee);

    // storage/app/{$filePath}
    $filePath = self::getFormFilePath($form, $formEmployee);
    $targetFilePath = storage_path('app/'.$filePath);
    checkCreateFolder( $targetFilePath);
//    $folder = pathinfo($targetFilePath, PATHINFO_DIRNAME);
//    FolderHelper::checkCreateFolders($folder);

    // language
    $langCode = 'en-us';
    if (isset($form->lang)) {
      $langCode = $form->lang->code;
    }

    $irdEmployee = IrdFormHelper::fetchDataAndGeneratePdf(
      $team,
      $formEmployee->employee_id,
      $form->irdForm->form_code,
      $langCode,
      [
        'form'=>$form,
        'outputFilePath'=>$targetFilePath,
        'sheetNo'=>$sheetNo,
        'irdMaster'=>$irdMaster
      ]
    );

    return [
      'outputFilePath'=>$targetFilePath,
      'irdEmployee'=>$irdEmployee
    ];

//    $formClass = get_class($form);
//    switch ($formClass) {
//      case 'App\Models\FormCommencement':
//        self::generateFormCommencement($team, $formEmployee, $form, $targetFilePath);
//        break;
//      case 'App\Models\FormTermination':
//        self::generateFormTermination($team, $formEmployee, $form, $targetFilePath);
//        break;
//      case 'App\Models\FormDeparture':
//        self::generateFormDeparture($team, $formEmployee, $form, $targetFilePath);
//        break;
//      case 'App\Models\FormSalary':
//        self::generateFormSalary($team, $formEmployee, $form, $targetFilePath);
//        break;
//    }
  }

  public static function generateFormCommencement($team, $employeeId, $form=null, $filePath=null, $options=[]) {
    // if filePath is null, output PDf on screen
    if(isset($form)) {
      $irdForm = $form->irdForm;
    } else {
      if(array_key_exists('formCode', $options)) {
        $formCode = $options['formCode'];
      } else {
        $formCode = 'IR56E';
      }
      $irdForm = IrdForm::whereFormCode($formCode)->first();
    }

    $data = Ir56eHelper::get($team, $employeeId, $form);
    // $data = self::getFormCommencementData($form, $employeeId);

    // language
    $langCode = array_key_exists('langCode', $options) ?
      $options['langCode'] :
      'en-us';
    // get ird form file
    $data->title = $irdForm->form_code;
    $irdFormFile = $irdForm->getFile( $langCode );
    // FormTemplateHelper::getTemplateFilePath($irdForm);

    CommencementFormPdfHelper::generate(
      $data,
      $filePath,
      $irdFormFile);

    if(isset($filePath)) {
      $filename = pathinfo($filePath, PATHINFO_BASENAME);
      $form->employees()->whereEmployeeId($employeeId)->update(['file' => $filename]);
    }
  }

  public static function xxxgetFormCommencementData($form, $employeeId) {

    $ir56eData = Ir56eHelper::get($form, $employeeId);
    return $ir56eData;
//    return [
//      // 'title' => 'MPF 2018',
//      'company' => [
//        'business_name' => 'Yoov Internet Technology Co . Ltd . ',
//        'file_no' => '6Y1 - 12345678',
//        'sheet_no' => 1,
//        'designation' => 'Director',
//        'form_date' => '2017 - 04 - 24'
//      ],
//      'employee' => [
//        'surname' => 'TIN',
//        'given_name' => 'BIU YI',
//        'full_name_in_chinese' => '田表易',
//        'hkid' => 'E123456(7)',
//        'passport_no' => 'xxxx',
//        'place_of_issue' => 'xxxxx',
//        'gender' => 'M',
//        'marital_status' => 2, /* 1=Single/Widowed/Divorced/Living Apart, 2=Married */
//        'spouse_name' => 'TSANG HING SUNG',
//        'spouse_hkid' => 'E246801(2)',
//        'spouse_passport_no' => '',
//        'spouse_place_of_issue' => '',
//        'residential_address' => 'Flat 306, Justice Bldg ., 1 Justice Road, HK',
//        'postal_address' => 'Flat 307, Justice Bldg ., 1 Justice Road, HK',
//        'capacity_employed' => 'Sales Manager(Asia Pacific)',
//        'part_time_principal_employer' => 'Hong Kong Kowloon New Territories',
//        'employment_period_start' => '2016 - 04 - 01',
//        'employment_period_end' => '2017 - 03 - 31'
//      ],
//      'income_particulars' => [
//        'salary' => ['start_date' => '2016 - 04 - 01', 'end_date' => '2017 - 03 - 31', 'amount' => 611200],
//        'leave_pay' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'director_fee' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'commission' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'bonus' => ['start_date' => '2016 - 04 - 01', 'end_date' => '2017 - 03 - 31', 'amount' => 100000],
//        'back_pay' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'payment_from_retirement_scheme' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'salaries_tax_paid_by_employer' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'education_benefits' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'gain_realized_under_share_option_scheme' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'any_other_rewards' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
//        'pensions' => ['start_date' => '', 'end_date' => '', 'amount' => 0]
//      ],
//      'residential_place_provided' => [
//        [
//          'address' => 'Rm 406, Peace Bldg ., 8 Peace St ., HK',
//          'nature' => 'Flat',
//          'start_date' => '2016 - 04 - 01',
//          'end_date' => '2016 - 08 - 31',
//          'rent_paid_to_landlord_by_employer' => 100000,
//          'rent_paid_to_landlord_by_employee' => 0,
//          'rent_refunded_to_employee_by_employer' => 0,
//          'rent_refunded_to_employer_by_employee' => 10000
//        ],
//        [
//          'address' => 'Rm 306, Justice Bldg ., 1 Justice Rd ., HK',
//          'nature' => 'Flat',
//          'start_date' => '2016 - 09 - 01',
//          'end_date' => '2017 - 03 - 31',
//          'rent_paid_to_landlord_by_employer' => 0,
//          'rent_paid_to_landlord_by_employee' => 154000,
//          'rent_refunded_to_employee_by_employer' => 140000,
//          'rent_refunded_to_employer_by_employee' => 0
//        ]
//      ],
//      'payment_by_non_hong_kong_company' => [
//        'wholly_or_partly' => true,
//        'non_hong_kong_company_name' => 'Good Harvest(International) Co Ltd . ',
//        'address' => 'No . 8, 400th Street, New York, USA',
//        'amount_hkd' => '312000',
//        'amount_other_currency' => 'US$40,000'
//      ],
//      'remark' => 'Remark'
//    ];
  }

//  public static function generateFormCommencementFile($data, $filePath) {
//
//  }

  public static function generateFormTermination($form, $employeeId, $filePath) {

  }

  public static function generateFormDeparture($form, $employeeId, $filePath) {

  }

  public static function generateFormSalary($form, $employeeId, $filePath) {

  }

  public static function formEmployeeFileStatus($formEmployee)
  {
    $result = $formEmployee['status'];
    if ($formEmployee['status'] === 'processing') {
      if (self::employeeFileExists($formEmployee)) {

      }
    }
  }

  public static function formFileExists($formEmployee)
  {
    $result = false;
    if (!empty($formEmployee['file'])) {

    }
    return $result;
  }

  public static function getFormFilePath($form, $formEmployee)
  {
    $team = $form->team;
    $pathSegs = [
      'teams',
      $team->oa_team_id,
      $form->id,
      'sheet_'.$formEmployee->sheet_no.'.pdf'
    ];
    return implode('/', $pathSegs);
  }

  public static function getFormPath($form)
  {
    $irdFormType = $form->irdFormType;
    return (string) $irdFormType->id;
//    $formType = self::getFormType($form);
//    return empty($formType) ? 'unknown' : FormType::whereName($formType)->value('path');
  }

  public static function getFormType($form)
  {
    $irdFormType = $form->irdFormType;
    return (string) $irdFormType->id;
//    $formClass = get_class($form);
//    $formType = '';
//    switch ($formClass) {
//      case 'App\Models\FormCommencement':
//        $formType = 'commencements';
//        break;
//      case 'App\Models\FormTermination':
//        $formType = 'terminations';
//        break;
//      case 'App\Models\FormDeparture':
//        $formType = 'departures';
//        break;
//      case 'App\Models\FormSalary':
//        $formType = 'salaries';
//        break;
//    }
//    return $formType;
  }

  public static function processJob($job)
  {
    logConsole('Processing job form_id = ' . $job['form_id'] . ' (type='.$job['form_type'].') ...');
    nf();
    switch ($job['form_type']) {
      case 'ird_form':
        self::processJob_irdForm($job);
        break;
      case 'ird_sample_form':
        self::processJob_irdSampleForm($job);
        break;
    }
  }

  public static function processJob_irdSampleForm($job) {
    $sampleForm = SampleForm::find($job['form_id']);
    if(is_null($sampleForm->team)) {
      $sampleForm->message = __('messages.team_not_defined');
      $sampleForm->status = 'terminated';
      $sampleForm->save();
      EventHelper::send( 'requestForm', ['sampleForm'=>$sampleForm]);
    } else {
      $team = $sampleForm->team;

      // Status => 'processing'
      if ($sampleForm->status != 'processing') {
        $sampleForm->update(['status' =>'processing']);
        EventHelper::send('requestForm', ['sampleForm'=>$sampleForm]);
      }

      // Build application letter
      // path: storage/app/teams/{oa_team_id}/application_letters/{sample_form_id}/
      //
      // Letter: letter.pdf
      // ir56b.xml
      // ir56b.xsd
      // ir56b_sample_sheets {
      //    ir56b_sample_sheet_1
      //    ir56b_sample_sheet_2
      //    ir56b_sample_sheet_3
      // }
      // ir56b_control_list
      //
      // ir56m.xml
      // ir56m.xsd
      // ir56m_sample_sheets {
      //    ir56m_sample_sheet_1
      //    ir56m_sample_sheet_2
      //    ir56m_sample_sheet_3
      // }
      // ir56m_control_list
      //
      // ir56e_sample_sheets
      // ir56f_sample_sheets
      // ir56g_sample_sheets
      // ir56m_sample_sheets (if ir56m soft copies not ncessary)
      //

      IrdApplicationLetterHelper::build($team, $sampleForm);
      EventHelper::send( 'requestForm', ['sampleForm'=>$sampleForm]);

      $applySoftcopiesStr = trim($sampleForm->apply_softcopies);
      $applySoftcopies = explode(',', $applySoftcopiesStr);
      foreach( $applySoftcopies as $irdFormCode ) {
        $irdMaster = IrdFormHelper::getIrdMaster($team,$sampleForm,[
          'fieldMappings'=>[
            'form_date'=>'application_date'
          ]
        ]);
        $employees = $sampleForm->employees;
        $sheetNo = 1;
        $sheetCount = 3;
        foreach($employees as $formEmployee) {
          if($sampleForm->status != 'processing') {
            break;
          }

          $generationResult = self::generateSampleForm($formEmployee, $sampleForm, $sheetNo, $irdMaster, $irdFormCode);
          $irdEmployee = $generationResult['irdEmployee'];
          $irdMaster['Employees'][] = $irdEmployee;
          $irdMaster['TotIncomeBatch'] += (double) $irdEmployee['TotalIncome'];
        }
        if($sampleForm->status != 'processing') {
          break;
        }

      }

      $applyPrintedFormsStr = trim($sampleForm->apply_printed_forms);
      $applyPrintedForms = explode(',', $sampleForm->apply_printed_forms);
      foreach( $applyPrintedForms as $applyPrintedForm ) {
        foreach($employees as $formEmployee) {
          if($sampleForm->status != 'processing') {
            break;
          }

        }
        if($sampleForm->status != 'processing') {
          break;
        }
      }

      if($sampleForm->status != 'processing') {
        return;
      }

      if($sampleForm->status == 'processing') {
        $sampleForm->update(['status' => 'ready']);
      }
      EventHelper::send('requestForm', ['sampleForm' => $sampleForm]);
      print_r( $irdMaster);
    }
  }

  public static function generateSampleForm($formEmployee, $sampleForm, $sheetNo, $irdMaster, $irdFormCode) {
    $team = $sampleForm->team;
    $employeeId = $formEmployee->employee_id;

    if($sheetNo != 0) {
      $outputFilePath = storage_path(
        'app/team/' .
        $team->oa_team_id .
        '/application_letters/' .
        $sampleForm->id . '/' .
        $irdFormCode . '_sample_sheet_' . $sheetNo . '.pdf');
      checkCreateFolder($outputFilePath);
    }


  }

  public static function processJob_irdForm($job) {
    $form = Form::find($job['form_id']);
    if(is_null($form->team)) {
      logConsole( __('messages.team_not_defined'), 1 );
      $form->message = __('messages.team_not_defined');
      $form->status = 'terminated';
      $form->save();
      EventHelper::send( 'form', ['form'=>$form]);
    } else {
      $team = $form->team;
//      OAHelper::updateTeamToken($team);

      logConsole('team #'.$team->id.'  ('.$team->oa_team_id.')',1);
      if ($form->status != 'processing') {
        $form->update(['status' => 'processing']);
        EventHelper::send('form', ['form' => $form]);
      }
      $employees = $form->employees()->get();

      $irdMaster = IrdFormHelper::getIrdMaster($team,$form);

      $sheetNo = 1;
      foreach ($employees as $formEmployee) {
        $form = Form::find($form->id);
        if($form->status != 'processing') {
          break;
        }
        $employeeId = $formEmployee->employee_id;

        echo 'processing employee #'.$employeeId.' ...'; nl();
        $formEmployee = $form->employees()->whereEmployeeId($employeeId)->first();

        // Status => "Processing"
        if($formEmployee->status != 'processing') {
          $form->employees()->whereEmployeeId($employeeId)->update(['status' => 'processing']);
          $formEmployee = $form->employees()->whereEmployeeId($employeeId)->first();
          EventHelper::send('formEmployee', ['form' => $form, 'formEmployee' => $formEmployee]);
        }

        // Process
        $generationResult = self::generateForm($formEmployee, $form, $sheetNo, $irdMaster);
        // generationResult = [
        //    'irdEmployee'=>...
        //    'outputFilePath'=>'....'
        // ]
        //

        // Status => "Ready"
        $form->employees()->whereEmployeeId($employeeId)->update([
          'status' => 'ready',
          'file' => pathinfo( $generationResult['outputFilePath'], PATHINFO_BASENAME)
        ]);
        $formEmployee = $form->employees()->whereEmployeeId($employeeId)->first();
        EventHelper::send('formEmployee', ['form' => $form, 'formEmployee' => $formEmployee]);
        $sheetNo++;

        // Calculation Summary
        $irdEmployee = $generationResult['irdEmployee'];
        $irdMaster['Employees'][] = $irdEmployee;
        $irdMaster['TotIncomeBatch'] += (double) $irdEmployee['TotalIncome'];
      }

      // create control list
      $irdForm = $form->irdForm;

      if($irdForm->requires_control_list) {
        self::createControlList($form, $irdMaster);
      }
      if($form->status == 'processing') {
        $form->update(['status' => 'ready']);
      }
      EventHelper::send('form', ['form' => $form]);
    }
  }

  public static function createControlList( $form, $irdMaster ) {
    $team = $form->team;
    $irdForm = $form->irdForm;
    $controlListIrdForm = IrdForm::whereFormCode($irdForm->ird_code.'_CL')->first();
    $controlListIrdFile = $controlListIrdForm->getFile($form->lang->code);
    $fields = $controlListIrdFile->fields;

    // FileNo
    // HeaderPeriod
    // ErName
    // TotIncomebatch
    // NoRecordBatch
    //
    $outputFilePath = storage_path('app/teams/'.$team->oa_team_id.'/'.$form->id.'/control_list.pdf' );
    $options = [
      'fields'=>$fields,
      'data'=>[
        'HeaderCompanyName'=>$irdMaster['ErName'],
        'HeaderFiscalYears'=>ucfirst(strtolower($irdMaster['HeaderPeriod'])),
        'HeaderFileNo'=>'File No.               '.$irdMaster['FileNo'],
        'HeaderPageSubject'=>'List of Employees with IR56Bs Prepared via Self-developed Software'
      ],
      'printHeader'=>true,
      'printFooter'=>true,
      'headerMargin'=>10,
      'footerMargin'=>5,
      'autoPageBreak'=>true
    ];
    $pdf = new FormPdf($options);

    if(file_exists($outputFilePath)) {
      unlink($outputFilePath);
    }
    $pdf->Output($outputFilePath, 'F');
  }

  public static function processCommencementJob($job)
  {
    logConsole('Processing commencement job form_id = '.$job['form_id'].' ...'); nl();
    $form = FormCommencement::find($job['form_id']);
    if(is_null($form->team)) {
      logConsole( __('messages.team_not_defined'), 1 );
      $form->message = __('messages.team_not_defined');
      $form->status = 'terminated';
      $form->save();
      EventHelper::send( 'commencementForm', ['form'=>$form]);
    } else {
      logConsole('team #'.$form->team->id.'  ('.$form->team->oa_team_id.')',1);
      if ($form->status != 'processing') {
        $form->update(['status' => 'processing']);
        EventHelper::send('commencementForm', ['form' => $form]);
      }
      $employees = $form->employees()->get();
      foreach ($employees as $formEmployee) {
        $form->employees()->whereEmployeeId($formEmployee->employee_id)->update(['status' => 'processing']);
        EventHelper::send('commencementFormEmployee', ['form' => $form, 'formEmployee' => $formEmployee]);
        self::generateForm($formEmployee, $form);

        $result = $form->employees()->whereEmployeeId($formEmployee->employee_id)->update(['status' => 'ready']);
//        echo 'TaxFormHelper :: formEmployee :  '; nl();
        $formEmployee = $form->employees()->whereEmployeeId($formEmployee->employee_id)->first();
        // dd($formEmployee->toArray());
        // dd($formEmployee);
        EventHelper::send('commencementFormEmployee', ['form' => $form, 'formEmployee' => $formEmployee]);
      }
      $form->update(['status' => 'ready']);
      EventHelper::send('commencementForm', ['form' => $form]);
    }
  }

  public static function processTerminationJob($job)
  {
    echo 'processTerminationJob: ';
    nl();
    $form = FormTermination::find($job['form_id']);
    if ($form->status != 'processing') {
      $form->update(['status' => 'processing']);
      event(new TerminationFormStatusUpdatedEvent([
        'team' => $form->team,
        'formId' => $form->id,
        'total' => $form->employees->count,
        'progress' => 0,
        'status' => 'processing'
      ]));
    }
  }

  public static function processDepartureJob($job)
  {
    echo 'processDepartureJob: ';
    nl();
    $form = FormDeparture::find($job['form_id']);
    if ($form->status != 'processing') {
      $form->update(['status' => 'processing']);
      event(new DepartureFormStatusUpdatedEvent([
        'team' => $form->team,
        'formId' => $form->id,
        'total' => $form->employees->count,
        'progress' => 0,
        'status' => 'processing'
      ]));
    }
  }

  public static function processSalaryJob($job)
  {
    echo 'processSalaryJob: ';
    nl();
    $form = FormSalary::find($job['form_id']);
    if ($form->status != 'processing') {
      $form->update(['status' => 'processing']);
      event(new SalaryFormStatusUpdatedEvent([
        'team' => $form->team,
        'formId' => $form->id,
        'total' => $form->employees->count,
        'progress' => 0,
        'status' => 'processing'
      ]));
    }
  }

  public static function processJobsWithType($jobs)
  {
    foreach ($jobs as $job) {
      switch ($job['form_type']) {
        case 'commencement':
          self::processCommencementJob($job);
          break;
        case 'termination':
          self::processTerminationJob($job);
          break;
        case 'departure':
          self::processDepartureJob($job);
          break;
        case 'salary':
          self::processSalaryJob($job);
          break;
      }
    }
  }

  public static function handlex($command)
  {
    $teamJobs = TeamJob::whereStatus('pending')->get();
    foreach ($teamJobs as $job) {
      $team = $job->team;
      $fiscalYear = $job->fiscal_year;

      $jobItems = $job->items()->whereEnabled(1)->get();
      $totalCount = $jobItems->count();

      $oaAuth = [
        'oa_access_token' => $job->oa_access_token,
        'oa_token_type' => $job->oa_token_type
      ];
      foreach ($jobItems as $i => $item) {
        // echo 'i = '.$i; nl();
        $employeeId = $item->employee_id;

        $taxForm = $team->getOrCreateTaxForm($employeeId, $fiscalYear);
        if ($taxForm->status == 'pending') {
          $taxForm->status = 'processing';
          $taxForm->save();
        }
        if ($taxForm->status == 'processing') {
          event(new xxxTaxFormStatusUpdatedEvent([
            'team' => $team,
            'index' => $i,
            'taxForm' => $taxForm,
            'total' => $totalCount
          ]));
          //*******************
          // Generation
          //*******************
          self::generateTaxForm($taxForm, $oaAuth);

          $taxForm->status = 'ready';
          $taxForm->save();
        }

        if ($taxForm->status == 'ready') {
          event(new xxxTaxFormStatusUpdatedEvent([
            'team' => $team,
            'index' => $i,
            'taxForm' => $taxForm,
            'item' => $item,
            'total' => $totalCount
          ]));
        }
      }
      $job->status = 'completed';
      $job->save();
    }
  }

  public static function getTaxFormData($taxForm)
  {
    return [
      'title' => 'MPF 2018',
      'company' => [
        'business_name' => 'Yoov Internet Technology Co . Ltd . ',
        'file_no' => '6Y1 - 12345678',
        'sheet_no' => 1,
        'designation' => 'Director',
        'form_date' => '2017 - 04 - 24'
      ],
      'employee' => [
        'surname' => 'TIN',
        'given_name' => 'BIU YI',
        'full_name_in_chinese' => '田表易',
        'hkid' => 'E123456(7)',
        'passport_no' => 'xxxx',
        'place_of_issue' => 'xxxxx',
        'gender' => 'M',
        'marital_status' => 2, /* 1=Single/Widowed/Divorced/Living Apart, 2=Married */
        'spouse_name' => 'TSANG HING SUNG',
        'spouse_hkid' => 'E246801(2)',
        'spouse_passport_no' => '',
        'spouse_place_of_issue' => '',
        'residential_address' => 'Flat 306, Justice Bldg ., 1 Justice Road, HK',
        'postal_address' => 'Flat 307, Justice Bldg ., 1 Justice Road, HK',
        'capacity_employed' => 'Sales Manager(Asia Pacific)',
        'part_time_principal_employer' => 'Hong Kong Kowloon New Territories',
        'employment_period_start' => '2016 - 04 - 01',
        'employment_period_end' => '2017 - 03 - 31'
      ],
      'income_particulars' => [
        'salary' => ['start_date' => '2016 - 04 - 01', 'end_date' => '2017 - 03 - 31', 'amount' => 611200],
        'leave_pay' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'director_fee' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'commission' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'bonus' => ['start_date' => '2016 - 04 - 01', 'end_date' => '2017 - 03 - 31', 'amount' => 100000],
        'back_pay' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'payment_from_retirement_scheme' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'salaries_tax_paid_by_employer' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'education_benefits' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'gain_realized_under_share_option_scheme' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'any_other_rewards' => ['start_date' => '', 'end_date' => '', 'amount' => 0],
        'pensions' => ['start_date' => '', 'end_date' => '', 'amount' => 0]
      ],
      'residential_place_provided' => [
        [
          'address' => 'Rm 406, Peace Bldg ., 8 Peace St ., HK',
          'nature' => 'Flat',
          'start_date' => '2016 - 04 - 01',
          'end_date' => '2016 - 08 - 31',
          'rent_paid_to_landlord_by_employer' => 100000,
          'rent_paid_to_landlord_by_employee' => 0,
          'rent_refunded_to_employee_by_employer' => 0,
          'rent_refunded_to_employer_by_employee' => 10000
        ],
        [
          'address' => 'Rm 306, Justice Bldg ., 1 Justice Rd ., HK',
          'nature' => 'Flat',
          'start_date' => '2016 - 09 - 01',
          'end_date' => '2017 - 03 - 31',
          'rent_paid_to_landlord_by_employer' => 0,
          'rent_paid_to_landlord_by_employee' => 154000,
          'rent_refunded_to_employee_by_employer' => 140000,
          'rent_refunded_to_employer_by_employee' => 0
        ]
      ],
      'payment_by_non_hong_kong_company' => [
        'wholly_or_partly' => true,
        'non_hong_kong_company_name' => 'Good Harvest(International) Co Ltd . ',
        'address' => 'No . 8, 400th Street, New York, USA',
        'amount_hkd' => '312000',
        'amount_other_currency' => 'US$40,000'
      ],
      'remark' => 'Remark'
    ];
  }

  public static function generateTaxForm($taxForm, $oaAuth)
  {
    $team = $taxForm->team;
    $oaUser = OAEmployeeHelper::get($taxForm->employee_id, $oaAuth, $team->oa_team_id);

    dd($oaUser);

    $user = UserHelper::getFromOAUser($oaUser);
    $filePath = self::getFilePath($taxForm->fiscal_year, $user);

    $data = self::getTaxFormData($taxForm);
    TaxFormPdfHelper::generate($data, $filePath);
  }

  public static function getFormUrl($formEmployee, $formType)
  {
    $form = $formEmployee->form;
    return storage_path('app / teams / ' . $form->team_id . ' / ' . $formType . ' / ' . $form->id . ' / ' . $formEmployee->file);
  }

  public static function getIrdFormId($formCode)
  {
    $irdForm = IrdForm::whereFormCode($formCode)->first();
    return isset($irdForm) ? $irdForm->id : 0;
  }

  public static function getNextFormId($query, $prefix)
  {
    $formNo = $prefix . date('Ymd');
    $count = 1;
    $suffix = $count > 1 ? '-' . $count : '';
    $newFormNo = $formNo . $suffix;
    while ($query->whereFormNo($newFormNo)->count() > 0) {
      $count++;
      $newFormNo = $formNo . '_' . $count;
    }
    return $newFormNo;
  }
}