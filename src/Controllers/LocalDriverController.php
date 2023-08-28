<?php

namespace Celysium\Payment\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocalDriverController extends Controller
{
    public function pay(Request $request)
    {
        return view('payment::local-pay', $request->all());
    }

    public function callback(Request $request)
    {
        $status = (bool)$request->input('status');

        cache(['status' => $status], now()->addMinutes(10));

        return redirect(cache('callbackUrl'))->with($request->all());
    }
}
