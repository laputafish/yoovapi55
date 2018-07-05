<?php
namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Folder;
use App\Models\Equipment;
use App\Models\Command;

use App\Events\ScannedDocumentReceived;
use App\Helpers\EquipmentHelper;

class ScannedDocumentHelper {
  public static function check($mode = 'auto')
  {
    \Carbon\Carbon::setLocale(config('app.locale'));

    $command = Command::whereName('checkScanned')->first();

    // create command if not exist
    if(is_null($command)) {
      $command = Command::create([
        'name' => 'checkScanned',
        'enabled' => 1
      ]);
    }

    // check user action
    switch($mode) {
      case 'enable':
        $command->enabled = 1;
        $command->save();
        return;
      case 'disable':
        $command->enabled = 0;
        $command->save();
        break;
      case 'manual':
        $command->mode = 'manual';
        $command->save();
        break;
      case 'auto':
        $command->mode = 'auto';
        $command->save();
        break;
    }

    if(!$command->enabled) {
      return;
    }

    if(isset($command->last_checked_at)) {
      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < 60 && ($command->mode == 'auto')) {
        echo "duration since last checking < 60sec => quit\n";
        return;
      }
    }

    // Process
    $path = EquipmentHelper::getSetting('scanner', 'path');
    while(true) {
      $now = now();
      $command->last_checked_at = $now;
      $command->save();
      $files = \File::allFiles($path);
      foreach( $files as $file) {
        // move file
        $document = FolderHelper::createNewDocument( $file );
        event(new ScannedDocumentReceivedEvent($document));
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
