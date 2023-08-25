<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = Carbon::now()->subDay()->format('Y-m-d');
//        $date = '2022-01-01';
        $backupLogsFile = storage_path('logs/laravel-' . $date . '.log');

        $logsFile = storage_path('logs/laravel.log');

        if (File::exists($logsFile)) {

            $logContent = File::get($logsFile);

            if (!File::exists($backupLogsFile)) {
                File::put($backupLogsFile, '');
                File::chmod($backupLogsFile, 0777);
            }

            File::append($backupLogsFile, $logContent);

            File::put($logsFile, '');
        }
    }
}
