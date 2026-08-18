<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Runs every minute and only actually backs up once the clock matches the
// saved "Backup Time" setting, and only when "Auto Backup" is enabled.
// This lets the schedule follow whatever time is saved in Settings without
// needing to re-register a cron entry every time that setting changes.
// Requires the standard Laravel cron entry on the server:
//   * * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
Schedule::command('backup:run')
    ->everyMinute()
    ->when(function () {
        if (! Setting::get('auto_backup')) {
            return false;
        }

        return now()->format('H:i') === Setting::get('backup_time', '02:00');
    })
    ->withoutOverlapping();