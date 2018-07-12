<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Test;

class TestController extends BaseAuthController
{
  public function insertRecords($count) {
    $t1 = time();
    dd($t1);
    for($i=0; $i<$count; $i++) {
      Test::create([
        'user_id'=>$i*10
      ]);
    }
    $t2 = time();
    $diff = $t2 - $t1;
    return $diff;

  }
}