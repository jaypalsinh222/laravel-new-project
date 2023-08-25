<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

if (!function_exists('change_money_format')) {

    function change_money_format($currency, $symbol = false, $onlyNumber = false)
    {
        $shop = auth()->user();
        $currencyFormat = $shop['currency'] ?? '';

        if (Str::contains($currencyFormat, '{{ amount }}')) {
            $amount = number_format($currency, 2, ".", ",");
            $currencyFormat = Str::replace('{{ amount }}', !$symbol ? $amount : '', $currencyFormat);
        } else if (Str::contains($currencyFormat, '{{amount}}')) {
            $amount = number_format($currency, 2, ".", ",");
            $currencyFormat = Str::replace('{{amount}}', !$symbol ? $amount : '', $currencyFormat);
        } else if (Str::contains($currencyFormat, '{{ amount_no_decimals }}')) {
            $amount = number_format($currency, 0, "", ",");
            $currencyFormat = Str::replace('{{ amount_no_decimals }}', !$symbol ? $amount : '', $currencyFormat);
        } else if (Str::contains($currencyFormat, '{{ amount_with_comma_separator }}')) {
            $amount = number_format($currency, 2, ",", ".");
            $currencyFormat = Str::replace('{{ amount_with_comma_separator }}', !$symbol ? $amount : '', $currencyFormat);
        } else if (Str::contains($currencyFormat, '{{ amount_no_decimals_with_comma_separator }}')) {
            $amount = number_format($currency, 0, "", ".");
            $currencyFormat = Str::replace('{{ amount_no_decimals_with_comma_separator }}', !$symbol ? $amount : '', $currencyFormat);
        } else if (Str::contains($currencyFormat, '{{ amount_with_apostrophe_separator }}')) {
            $amount = number_format($currency, 2, ".", "'");
            $currencyFormat = Str::replace('{{ amount_with_apostrophe_separator }}', !$symbol ? $amount : '', $currencyFormat);
        } else {
            return $currency;
        }
        return $currencyFormat;
    }
}

if (!function_exists('ip_check')) {
    /**
     * Cirkle custom check ip of the user.
     *
     * @return bool
     */
    function ip_check()
    {
//        if (
//            request()->server('HTTP_CF_CONNECTING_IP') == env('DEBUG_IP_IPV4')
//            || Str::contains(request()->server('HTTP_CF_CONNECTING_IP'), env('DEBUG_IP_IPV6'))
//            || Str::contains(request()->ip(), env('DEBUG_IP_IPV6'))
//            || request()->ip() == env('DEBUG_IP_IPV4')
//            || request()->ip() == '127.0.0.1'
//        )
        if (Str::contains(request()->server('HTTP_CF_CONNECTING_IP'), env('DEBUG_IP_IPV4'))
            || Str::contains(request()->server('HTTP_CF_CONNECTING_IP'), env('DEBUG_IP_IPV6'))
            || Str::contains(request()->ip(), env('DEBUG_IP_IPV6'))
            || Str::contains(request()->ip(), env('DEBUG_IP_IPV4'))
            || Str::contains(request()->ip(), '127.0.0.1')
        )
            return true;
        else
            return false;
    }
}
if (!function_exists('cs_dump')) {
    /**
     * Cirkle custom dump function for debug enable for particular IP address which is located on (.env) file.
     *
     * @param ...$vars - pass (,)comma separated multiple variable
     * @return bool|void
     */
    function cs_dump(...$vars)
    {
        if (ip_check())
            return dump(...$vars);
        else
            return true;
    }
}
if (!function_exists('cs_dd')) {
    /**
     * Cirkle custom die and dump function for debug enable for particular IP address which is located on (.env) file.
     *
     * @param ...$vars - pass (,)comma separated multiple variable
     * @return bool|void
     */
    function cs_dd(...$vars)
    {
        if (ip_check())
            return dd(...$vars);
        else
            return true;
    }
}

if (!function_exists('returnJson')) {
    /**
     * @param        $success
     * @param        $message
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    function returnJson($success, $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data], $status);
    }
}

if (!function_exists('returnException')) {
    /**
     * @param $e
     * @return JsonResponse
     */
    function returnException($e): JsonResponse
    {
        Log::debug("\nCode: " . $e->getCode() . "\nLine: " . $e->getLine() . "\nMessage: " . $e->getMessage());
        Log::debug($e->getFile());
        return returnJson(false, 'We unable to process your request right now please try after sometime.', [], 500);
    }
}

if (!function_exists('getShopDomain')) {
    function getShopDomain($request = null)
    {
        if ($request->filled('host') && Str::contains(base64_decode($request->get('host')), 'admin.shopify.com/store/')) {
            return Str::replace('admin.shopify.com/store/', '', base64_decode($request->get('host'))) . '.myshopify.com';
        } else if ($request->filled('host') && Str::contains(base64_decode($request->get('host')), '/admin')) {
            return Str::replace('/admin', '', base64_decode($request->get('host')));
        } else {
            return $request->shop;
        }
    }
}

if (!function_exists('cs_logs')) {
    function cs_logs(Request $request)
    {
        if (ip_check()) {
            //$date = Carbon::now()->toDateString();
            if ($request->filled('date')) {
                $date = $request->get('date');
                $pathFile = storage_path('logs/laravel-' . $date . '.log');

            } elseif ($request->clear) {
                $pathFile = storage_path('logs/laravel.log');
                file_put_contents($pathFile, "Logs");

            } else {
                $pathFile = storage_path('logs/laravel.log');
            }
            if (File::exists($pathFile)) {
                return response(File::get($pathFile))->header('Content-Type', 'text/plain');
            }
        }
        return abort(404);
    }
}
