<?php namespace App\Helpers;


class FormHelper {
  public static function removeIrdFormFiles($form) {
    $team = $form->team;
    $employeeFormFiles = self::getFormEmployeeFiles($form);
    $formPath = storage_path('app/teams/'.$team->oa_team_id.'/'.$form->id);
    foreach($employeeFormFiles as $file) {
      $path = $formPath.'/'.$file;
      if(file_exists($path)) {
        unlink(storage_path($path));
      }
    }
    if(file_exists($formPath)) {
      rmdir($formPath);
    }
  }

  public static function getFormEmployeeFiles($form) {
    $result = [];
    foreach($form->employees as $employee) {
      $result[] = $employee->file;
    }
    return $result;
  }

  public static function getFiscalYearInfo($form, $options=[]) {
    $result = [];

    $fiscalYearStart = getCurrentFiscalYearStartDate();
    if(isset($form)) {
      $fiscalYearStart = $form->fiscal_start_year . '-04-01';
    } else if(array_key_exists('year', $options)) {
      $fiscalYearStart = $options['year'];
    }

    $startYear = (int)substr($fiscalYearStart, 0, 4);
    $result['startDate' ] = $fiscalYearStart;
    $result['startYear'] = $startYear;
    $result['endYear'] = $startYear + 1;
    $result['startDate'] = $startYear . '-04-01';
    $result['endDate'] = ($startYear + 1) . '-03-31';

    return $result;
  }
}