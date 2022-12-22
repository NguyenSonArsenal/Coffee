<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Language;
use Artisan;
use Session;

class CacheController extends Controller
{
  public function __construct()
  {
      $this->middleware('setlang');
  }
    public function clear() {
      Artisan::call('cache:clear');
      Artisan::call('config:clear');
      Artisan::call('route:clear');
      Artisan::call('view:clear');

      Session::flash('success', 'Cache, route, view, config cleared successfully!');
      return back();
    }
}
