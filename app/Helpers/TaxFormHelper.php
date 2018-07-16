<?php namespace App\Helpers;

use App\Models\TeamJob;
use App\Models\IrdForm;
use App\Models\FormCommencement;
use App\Models\FormTermination;
use App\Models\FormSalary;
use App\Models\formDeparture;
use App\Models\FormType;

use App\Events\TaxFormStatusUpdatedEvent;

use App\Events\CommencementFormStatusUpdatedEvent;
use App\Events\TerminationFormStatusUpdatedEvent;
use App\Events\DepartureFormStatusUpdatedEvent;
use App\Events\SalaryFormStatusUpdatedEvent;

use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;
use App\Helpers\OA\OAHelper;

use App\Events\CommencementFormEmployeeStatusUpdatedEvent;
use App\Events\TerminationFormEmployeeStatusUpdatedEvent;
use App\Events\DepartureFormEmployeeStatusUpdatedEvent;
use App\Events\SalaryFormEmployeeStatusUpdatedEvent;

use App\Helpers\Forms\CommencementFormPdfHelper;

class TaxFormHelper
{
  public static $COMMAND_NAME = 'generateTaxForms';

  public static function checkPending()
  {
    CommandHelper::start(self::$COMMAND_NAME, function ($command) {
      self::handle($command);
    });
  }

  public static function handle($command)
  {
    $jobs = [];

    // Commencement
    $forms = FormCommencement::whereIn('status', ['processing', 'ready_for_processing'])->select(['id', 'updated_at'])->get();
    self::fillForms($jobs, $forms, 'commencement');

    // Termination
    $forms = FormTermination::whereIn('status', ['processing', 'ready_for_processing'])->select(['id', 'updated_at'])->get();
    self::fillForms($jobs, $forms, 'termination');

    // Departure
    $forms = FormDeparture::whereIn('status', ['processing', 'ready_for_processing'])->select(['id', 'updated_at'])->get();
    self::fillForms($jobs, $forms, 'departure');

    // Salary
    $forms = FormSalary::whereIn('status', ['processing', 'ready_for_processing'])->select(['id', 'updated_at'])->get();
    self::fillForms($jobs, $forms, 'salary');

    usort($jobs, function ($a, $b) {
      return ($a > $b) ? 1 : -1;
    });

    self::processJobs($jobs);

    return false;
  }

  public static function fillForms(&$jobs, $forms, $formType)
  {
    foreach ($forms as $form) {
      $jobs[] = [
        'form_type' => $formType,
        'form_id' => $form->id,
        'updated_at' => $form->updated_at
      ];
    }
  }

  public static function generateForm($formEmployee, $form)
  {
    $team = $form->team;
    OAHelper::updateTeamToken($team);

    $oaAuth = $team->getOaAuth();

    $oaEmployee = OAEmployeeHelper::get($formEmployee->employee_id, $oaAuth, $team->oa_team_id);
    if (array_key_exists('code', $oaEmployee)) {
      dd($oaEmployee['message']);
    }

    // storage/app/{$filePath}
    $filePath = self::getFormFilePath($form, $formEmployee);
    $targetFilePath = storage_path('app/'.$filePath);
    $folder = pathinfo($targetFilePath, PATHINFO_DIRNAME);
    FolderHelper::checkCreateFolders($folder);

    $formClass = get_class($form);
    switch ($formClass) {
      case 'App\Models\FormCommencement':
        self::generateFormCommencement($form, $formEmployee, $targetFilePath);
        break;
      case 'App\Models\FormTermination':
        self::generateFormTermination($form, $formEmployee, $targetFilePath);
        break;
      case 'App\Models\FormDeparture':
        self::generateFormDeparture($form, $formEmployee, $targetFilePath);
        break;
      case 'App\Models\FormSalary':
        self::generateFormSalary($form, $formEmployee, $targetFilePath);
        break;
    }
  }

  public static function generateFormCommencement($form, $formEmployee, $filePath) {
    echo 'generateFormCommencement :: form id = '.$form->id; nl();
    echo 'generateFormCommencement :: employee id = '.$formEmployee->employee_id; nl();

    $irdForm = $form->irdForm;

    $data = self::getFormCommencementData($form, $formEmployee);
    $data['title'] = $irdForm->form_code;
    $templateFilePath = FormTemplateHelper::getTemplateFilePath($form, $irdForm->form_code);

    CommencementFormPdfHelper::generate(
      $data,
      $templateFilePath,
      $filePath,
      $irdForm->fields);

    $filename = pathinfo($filePath, PATHINFO_BASENAME);
    $form->employees()->whereEmployeeId($formEmployee->employee_id)->update(['file'=>$filename]);
  }

  public static function getFormCommencementData($form, $formEmployee) {

    $ir56b = IrdDataHelper::getIr56b($form, $formEmployee);
    return [
      // 'title' => 'MPF 2018',
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

  public static function generateFormCommencementFile($data, $filePath) {

  }

  public static function generateFormTermination($form, $formEmployee, $filePath) {

  }

  public static function generateFormDeparture($form, $formEmployee, $filePath) {

  }

  public static function generateFormSalary($form, $formEmployee, $filePath) {

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
      self::getFormPath($form),
      $form->id,
      $formEmployee->employee_id . '.pdf'
    ];
    return implode('/', $pathSegs);
  }

  public static function getFormPath($form)
  {
    $formType = self::getFormType($form);
    return empty($formType) ? 'unknown' : FormType::whereName($formType)->value('path');
  }

  public static function getFormType($form)
  {
    $formClass = get_class($form);
    $formType = '';
    switch ($formClass) {
      case 'App\Models\FormCommencement':
        $formType = 'commencements';
        break;
      case 'App\Models\FormTermination':
        $formType = 'terminations';
        break;
      case 'App\Models\FormDeparture':
        $formType = 'departures';
        break;
      case 'App\Models\FormSalary':
        $formType = 'salaries';
        break;
    }
    return $formType;
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

  public static function processJobs($jobs)
  {
    echo 'processJobs: ';
    nl();
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
          event(new TaxFormStatusUpdatedEvent([
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
          event(new TaxFormStatusUpdatedEvent([
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
    $suffix = $count > 1 ? '_' . $count : '';
    $newFormNo = $formNo . $suffix;
    while (is_null($query->whereFormNo($newFormNo))) {
      $count++;
      $newFormNo = $formNo . '_' . $count;
    }
    return $newFormNo;
  }
}