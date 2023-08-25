<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take database backup';

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
        $appName = Str::of(config('constants.app_name'))->slug('_');
        $filename = $appName . '__' . env("DB_DATABASE") . '__' . Carbon::now()->format('d_m_Y-H_i_s') . ".sql";
        $storagePath = 'db_backups/';

        if (!Storage::exists($storagePath)) {
            Storage::makeDirectory($storagePath);
        }

        /* remove telescope entries while backing up DB */
        $this->call('telescope:clear');

        /* sql backup command */
        $command = "mysqldump -u " . env('DB_USERNAME') . " -p\"" . env('DB_PASSWORD') . "\" " . env('DB_DATABASE') . "  > " . storage_path('app/') . $storagePath . $filename;

        $returnVar = null;
        $output = null;
        $array = Storage::allFiles('db_backups');
        if (count($array) >= 3) {
            $min = Arr::sort($array);
            array_pop($min);
            Storage::delete($min);
        }

        exec($command, $output, $returnVar);
    }
}
