<?php

namespace App\Http\Controllers;

use App\Payload;
use Illuminate\Http\Request;

class UssdController extends Controller
{
    public function index(){
        error_reporting(0);
        header('Content-type: text/plain');
        set_time_limit(100);


        //get inputs
        $sessionId = $_REQUEST["sessionId"];
        $serviceCode = $_REQUEST["serviceCode"];
        $phoneNumber = $_REQUEST["phoneNumber"];
        $text = trim($_REQUEST["text"]);

        $data = ['phone' => $phoneNumber, 'text' => $text, 'service_code' => $serviceCode, 'session_id' => $sessionId];

        $payload = new Payload();
        $payload->data = \GuzzleHttp\json_encode($_REQUEST);
        $payload->save();
    }
}
