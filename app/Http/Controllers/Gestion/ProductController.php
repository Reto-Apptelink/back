<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() {
        return view('page.product.index');
    }

    public function createForm() {
        return view('page.product.create');
    }

    public function editForm() {
        return view('page.product.edit');
    }
}
