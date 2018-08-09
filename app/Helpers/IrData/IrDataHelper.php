<?php namespace App\Helpers\IrData;

use App\Helpers\IrData\xxxxxIr56B;
use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;
use App\Helpers\OA\OASalaryHelper;
use App\Helpers\OA\OAPayslipHelper;
use App\Helpers\FormHelper;

use App\Models\IrdForm;

class IrDataHelper
{
  protected static $team = null;
  protected static $employeeId = null;
  protected static $form = null;
  protected static $oaAuth = null;
  protected static $irdCode = null;
  protected static $forceDefaults = false;
  protected static $testing = false;
  protected static $hasDefaults = false;

//  public static function getIr56b($form, $employeeId)
//  {
//    $team = $form->team;
//    $oaAuth = OAHelper::refreshTokenByTeam($team);
//
//    $oaEmployee = oaEmployeeHelper::get($employeeId, $oaAuth, $team->oa_team_id);
//    $oaTeam = oaTeamHelper::get($team->oa_team_id, $oaAuth);
//
//    $irData = new ir56b;
//    $employerFileNo = $team->getSetting('employer_file_no', '');
//    $employerFileNoSegs = explode('-', $employerFileNo);
//
//    $irData->section = count($employerFileNoSegs)>0 ? $employerFileNoSegs[0] : '';
//    $irData->ern = count($employerFileNoSegs)>1 ? $employerFileNoSegs[1] : '';
//    $irData->yrErReturn = 0;
//    $irData->subDate = date('Ymd');
//    $irData->erName = $oaTeam['name'];
//    $irData->designation = '';
//    $irData->noRecordBatch = 0;
//    $irData->totIncomeBatch = 0;
//    $irData->employees = [];
//
//    dd((array)$irData);
//
//    dd($oaEmployee);
//
//  }

  public static function getOAEmployee()
  {
    return OAEmployeeHelper::get(static::$oaAuth, static::$employeeId, static::$team->oa_team_id);
  }

  public static function getOAAdminEmployee()
  {
    return OAEmployeeHelper::getAdminInfo(static::$oaAuth, static::$employeeId, static::$team->oa_team_id);
  }

  public static function getIrdInfo($irdCode, $langCode, $extra = [])
  {
    $irdForm = IrdForm::whereIrdCode($irdCode)->whereEnabled(1)->first();
    $irdFormFile = $irdForm->getFile($langCode);

    return array_merge([
      'langCode' => $langCode,
      'irdForm' => $irdForm,
      'fields' => $irdFormFile->fields->toArray(),
      'is_sample' => false,
      'is_testing' => false
    ], $extra);
  }

