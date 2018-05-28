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
      if(!$command->enabled) return;

      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < 60) {
        return;
      }
    }

    if(is_null($command)) {
      $command = Command::create([
        'name' => 'checkScanned'
      ]);
    }

    // Process
    $scanner = Equipment::whereName('scanner')->first();
    $pathSettings = json_decode( $scanner->settings, true );
    $path = $pathSettings['path'];
    while(true) {
      $now = now();
      $command->last_checked_at = $now;
      $command->save();
      $files = \File::allFiles($path);
      echo $now. ': '.count($files)."<Br/>\n";
      foreach( $files as $file) {
        // move file
        FolderHelper::moveScannedFile( $file );
      }
      sleep(1);

      // Check enabled
      $command = Command::whereName('checkScanned')->first();
      if(!$command->enabled) {
        break;
      }
    }
  }
}