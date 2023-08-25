<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class CreateWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create webhook';

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
        $shops = User::whereNotNull('password')
                     ->where('password', '!=', '')
                     //->whereHas('plan')
                     ->orderByDESC('id')
                     ->get();

        $error = [];

        $bar = $this->output->createProgressBar(count($shops));
        $bar->start();

        foreach ($shops as $shop) {

            $topics = config('constants.shopify_webhooks');
            //$topics = ['app/uninstalled'];

            foreach ($topics as $key => $topic) {

                $shopUrl = 'https://' . $shop->name . config('constants.shopify_api_version') . '/';

                $topicCallbackSlug = str_replace(str_split('_/'), '-', $topic);  //  replace '_' and '/' with '-'(dash) sign
                $topicCallbackJobClass = str_replace(' ', '', ucwords(str_replace('-', ' ', $topicCallbackSlug)) . 'Job');
                //$topicCallbackClass[$topicCallbackSlug] = $topicCallbackJobClass;

                if (!class_exists($topicCallbackJobClass)) {
                    Artisan::call('make:job ' . $topicCallbackJobClass);
                }

                $webhookArray['webhook'] = [
                    'topic' => $topic,
                    'address' => env('APP_URL') . 'webhook/' . $topicCallbackSlug,
                    'format' => 'json',
                ];

                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $shop->getAuthPassword(),
                ])->post($shopUrl . 'webhooks.json', $webhookArray);

                if (!$response->successful()) {
                    $error[$shop->name] = $response->body();
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        info(print_r($error, true));
    }
}
