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
}