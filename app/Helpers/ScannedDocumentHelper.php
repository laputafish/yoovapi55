<?php
namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Folder;
use App\Models\Equipment;
use App\Models\Command;
use App\Events\ScannedDocumentReceived;

class ScannedDocumentHelper {
  public static function check($mode = 'auto')
  {
    \Carbon\Carbon::setLocale(config('app.locale'));

    $command = Command::whereName('checkScanned')->first();

    if(isset($command) && isset($command->last_checked_at)) {
      echo 'enabled: '.$command->enabled."<br/>\n";
      echo 'mode: '.$command->mode."<Br/>\n";

      if(!$command->enabled && ($command->mode!=$mode)) return;

      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < 60 && ($command->mode == 'auto')) {
        echo "duration since last checking < 60sec => quit\n";
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
        $document = FolderHelper::createNewDocument( $file );
        event(new ScannedDocumentReceived($document));
      }
      sleep(1);

      // Check enabled
      $command = Command::whereName('checkScanned')->first();
      if(!$command->enabled) {
        echo "command not enabled => quit\n";
        break;
      }

      if($command->mode == 'manual') {
        echo "command mode = manual => after run once => quit\n";
        break;
      }
    }
  }
}