  public static function getOAPayrollSummary(
    $oaAuth,
    $team,
    $oaEmployee,
    $fiscalYearInfo)
  {
    $period = [
      'startDate' => $fiscalYearInfo['startDate'],
      'endDate' => $fiscalYearInfo['endDate']
    ];

    $summary = [
      'totalIncome' => 0,
      'salary' => 0, // 1
      'leave_pay' => 0, // 2
      'director_fee' => 0, // 3
      'comm_fee' => 0, // 4
      'bonus' => 0, // 5
      'bp_etc' => 0, // 6
      'pay_retire' => 0, // 7
      'sal_tax_paid' => 0, // 8
      'edu_ben' => 0, // 9
      'gain_share_option' => 0, // 10
      'other_raps' => [], // 11
      'pension' => 0, // 12
      'special_payments'=>0,
      'special_payments_nature'=>''
    ];
    // dd($period);

    // Fetch pay type mapping
    //
    // $payTypeMappings = [
    //  'salary' => [1,2,4],
    //  'leave_pay' => [2,4],
    //  ...
    // ]
    //
    // Init all summary item to 0;
    //

    $incomeMappings = [];
    $payTypeToTokenMappings = [];
    $summary['totalIncome'] = 0;
    // $otherRapPayTypeIds = [];
    $otherRaps = [];
    $specialPayments = [];

    if(static::$irdCode == 'IR56B') {
      $incomeMappings = $team->teamIr56bIncomes()->with('ir56bIncome')->get();
      foreach ($incomeMappings as $item) {
        $token = $item->ir56bIncome->token;
        $payTypeIds = trim($item->pay_type_ids);
        if ($payTypeIds != '') {
          $arPayTypeIds = explode(',', $payTypeIds);
          foreach ($arPayTypeIds as $payTypeId) {
            $payTypeToTokenMappings['payType_' . $payTypeId] = $token;
          }
        }
        if ($token == 'other_raps') {
          $summary[$token] = [];
        } else {
          $summary[$token] = 0;
        }
      }
    } else {
      $incomeMappings = $team->teamIr56fIncomes()->with('ir56fIncome')->get();
      foreach ($incomeMappings as $item) {
        $token = $item->ir56fIncome->token;
        $payTypeIds = trim($item->pay_type_ids);
        if ($payTypeIds != '') {
          $arPayTypeIds = explode(',', $payTypeIds);
          foreach ($arPayTypeIds as $payTypeId) {
            $payTypeToTokenMappings['payType_' . $payTypeId] = $token;
          }
        }
        if ($token == 'other_raps') {
          $summary[$token] = [];
        } else {
          $summary[$token] = 0;
        }
      }
    }

    // $otherRaps = [
    //    [
    //      'nature'=>'xxx',
    //      'amt'=>0
    //    ],
    //    [
    //      'nature'=>'xxx',
    //      'amt'=>0
    //    ]
    // ]

    //****************************************
    // Fetch payslips amount of each pay type
    //****************************************

    // Filter payslips for related fiscal year
    $payslips = OAPayslipHelper::get($oaAuth, $oaEmployee['id'], $team->oa_team_id);

    $effectivePayslips = [];
    foreach ($payslips as $i => $payslip) {
      if (inBetween($payslip['startedDate'], $period) ||
        inBetween($payslip['endedDate'], $period)) {
        $effectivePayslips[] = $payslip;
      }
    }

    foreach ($effectivePayslips as $payslip) {
      foreach ($payslip['details'] as $detail) {
        if ($detail['isBasicSalary']) {
          $summary['salary'] += $detail['amount'];
        } else {
          if ($detail['payTypeId']) {
            $index = 'payType_' . $detail['payTypeId'];
            if (array_key_exists($index, $payTypeToTokenMappings)) {
              $token = $payTypeToTokenMappings['payType_' . $detail['payTypeId']];
              if ($token == 'other_raps') {
                if (array_key_exists($detail['name'], $otherRaps)) {
                  $otherRaps[$detail['name']] += $detail['amount'];
                } else {
                  $otherRaps[$detail['name']] = $detail['amount'];
                }
              } else if ($token == 'special_payments') {
                if (array_key_exists($detail['name'], $specialPayments)) {
                  $specialPayments[$detail['name']] += $detail['amount'];
                } else {
                  $specialPayments[$detail['name']] = $detail['amount'];
                }
              } else {
                $summary[$token] += $detail['amount'];
              }
            } else {
              $summary['salary'] += $detail['amount'];
            }
          } else {
            $summary['salary'] += $detail['amount'];
          }
        }
        $summary['totalIncome'] += $detail['amount'];
      }
    }

    foreach ($otherRaps as $nature => $amount) {
      $summary['other_raps'][] = [
        'nature' => $nature,
        'amt' => $amount
      ];
    }
    if(count($summary['other_raps'])>3) {
      for($i=3; $i<count($summary['other_raps']); $i++) {
        $summary['other_raps'][2]['nature'] += ','.$summary['other_raps'][$i]['nature'];
        $summary['other_raps'][2]['amt'] += $summary['other_raps'][$i]['amt'];
      }
      $summary['other_raps'] = array_slice($summary['other_raps'], 0, 3);
    }

    $specialNatures = [];
    foreach( $specialPayments as $nature => $amount) {
      $summary['special_payments'] += $amount;
      $specialNatures[] = $nature;
    }
    $summary['special_payments_nature'] = implode(', ', $specialNatures);

    return $summary;
  }

  public static function getOATeam()
  {
    return OATeamHelper::get(static::$oaAuth, static::$team->oa_team_id);
  }

  public static function getOASalary()
  {
    return OASalaryHelper::get(static::$oaAuth, static::$employeeId, static::$team->oa_team_id);
  }

  public static function getIrdMaster($team, $form = null, $options = [])
  {
    $isEnglish = true;
    if (isset($form)) {
      $isEnglish = $form->lang->code == 'en-us';
    } else if (array_key_exists('lang', $options)) {
      $isEnglish = $options['lang'] == 'en-us';
    }

    // Fiscal Year Info
    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);

    //************************************
    // Get OA Team & relevant company info
    //************************************
    $oaAuth = OAHelper::refreshTokenByTeam($team);
    $oaTeam = OATeamHelper::get($oaAuth, $team->oa_team_id);
    // Registration Number
    $registrationNumber = $team->getSetting('fileNo', '');
    $registrationNumberSegs = explode('-', $registrationNumber);
    $section = $registrationNumberSegs[0];
    $ern = count($registrationNumberSegs)>1 ? $registrationNumberSegs[1] : '';
    $headerPeriod = $isEnglish ?
      'for the year from 1 April ' . ($fiscalYearInfo['startYear']) . ' to 31 March ' . ($fiscalYearInfo['endYear']) :
      '在 ' . $fiscalYearInfo['startYear'] . ' 年 4 月 1 日至 ' . $fiscalYearInfo['endYear'] . ' 年 3 月 31 日 年內';
    $formDate = date('Y-m-d');
    $designation = 'Manager';
    if (isset($form)) {
      $formDate = $form->{getFieldMapping($options, 'form_date')};
      $designation = $form->designation;
    }

