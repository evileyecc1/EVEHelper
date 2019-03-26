<?php

namespace App\Console\Commands\ESI\Update;

use Carbon\Carbon;
use ESIHelper\ESIHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class JitaMarketOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:jita_prices';

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
     * @return mixed
     */
    public function handle(ESIHelper $ESIHelper)
    {
        $page = 1;
        $max_pages = 1;
        $jita_orders = collect();

        $error_limit = 1;

        while (true) {
            $esi_response = $ESIHelper->invoke('get', '/v1/markets/{region_id}/orders/', ['region_id' => '10000002'], ['page' => $page], [], false);
            //if ($esi_response->status_code == 304)
            //    return;
            if ($esi_response->status_code != 200) {
                \Log::alert('Update makret orders failed.Error details:'.json_encode($esi_response));
                if ($error_limit > 3) {
                    return;
                } else {
                    $error_limit = $error_limit + 1;
                    continue;
                }
            }
            $max_pages = $esi_response->headers['X-Pages'][0];
            if ($page >= $max_pages) {
                break;
            }
            $orders = json_decode($esi_response->response_text);
            foreach ($orders as $order) {
                if ($order->system_id == 30000142) {
                    $type = $order->is_buy_order ? 'buy' : 'sell';
                    if ($jita_orders->has($order->type_id)) {
                        $prices = $jita_orders->get($order->type_id);
                        if ($type == 'buy') {
                            if ($order->price > $prices['buy']) {
                                $prices['buy'] = $order->price;
                                $jita_orders->put($order->type_id, $prices);
                            }
                        } else {
                            if ($order->price < $prices['sell']) {
                                $prices['sell'] = $order->price;
                                $jita_orders->put($order->type_id, $prices);
                            }
                        }
                    } else {
                        $prices = ['buy' => 0.0, 'sell' => 0.0];
                        $prices[$type] = $order->price;
                        $jita_orders->put($order->type_id, $prices);
                    }
                }
            }
            $page = $page + 1;
            $this->info('Current process:'.$page.'/'.$max_pages.PHP_EOL);
        }
        $jita_orders->each(function ($item, $key) {
            Cache::put('jita_price:'.$key, $item, Carbon::now()->addDay());
        });
        $this->info('Total type orders '.$jita_orders->count());
    }
}
