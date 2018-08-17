<?php namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Command;
use App;

class CommandHelper
{
  protected static $COOL_PERIOD = 10;

  public static function start($commandName, $handler)
  {
    \Carbon\Carbon::setLocale(config('app.locale'));
    App::setLocale('hk');

    echo "Instance count: ";
    $instanceCount = system('ps -A | grep php$ | wc -l');
    echo 'Ready.'; nf();
    if($instanceCount > 1) {
      echo 'Another instance already running.'; nf();
      echo 'Quit now.'; nf();
      return;
    }

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
      if ($durationPassed < static::$COOL_PERIOD && ($command->mode == 'auto')) {
        echo "duration since last checking < ".static::$COOL_PERIOD."sec => quit\n";
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