    $result = [
      // Non-ird fields
      'HeaderPeriod' => strtoupper($headerPeriod),
      'EmpPeriod' => $headerPeriod . ':',
      'IncPeriod' => 'Particulars of income accuring ' . $headerPeriod,
      'FileNo' => $registrationNumber,

      // for Chinese version only
      'HeaderPeriodFromYear' => $fiscalYearInfo['startYear'],
      'HeaderPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
      'EmpPeriodFromYear' => $fiscalYearInfo['startYear'],
      'EmpPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
      'IncPeriodFromYear' => $fiscalYearInfo['startYear'],
      'IncPeriodToYear' => $fiscalYearInfo['startYear'] + 1,

      // Ird fields
      'Section' => $section,
      'ERN' => $ern,
      'AssYr' => $fiscalYearInfo['startYear'] + 1,
      'YrErReturn' => $fiscalYearInfo['startYear'] + 1,
      'SubDate' => phpDateFormat($formDate, 'd/m/Y'),
      'PayerName' => $oaTeam['name'],
      'ErName' => $oaTeam['name'],
      'ErAddr' => $oaTeam['setting']['companyAddress'],
      'Designation' => $designation,
      'SignatureName' => $form->signature_name,
      'NoRecordBatch' => isset($form) ? $form->employees->count() : 1,
      'TotIncomeBatch' => 0, // isset($formSummary) ? $formSummary['totalEmployeeIncome'] : 0,
      'Employees' => [],
      'Recipient' => []
    ];
    return $result;
  }

  //***********************************
  // General Routeins for common info
  //***********************************
  protected static function getFormInfo($oaEmployee, $defaults, $fiscalYearInfo)
  {
    $joinedDate = substr($oaEmployee['joinedDate'], 0, 10);
    $jobEndedDate = substr($oaEmployee['jobEndedDate'], 0, 10);

    $empStartDate = $fiscalYearInfo['startDate'] > $joinedDate ? $fiscalYearInfo['startDate'] : $joinedDate;
    $empEndDate = isset($oaEmployee['jobEndedDate']) ?
      ($fiscalYearInfo['endDate'] < $jobEndedDate ? $fiscalYearInfo['endDate'] : $jobEndedDate) :
      $fiscalYearInfo['endDate'];

    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    // Employee
    if (isset($oaEmployee['jobEndedDate'])) {
      $jobEndedDate = phpDateFormat($oaEmployee['jobEndedDate'], 'd/m/Y');
      $fiscalYearStartBeforeCease = getFiscalYearStartOfDate($jobEndedDate);
    } else {
      $jobEndedDate = '';
      $fiscalYearStartBeforeCease = '';
    }
    $remarks = array_key_exists('Remarks', $defaults) ?
      $defaults['Remarks'] : '';

    return [
      // Original, Supplementary, Replacement
      'TypeOfForm' => 'O',
      // All dates in 'yyyy-mm-dd' format
      'JoinedDate' => 'joinedDate',
      'JobEndedDate' => 'jobEndedDate',
      'EmpStartDate' => $empStartDate,
      'EmpEndDate' => $empEndDate,
      'PerOfEmp' => $perOfEmp,
      'FiscalYearStartBeforeCease' => $fiscalYearStartBeforeCease,
      'HeaderPeriodToYear' => $fiscalYearInfo['endYear'],
      'Remarks' => $remarks,
      'IndOfRemark' => empty($remarks) ? '0' : '1',
      'Remarks' => $remarks
    ];
  }

  protected static function getEmployeeInfo($oaEmployee, $defaults)
  {
    $capacity = strtoupper($oaEmployee['jobTitle']);
    $ptPrinEmp = array_key_exists('ptPrinEmp', $defaults) ?
      $defaults['ptPrinEmp'] :
      '';

    $resAddr = count($oaEmployee['address']) > 0 ? $oaEmployee['address'][0]['text'] : '';
    $areaCodeResAddr = '';

    $posAddr = count($oaEmployee['address']) > 1 ? $oaEmployee['address'][1] : trans('tax.same_as_above');
    $areaCodePosAddr = ''; // array_key_exists('areaCodeResAddr', $defaults) ? $defaults['areaCodeResAddr'];

    $ppNum = '';
    if(empty($oaEmployee['identifyNumber'])) {
      $segs = [];
      if(!empty($oaEmployee['passport'])) {
        $segs[] = $oaEmployee['passport'];
      }
      if(isset($oaEmployee['country'])) {
        $segs[] = '(' + $oaEmployee['country']['name'] + ')';
      }
      $ppNum = implode(' ', $segs);
    }

    $result = [
      'HKID' => $oaEmployee['identityNumber'],
      'Surname' => $oaEmployee['lastName'],
      'GivenName' => $oaEmployee['firstName'],
      'NameInEnglish' => concatNames([$oaEmployee['firstName'] . ',', $oaEmployee['lastName']]),
      'NameInChinese' => getOAEmployeeChineseName($oaEmployee),
      'PhoneNum' => empty($oaEmployee['officePhone']) ? $oaEmployee['mobilePhone'] : $oaEmployee['officePhone'],
      'PpNum' => $ppNum,

      'ComRecNameEng' => array_key_exists('ComRecNameEng', $defaults) ? $defaults['ComRecNameEng'] :
        (array_key_exists('ComRecNameEng', $oaEmployee) ? $oaEmployee['ComRecNameEng'] : ''),

      'ComRecNameChi' => array_key_exists('ComRecNameChi', $defaults) ? $defaults['ComRecNameChi'] :
        (array_key_exists('ComRecNameEng', $oaEmployee) ? $oaEmployee['ComRecNameChi'] : ''),

      'ComRecBRN' => array_key_exists('ComRecBRN', $defaults) ? $defaults['ComRecBRN'] :
        (array_key_exists('ComRecBRN', $oaEmployee) ? $oaEmployee['ComRecBRN'] : ''),

      'Sex' => $oaEmployee['gender'],
      'Capacity' => $capacity,
      'PtPrinEmp' => $ptPrinEmp,

      'ResAddr' => $resAddr,
      'AreaCodeResAddr' => $areaCodePosAddr,
      'PosAddr' => $posAddr,
      'AreaCodePosAddr' => $areaCodePosAddr,

      // IR56F
      'CessationReason' => '',

      // IR56G
      'LeftAtYear' => '',
      'LeftAtMonth' => '',
      'LeftAtDay' => ''
    ];

    if(static::$hasDefaults) {
      $result['HKID'] = getDefault($defaults, 'hkid', $result['HKID']);
      $result['Surname'] = getDefault($defaults, 'surname', $result['Surname']);
      $result['GivenName'] = getDefault($defaults, 'givenName', $result['GivenName']);
      $result['NameInEnglish'] = getDefault($defaults, 'nameInEnglish', $result['NameInEnglish']);
      $result['NameInChinese'] = getDefault($defaults, 'nameInChinese', $result['NameInChinese']);
      $result['PhoneNum'] = getDefault($defaults, 'phoneNum', $result['PhoneNum']);
      $result['PpNum'] = getDefault($defaults, 'ppNum', $result['PpNum']);
      $result['ComRecNameEng'] = getDefault($defaults, 'comRecNameEng', $result['ComRecNameEng']);
      $result['ComRecNameChi'] = getDefault($defaults, 'comRecNameChi', $result['ComRecNameChi']);
      $result['Sex'] = getDefault($defaults, 'sex', $result['Sex']);
      $result['Capacity'] = getDefault($defaults, 'capacity', $result['Capacity']);
      $result['PtPrinEmp'] = getDefault($defaults, 'ptPrinEmp', $result['PtPrinEmp']);
      $result['ResAddr'] = getDefault($defaults, 'resAddr', $result['ResAddr']);
      $result['AreaCodeResAddr'] = getDefault($defaults, 'areaCodeResAddr', $result['AreaCodeResAddr']);
      $result['PosAddr'] = getDefault($defaults, 'posAddr', $result['PosAddr']);
      $result['AreaCodePosAddr'] = getDefault($defaults, 'areaCodePosAddr', $result['AreaCodePosAddr']);
      $result['CessationReason'] = getDefault($defaults, 'cessationReason', $result['CessationReason']);
    }
    return $result;
  }

  protected static function getMaritalInfo($oaEmployee, $defaults)
  {
    // 1=Single/Widowed/Divorced/Living Apart, 2=Married
    $maritalStatus = $oaEmployee['marital'] == 'married' ? 2 : 1;
    if ($maritalStatus == 1) {
      $spouseName = '';
      $spouseHkid = '';
      $spousePpNum = '';
    } else {
      $spouseName = array_key_exists('spouseName', $oaEmployee) ? $oaEmployee['spouseName'] : '';
      $spouseHkid = array_key_exists('spouseHKID', $oaEmployee) ? $oaEmployee['spouseHKID'] : '';
      $spousePpNum = $oaEmployee['spouseHKID'] == '' ?
        (array_key_exists('spousePpNum', $oaEmployee) ? $oaEmployee['spousePpNum'] : '') :
        '';
    }
    $spouseHkidPpNum = trim($spouseHkid).trim($spousePpNum);

    return [
      'MaritalStatus' => $maritalStatus,
      'SpouseName' => getDefault($defaults, 'spouseName', $spouseName),
      'SpouseHKID' => getDefault($defaults, 'spouseHkid', $spouseHkid),
      'SpousePpNum' => getDefault($defaults, 'spousePpNum', $spousePpNum),
      'SpouseHKIDPpNum' => $spouseHkidPpNum
    ];
  }

  protected static function getIncomeInfo($oaAuth, $team, $oaEmployee, $fiscalYearInfo, $perOfEmp, $defaults)
  {
    $options = [
      'oaAuth'=>$oaAuth,
      'team'=>$team,
      'oaEmployee'=>$oaEmployee,
      'fiscalYearInfo'=>$fiscalYearInfo,
      'perOfEmp' => $perOfEmp,
      'defaults' => $defaults
    ];

    $result =
      (in_array(static::$irdCode, ['IR56B', 'IR56F']) ? static::getIncomeInfoForIR56B($options) : []) +
      (static::$irdCode=='IR56M' ? static::getIncomeInfoForIR56M($options) : []) +
      (static::$irdCode=='IR56E' ? static::getIncomeInfoForIR56E($options) : []);
    return $result;
  }

  private static function getIncomeInfoForIR56E($options) {
    $oaAuth = $options['oaAuth'];
    $team = $options['team'];
    $oaEmployee = $options['oaEmployee'];
    $fiscalYearInfo = $options['fiscalYearInfo'];
    $perOfEmp = $options['perOfEmp'];
    $defaults = $options['defaults'];

    $oaSalaries = static::getOASalary();

    $addrOfPlace = '(address of place)';
    $natureOfPlace = '(nature of place)';

    $nameOfOverseaCo = '(non Hong Kong company)';
    $addrOfOverseaCo = '(non Hong Kong company address)';

    return [
      'MonthlyFixedIncome' => toCurrency(OAEmployeeHelper::getCommencementSalary(
        phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d'), $oaSalaries)),
      // share option
      'ShareBeforeEmp' => 0, // 0' => no, 1' => yes
      'MonthlyAllowance' => toCurrency(110 ),
      'FluctuatingIncome' => toCurrency(120),

      // Place of residence
      'PlaceProvided' => empty($addrOfPlace) ? '0' : '1',
      'AddrOfPlace' => $addrOfPlace,
      'NatureOfPlace' => $natureOfPlace,
      'RentPaidEr' => 1,
      'RentPaidEe' => 2,
      'RentRefund' => 3,
      'RentPaidErByEe' => 4,

      // Non-Hong Kong Income
      'OverseaIncInd' => empty($nameOfOverseaCo) ? '0' : '1', // 0' => not wholly or partly paid, 1' => yes
      'AmtPaidOverseaCo' => '',
      'NameOfOverseaCo' => $nameOfOverseaCo,
      'AddrOfOverseaCo' => $addrOfOverseaCo
    ];
  }

  private static function getIncomeInfoForIR56M($options) {
    $oaAuth = $options['oaAuth'];
    $team = $options['team'];
    $oaEmployee = $options['oaEmployee'];
    $fiscalYearInfo = $options['fiscalYearInfo'];
    $perOfEmp = $options['perOfEmp'];
    $defaults = $options['defaults'];

    // For IR56M
    // Service Period
    $joinedDate = substr($oaEmployee['joinedDate'], 0, 10);
    $jobEndedDate = substr($oaEmployee['jobEndedDate'], 0, 10);

    $empStartDate = $fiscalYearInfo['startDate'] > $joinedDate ? $fiscalYearInfo['startDate'] : $joinedDate;
    $empEndDate = isset($oaEmployee['jobEndedDate']) ?
      ($fiscalYearInfo['endDate'] < $jobEndedDate ? $fiscalYearInfo['endDate'] : $jobEndedDate) :
      $fiscalYearInfo['endDate'];
    $perOfService = phpDateFormat($empStartDate, 'd/m/Y') . ' - ' . phpDateFormat($empEndDate, 'd/m/Y');
    $amtOfType1 = array_key_exists('AmtOfType1', $defaults) ? $defaults['AmtOfType1'] : 0;
    $amtOfType2 = array_key_exists('AmtOfType2', $defaults) ? $defaults['AmtOfType2'] : 0;
    $amtOfType3 = array_key_exists('AmtOfType3', $defaults) ? $defaults['AmtOfType3'] : 0;
    $amtOfArtistFee = array_key_exists('AmtOfArtistFee', $defaults) ? $defaults['AmtOfArtistFee'] : 0;
    $amtOfCopyright = array_key_exists('AmtOfCopyright', $defaults) ? $defaults['AmtOfCopyright'] : 0;
    $amtOfConsultFee = array_key_exists('AmtOfConsultFee', $defaults) ? $defaults['AmtOfConsultFee'] : 0;
    $natureOtherInc1 = 'Services Fee';
    $amtOfOtherInc1 = array_key_exists('AmtOfOtherInc1', $defaults) ? $defaults['AmtOfOtherInc1'] : 0;
    $natureOtherInc2 = array_key_exists('NatureOtherInc2', $defaults) ? $defaults['NatureOtherInc2'] : 0;
    $amtOfOtherInc2 = array_key_exists('AmtOfOtherInc2', $defaults) ? $defaults['AmtOfOtherInc2'] : 0;
    $totalIncome = array_key_exists('TotalIncome', $defaults) ? $defaults['TotalIncome'] :
      $amtOfType1 +
      $amtOfType2 +
      $amtOfType3 +
      $amtOfArtistFee +
      $amtOfCopyright +
      $amtOfConsultFee +
      $amtOfOtherInc1 +
      $amtOfOtherInc2;

    $perOfType1 = empty($amtOfType1) ? '' : $perOfService;
    $perOfType2 = empty($amtOfType2) ? '' : $perOfService;
    $perOfType3 = empty($amtOfType3) ? '' : $perOfService;
    $perOfArtistFee = empty($amtOfArtistFee) ? '' : $perOfService;
    $perOfCopyright = empty($amtOfCopyright) ? '' : $perOfService;
    $perOfConsultFee = empty($amtOfConsultFee) ? '' : $perOfService;
    $perOfOtherInc1 = empty($amtOfOtherInc1) ? '' : $perOfService;
    $perOfOtherInc2 = empty($amtOfOtherInc2) ? '' : $perOfService;
    if ($amtOfOtherInc1 == 0) {
      $natureOtherInc1 = '';
    }
    if ($amtOfOtherInc2 == 0) {
      $natureOtherInc2 = '';
    }

    $amtOfSumWithheld = array_key_exists('AmtOfSumWithheld', $defaults) ? $defaults['AmtOfSumWithheld'] : 0;
    $indOfSumWithheld = empty($amtOfSumWithheld) ? '0' : '1';

    $remarks = array_key_exists('Remarks', $defaults) ? $defaults['Remarks'] : '';
    $indOfRemark = empty($remarks) ? '0' : '1';

    return [
      // For IR56M
      'AmtOfType1' => $amtOfType1,
      'PerOfType1' => $perOfType1,
      'AmtOfType2' => $amtOfType2,
      'PerOfType2' => $perOfType2,
      'AmtOfType3' => $amtOfType3,
      'PerOfType3' => $perOfType3,
      'AmtOfArtistFee' => $amtOfArtistFee,
      'PerOfArtistFee' => $perOfArtistFee,
      'AmtOfCopyright' => $amtOfCopyright,
      'PerOfCopyright' => $perOfCopyright,
      'AmtOfConsultFee' => $amtOfConsultFee,
      'PerOfConsultFee' => $perOfConsultFee,

      'NatureOtherInc1' => $natureOtherInc1,
      'AmtOfOtherInc1' => $amtOfOtherInc1,
      'PerOfOtherInc1' => $perOfOtherInc1,

      'NatureOtherInc2' => $natureOtherInc2,
      'AmtOfOtherInc2' => $amtOfOtherInc2,
      'PerOfOtherInc2' => $perOfOtherInc2,

      'TotalIncome' => $totalIncome,

      'IndOfSumWithheld' => $indOfSumWithheld,
      'AmtOfSumWithheld' => $amtOfSumWithheld,

      'IndOfRemark' => $indOfRemark,
      'Remarks' => $remarks
    ];
  }

  private static function getIncomeInfoForIR56B($options) {
    $oaAuth = $options['oaAuth'];
    $team = $options['team'];
    $oaEmployee = $options['oaEmployee'];
    $fiscalYearInfo = $options['fiscalYearInfo'];
    $perOfEmp = $options['perOfEmp'];
    $defaults = $options['defaults'];

    $oaPayrollSummary = static::getOAPayrollSummary($oaAuth, $team, $oaEmployee, $fiscalYearInfo);
    // Income Particulars
    $tableMapping = [
      'Salary' => 'salary',
      'LeavePay' => 'leave_pay',
      'DirectorFee' => 'director_fee',
      'CommFee' => 'comm_fee',
      'Bonus' => 'bonus',
      'BpEtc' => 'bp_etc',
      'PayRetire' => 'pay_retire',
      'SalTaxPaid' => 'sal_tax_paid',
      'EduBen' => 'edu_ben',
      'GainShareOption' => 'gain_share_option',
      'Pension' => 'pension',
      'SpecialPayments' => 'special_payments'
    ];

    $oaPayrollSummary['salary'] = getDefault($defaults, 'salary', $oaPayrollSummary['salary']);
    $oaPayrollSummary['leave_pay'] = getDefault($defaults, 'leave_pay', $oaPayrollSummary['leave_pay']);
    $oaPayrollSummary['director_fee'] = getDefault($defaults, 'director_fee', $oaPayrollSummary['director_fee']);
    $oaPayrollSummary['comm_fee'] = getDefault($defaults, 'comm_fee', $oaPayrollSummary['comm_fee']);
    $oaPayrollSummary['bonus'] = getDefault($defaults, 'bonus', $oaPayrollSummary['bonus']);
    $oaPayrollSummary['bp_etc'] = getDefault($defaults, 'bp_etc', $oaPayrollSummary['bp_etc']);
    $oaPayrollSummary['pay_retire'] = getDefault($defaults, 'pay_retire', $oaPayrollSummary['pay_retire']);
    $oaPayrollSummary['sal_tax_paid'] = getDefault($defaults, 'sal_tax_paid', $oaPayrollSummary['sal_tax_paid']);
    $oaPayrollSummary['edu_ben'] = getDefault($defaults, 'edu_ben', $oaPayrollSummary['edu_ben']);
    $oaPayrollSummary['gain_share_option'] = getDefault($defaults, 'gain_share_option', $oaPayrollSummary['gain_share_option']);
    $oaPayrollSummary['pension'] = getDefault($defaults, 'pension', $oaPayrollSummary['pension']);
    $oaPayrollSummary['special_payments'] = getDefault($defaults, 'special_payments', $oaPayrollSummary['special_payments']);

    $natureOtherRAP1 = '';
    $perOfOtherRAP1 = '';
    $amtOfOtherRAP1 = '';

    $natureOtherRAP2 = '';
    $perOfOtherRAP2 = '';
    $amtOfOtherRAP2 = '';

    $natureOtherRAP3 = '';
    $perOfOtherRAP3 = '';
    $amtOfOtherRAP3 = '';

    $incomeSummary = [];

    foreach ($tableMapping as $irdField => $token) {
      if ($irdField == 'Salary') {
        $incomeSummary['PerOf' . $irdField] = $perOfEmp;
      } else {
        $incomeSummary['PerOf' . $irdField] = $oaPayrollSummary[$token] > 0 ? $perOfEmp : '';
      }
      $incomeSummary['AmtOf' . $irdField] = $oaPayrollSummary[$token];
    }

    if (isset($oaPayrollSummary)) {
      if (count($oaPayrollSummary['other_raps']) > 0) {
        $natureOtherRAP1 = $oaPayrollSummary['other_raps'][0]['nature'];
        $perOfOtherRAP1 = $perOfEmp;
        $amtOfOtherRAP1 = $oaPayrollSummary['other_raps'][0]['amt'];
      }

      if (count($oaPayrollSummary['other_raps']) > 1) {
        $natureOtherRAP2 = $oaPayrollSummary['other_raps'][1]['nature'];
        $perOfOtherRAP2 = $perOfEmp;
        $amtOfOtherRAP2 = $oaPayrollSummary['other_raps'][1]['amt'];
      }

      if (count($oaPayrollSummary['other_raps']) > 2) {
        $natureOtherRAP3 = $oaPayrollSummary['other_raps'][2]['nature'];
        $perOfOtherRAP3 = $perOfEmp;
        $amtOfOtherRAP3 = $oaPayrollSummary['other_raps'][2]['amt'];
      }
    }

    $addrOfPlace1 = '';
    $natureOfPlace1 = '';
    $perOfPlace1 = '';
    $rentPaidEr1 = '';
    $rentPaidEe1 = '';
    $rentRefund1 = '';
    $rentPaidErByEe1 = '';

    $addrOfPlace2 = '';
    $natureOfPlace2 = '';
    $perOfPlace2 = '';
    $rentPaidEr2 = '';
    $rentPaidEe2 = '';
    $rentRefund2 = '';
    $rentPaidErByEe2 = '';

    if (array_key_exists('placeOfResInd', $defaults)) {
      $placeOfResInd = $defaults['placeOfResInd'];
      if ($defaults['placeOfResInd'] == '1') {
        // Place #1
        $addrOfPlace1 = $defaults['addrOfPlace1'];
        $natureOfPlace1 = $defaults['natureOfPlace1'];
        $perOfPlace1 = $defaults['perOfPlace1'];
        $rentPaidEr1 = $defaults['rentPaidEr1'];
        $rentPaidEe1 = $defaults['rentPaidEe1'];
        $rentRefund1 = $defaults['rentRefund1'];
        $rentPaidErByEe1 = $defaults['rentPaidErByEe1'];
        // Place #2
        $addrOfPlace2 = $defaults['addrOfPlace2'];
        $natureOfPlace2 = $defaults['natureOfPlace2'];
        $perOfPlace2 = $defaults['perOfPlace2'];
        $rentPaidEr2 = $defaults['rentPaidEr2'];
        $rentPaidEe2 = $defaults['rentPaidEe2'];
        $rentRefund2 = $defaults['rentRefund2'];
        $rentPaidErByEe2 = $defaults['rentPaidErByEe2'];
      }
    }

    if (array_key_exists('overseaIncInd', $defaults)) {
      $result['OverseaIncInd'] = $defaults['overseaIncInd'];
      if ($defaults['overseaIncInd'] == '1') {
        $result['AmtPaidOverseaCo'] = $defaults['amtPaidOverseaCo'];
        $result['NameOfOverseaCo'] = $defaults['nameOfOverseaCo'];
        $result['AddrOfOverseaCo'] = $defaults['addrOfOverseaCo'];
      }
    }

    return [
      // Income Particulars
      // 1. Salary
      'PerOfSalary' => $incomeSummary['PerOfSalary'],
      'AmtOfSalary' => $incomeSummary['AmtOfSalary'],
      //
      // 2. LeavePay
      'PerOfLeavePay' => $incomeSummary['PerOfLeavePay'],
      'AmtOfLeavePay' => $incomeSummary['AmtOfLeavePay'],
      //
      // 3. DirectorFee
      'PerOfDirectorFee' => $incomeSummary['PerOfDirectorFee'],
      'AmtOfDirectorFee' => $incomeSummary['AmtOfDirectorFee'],
      //
      // 4. CommFee
      'PerOfCommFee' => $incomeSummary['PerOfCommFee'],
      'AmtOfCommFee' => $incomeSummary['AmtOfCommFee'],
      //
      // 5. Bonus
      'PerOfBonus' => $incomeSummary['PerOfBonus'],
      'AmtOfBonus' => $incomeSummary['AmtOfBonus'],
      //
      // 6. BpEtc
      'PerOfBpEtc' => $incomeSummary['PerOfBpEtc'],
      'AmtOfBpEtc' => $incomeSummary['AmtOfBpEtc'],
      //
      // 7. PayRetire
      'PerOfPayRetire' => $incomeSummary['PerOfPayRetire'],
      'AmtOfPayRetire' => $incomeSummary['AmtOfPayRetire'],
      //
      // 8. SalTaxPaid
      'PerOfSalTaxPaid' => $incomeSummary['PerOfSalTaxPaid'],
      'AmtOfSalTaxPaid' => $incomeSummary['AmtOfSalTaxPaid'],
      //
      // 9. EduBen
      'PerOfEduBen' => $incomeSummary['PerOfEduBen'],
      'AmtOfEduBen' => $incomeSummary['AmtOfEduBen'],
      //
      // 10. GainShareOption
      'PerOfGainShareOption' => $incomeSummary['PerOfGainShareOption'],
      'AmtOfGainShareOption' => $incomeSummary['AmtOfGainShareOption'],
      //
      // 11.1 RAP 1
      'NatureOtherRAP1' => $natureOtherRAP1,
      'PerOfOtherRAP1' => $perOfOtherRAP1,
      'AmtOfOtherRAP1' => $amtOfOtherRAP1,

      // 11.2 RAP 2
      'NatureOtherRAP2' => $natureOtherRAP2,
      'PerOfOtherRAP2' => $perOfOtherRAP2,
      'AmtOfOtherRAP2' => $amtOfOtherRAP2,

      // 11.3 RAP 3
      'NatureOtherRAP3' => $natureOtherRAP3,
      'PerOfOtherRAP3' => $perOfOtherRAP3,
      'AmtOfOtherRAP3' => $amtOfOtherRAP3,

      // 12 Pension
      'PerOfPension' => $incomeSummary['PerOfPension'],
      'AmtOfPension' => $incomeSummary['AmtOfPension'],

      // Total Income
      'TotalIncome' => $oaPayrollSummary['totalIncome'],

      // For IR56F
      'NatureSpecialPayments' => $oaPayrollSummary['special_payments_nature'],
      'PerOfSpecialPayments' => $incomeSummary['PerOfSpecialPayments'],
      'AmtOfSpecialPayments' => $incomeSummary['AmtOfSpecialPayments'],

      // For IR56F ends
      //
      // Place of Residence
      'PlaceOfResInd' => '0',

      // Place #1
      'AddrOfPlace1' => $addrOfPlace1,
      'NatureOfPlace1' => $natureOfPlace1,
      'PerOfPlace1' => $perOfPlace1,
      'RentPaidEr1' => $rentPaidEr1,
      'RentPaidEe1' => $rentPaidEe1,
      'RentRefund1' => $rentRefund1,
      'RentPaidErByEe1' => $rentPaidErByEe1,

      // Place #2
      'AddrOfPlace2' => $addrOfPlace2,
      'NatureOfPlace2' => $natureOfPlace2,
      'PerOfPlace2' => $perOfPlace2,
      'RentPaidEr2' => $rentPaidEr2,
      'RentPaidEe2' => $rentPaidEe2,
      'RentRefund2' => $rentRefund2,
      'RentPaidErByEe2' => $rentPaidErByEe2,

      // Non-Hong Kong Income
      'OverseaIncInd' => '0',
      'AmtPaidOverseaCo' => '',
      'NameOfOverseaCo' => '',
      'AddrOfOverseaCo' => '',

    ];
  }

  protected static function getTestingDefaults() {
    return [];
  }

  public static function get($team, $employeeId, $options=[])
  {
    $isSample = array_key_exists('mode', $options) ? $options['mode'] == 'sample' : false;
    $isTesting = array_key_exists('mode', $options) ? $options['mode'] == 'testing' : false;
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    $form = array_key_exists('form', $options) ? $options['form'] : null;

    if (static::$testing || $isTesting || static::$forceDefaults) {
      $defaults = static::getTestingDefaults();
    }

    static::$team = $team;
    $oaAuth = OAHelper::refreshTokenByTeam(static::$team);
    static::$employeeId = $employeeId;
    static::$oaAuth = $oaAuth;
    $oaEmployee = static::getOAAdminEmployee();
    if (is_null($oaEmployee)) {
      return null;
    }

    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);
    $formInfo = static::getFormInfo($oaEmployee, $defaults, $fiscalYearInfo);
    $employeeInfo = static::getEmployeeInfo($oaEmployee, $defaults);

    $maritalInfo = static::getMaritalInfo($oaEmployee, $defaults);
    $incomeInfo = static::getIncomeInfo(
      $oaAuth,
      $team,
      $oaEmployee,
      $fiscalYearInfo,
      $formInfo['PerOfEmp'],
      $defaults);

//    $result = [
//      'fileNo' => $registrationNumber,
//      'ern' => $ern,
//      'erName' => $oaTeam['name'],
//      'erAddress' => $oaTeam['setting']['companyAddress'],
//      'signatureName' => $signatureName,
//      'designation' => $designation,
//      'formDate' => phpDateFormat($formDate, 'd/m/Y')
//    ];

    // Employee
//    if (isset($oaEmployee)) {
//      if (isset($oaEmployee['jobEndedDate'])) {
//        $jobEndedDate = phpDateFormat($oaEmployee['jobEndedDate'], 'd/m/Y');
//        $fiscalYearStartBeforeCease = phpDateFormat(getFiscalYearStartOfDate($jobEndedDate), 'd/m/Y');
//      } else {
//        $jobEndedDate = '';
//        $fiscalYearStartBeforeCease = '';
//      }
//      $result = array_merge($result, [
//        'name' => $oaEmployee['lastName'] . ', ' . $oaEmployee['firstName'],
//        'surname' => $oaEmployee['lastName'],
//        'nameInChinese' => getOAEmployeeChineseName($oaEmployee),
//        'hkid' => $oaEmployee['identityNumber'],
//        'ppNum' => empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : 'xxxxx',
//        'gender' => $oaEmployee['gender'],
//        'maritalStatus' => ($oaEmployee['marital'] == 'married' ? 2 : 1),
//        // 1=Single/Widowed/Divorced/Living Apart, 2=Married
//
//        'endDateOfEmp' => $jobEndedDate,
//        'fiscalYearStartDateBeforeCease' => $fiscalYearStartBeforeCease
//      ]);
//    }

    return static::prepareResult(
      $sheetNo,
      $formInfo,
      $employeeInfo,
      $maritalInfo,
      $incomeInfo
    );
  }

}