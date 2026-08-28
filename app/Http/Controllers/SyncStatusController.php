<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SyncStatusController extends Controller
{
    /**
     * Display offline synchronization status.
     */
    public function index(): View
    {
        return view('sync-status.index');
    }
}