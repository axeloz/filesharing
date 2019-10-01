<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;


class WebController extends Controller
{
    function homepage(Request $request)
    {

        if (Upload::canUpload($request->ip()) !== true) {
            return view('cannotupload', [
                'u' => $request->get('u')
            ]);
        } else {
            return redirect()->route('upload.create');
        }

    }
}