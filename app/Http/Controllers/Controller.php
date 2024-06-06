<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function simpleReturn($status = '', $response = array(), $code = 200)
    {
        $out['status'] = $status;
        $out['response'] = is_string($response) ? array('msg' => $response) : $response;
        $out['code'] = $code;
        return response()->json($out, $code);
    }
}
