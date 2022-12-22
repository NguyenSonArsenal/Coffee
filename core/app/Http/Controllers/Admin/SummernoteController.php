<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Language;

class SummernoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('setlang');
    }
    public function upload(Request $request) {
        $img = $request->file('image');
        $filename = uniqid() . '.' . $img->getClientOriginalExtension();
        $img->move('assets/front/img/summernote/', $filename);

        return url('/') . "/assets/front/img/summernote/" . $filename;
    }
}
