<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DeleteUnusedWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete unused webhook';

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
                     ->where('app_version', config('constants.app_version'))
            //->whereDoesntHave('plan')
                     ->get();

        if ($shops->isNotEmpty()) {
            $error = [];

            $ids = [];

            $topics = config('constants.shopify_webhooks');
//            if (($key = array_search('app/uninstalled', $topics)) !== false) {
//                unset($topics[$key]);
//            }

            $bar = $this->output->createProgressBar(count($shops));
            $bar->start();

            foreach ($shops as $i => $shop) {

                foreach ($topics as $key => $topic) {

                    $shopUrl = 'https://' . $shop->name . config('constants.shopify_api_version') . '/';

                    $response = Http::withHeaders([
                        'X-Shopify-Access-Token' => $shop->getAuthPassword(),
                    ])->get($shopUrl . 'webhooks.json', [
                        'topic' => $topic,
                    ]);

                    if ($response->successful()) {
                        if (isset($response->object()->webhooks[0]->id)) {
                            Http::withHeaders([
                                'X-Shopify-Access-Token' => $shop->getAuthPassword(),
                            ])->delete($shopUrl . 'webhooks/' . $response->object()->webhooks[0]->id . '.json', [
                                'topic' => $topic,
                            ]);

                            info($i . ' - ' . $shopUrl . ' - ' . $topic);
                        }
                    } else {
                        $error[$shop->name][$i][$key][] = $topic;
                        $error[$shop->name][$i][$key][] = $response->body();
                    }
                }

                $ids[] = $shop->id;

                $bar->advance();
            }


            User::whereIn('id', $ids)->update([
                'app_version' => config('constants.app_version') - 1,
            ]);

            $bar->finish();
            $this->newLine();

            info(print_r($error, true));
        } else {
            info('No shop found .. for delete webhooks');
        }
    }
}
