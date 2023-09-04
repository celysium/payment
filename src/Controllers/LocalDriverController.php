<?php

namespace Celysium\Payment\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LocalDriverController extends Controller
{
    /**
     * @return View
     */
    public function pay(): View
    {
        return view('payment::pay');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function callback(Request $request): View
    {
        $status = $request->input('status');

        cache(['status' => $status], now()->addMinutes(10));

        return view('payment::callback');
    }
}
