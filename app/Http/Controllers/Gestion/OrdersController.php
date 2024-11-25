<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function ordersIndex(Request $request) {
        return view('page.order.index');
    }

    public function createForm(Request $request) {
        return view('page.order.create');
    }
}
