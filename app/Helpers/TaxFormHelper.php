<?php namespace App\Helpers;

use App\Models\TeamJob;

use App\Events\TaxFormStatusUpdatedEvent;

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

  public static function getTaxFormData($taxForm) {
    return [
      'title' => 'MPF 2018',
      'company' => [
        'business_name' => 'Yoov Internet Technology Co. Ltd.',
        'file_no' => '6Y1-12345678',
        'sheet_no' => 1,
        'designation' => 'Director',
        'form_date' => '2017-04-24'
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
        'residential_address' => 'Flat 306, Justice Bldg., 1 Justice Road, HK',
        'postal_address' => 'Flat 307, Justice Bldg., 1 Justice Road, HK',
        'capacity_employed' => 'Sales Manager (Asia Pacific)',
        'part_time_principal_employer' => 'Hong Kong Kowloon New Territories',
        'employment_period_start' => '2016-04-01',
        'employment_period_end' => '2017-03-31'
      ],
      'income_particulars' => [
        'salary' => ['start_date'=>'2016-04-01','end_date'=>'2017-03-31','amount'=>611200],
        'leave_pay' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'director_fee' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'commission' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'bonus' => ['start_date'=>'2016-04-01', 'end_date'=>'2017-03-31', 'amount'=>100000],
        'back_pay' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'payment_from_retirement_scheme' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'salaries_tax_paid_by_employer' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'education_benefits' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'gain_realized_under_share_option_scheme' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'any_other_rewards' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'pensions' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0]
      ],
      'residential_place_provided' => [
        [
          'address'=>'Rm 406, Peace Bldg., 8 Peace St., HK',
          'nature'=>'Flat',
          'start_date'=>'2016-04-01',
          'end_date'=>'2016-08-31',
          'rent_paid_to_landlord_by_employer'=>100000,
          'rent_paid_to_landlord_by_employee'=>0,
          'rent_refunded_to_employee_by_employer'=>0,
          'rent_refunded_to_employer_by_employee'=>10000
        ],
        [
          'address'=>'Rm 306, Justice Bldg., 1 Justice Rd., HK',
          'nature'=>'Flat',
          'start_date'=>'2016-09-01',
          'end_date'=>'2017-03-31',
          'rent_paid_to_landlord_by_employer'=>0,
          'rent_paid_to_landlord_by_employee'=>154000,
          'rent_refunded_to_employee_by_employer'=>140000,
          'rent_refunded_to_employer_by_employee'=>0
        ]
      ],
      'payment_by_non_hong_kong_company'=>[
        'wholly_or_partly'=>true,
        'non_hong_kong_company_name'=>'Good Harvest (International) Co Ltd.',
        'address' => 'No. 8, 400th Street, New York, USA',
        'amount_hkd' => '312000',
        'amount_other_currency' => 'US$40,000'
      ],
      'remark'=>'Remark'
    ];
  }

  public static function generateTaxForm($taxForm, $oaAuth) {
    $team = $taxForm->team;
    $oaUser = OAEmployeeHelper::get($taxForm->employee_id, $oaAuth, $team->oa_team_id);

    dd($oaUser);

    $user = UserHelper::getFromOAUser($oaUser);
    $filePath = self::getFilePath($taxForm->fiscal_year, $user);

    $data = self::getTaxFormData($taxForm);
    TaxFormPdfHelper::generate($data, $filePath);
  }
}