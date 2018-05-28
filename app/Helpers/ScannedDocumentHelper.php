<?php
namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Folder;
use App\Models\Equipment;
use App\Models\Command;

class ScannedDocumentHelper {
  public static function check()
  {
    \Carbon\Carbon::setLocale(config('app.locale'));

    $command = Command::whereName('checkScanned')->first();

    if(isset($command) && isset($command->last_checked_at)) {
      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < 60) {
        return;
      }
    }

    // Process
    $scanner = Equipment::whereName('scanner')->first();
    $pathSettings = json_decode( $scanner->settings, true );
    $path = $pathSettings['path'];

      $command = Command::create([
        'name'=>'checkScanned'
      ]);
  }
}