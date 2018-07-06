<?php namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Command;

class CommandHelper
{
  public static function start($commandName, $handler)
  {
    \Carbon\Carbon::setLocale(config('app.locale'));

    $command = Command::whereName($commandName)->first();
    if (is_null($command)) {
      $command = Command::create([
        'name' => $commandName,
        'enabled' => 1
      ]);
    }

    if (!$command->enabled) {
      return;
    }

    if (isset($command->last_checked_at)) {
      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < 60 && ($command->mode == 'auto')) {
        echo "duration since last checking < 60sec => quit\n";
        return;
      }
    }

    while (true) {
      $now = now();
      $command->last_checked_at = $now;
    //  $command->save();
      $handler($command);
      sleep(1);

      // Check enabled
      $command = Command::whereName($commandName)->first();
      if (!$command->enabled) {
        echo "command not enabled => quit\n";
        break;
      }

      if ($command->mode == 'manual') {
        echo "command mode = manual => after run once => quit\n";
        break;
      }
    }
  }
}