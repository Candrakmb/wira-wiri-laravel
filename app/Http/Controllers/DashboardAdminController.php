<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public $data = [
        'title' => 'dashboard',
        'modul' => 'dashboardAdmin',
    ];

    public function index() {
        return view($this->data['modul'], $this->data);
    }
}
