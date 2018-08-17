<?php namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Command;
use App;

class CommandHelper
{
  public static function start($commandName, $handler)
  {
    \Carbon\Carbon::setLocale(config('app.locale'));
    App::setLocale('hk');
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
      if ($durationPassed < 10 && ($command->mode == 'auto')) {
        echo "duration since last checking < 60sec => quit\n";
        return;
      }
    }

    while (true) {
      $now = now();
      $command->last_checked_at = $now;
      $command->save();
      $pass = $handler($command);
      if(!$pass) {
        echo 'Handler result not passed!'; nf();
        break;
      }
      sleep(1);

      // Check enabled
      $command = Command::whereName($commandName)->first();
      if (!$command->enabled) {
        logConsole('messages.command_not_enabled');
        break;
      }

      if (!$command->loop) {
        logConsole('messages.command_loop_not_enabled');
        break;
      }
    }
  }
}