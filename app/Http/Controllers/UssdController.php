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
        $sessionId = $_REQUEST["session_id"];
        $serviceCode = $_REQUEST["service_code"];
        $phoneNumber = $_REQUEST["mobile_number"];
        $text = trim($_REQUEST["message"]);

        $data = ['mobile_number' => $phoneNumber, 'text' => $text, 'service_code' => $serviceCode, 'session_id' => $sessionId];

        $payload = new Payload();
        $payload->data = \GuzzleHttp\json_encode($data);
        $payload->save();

        $exp_service_code = explode("*",$serviceCode);

        if(count($exp_service_code) > 3){
            $amount = $exp_service_code[3];
            self::sendResponse('Please wait for mpesa pin to pay kshs.'.$amount,3);
        }else{

            self::sendResponse('Please enter *989*100*Amount#',3);
        }

    }

    public function sendResponse($response,$type=1,$user=null)
    {

        if ($type == 1) {
            $output = "CON ";
        } elseif($type == 2) {
            $output = "CON ";
        }else{
            $output = "END ";
        }

        $output .= $response;
        header('Content-type: text/plain');
        echo $output;
        exit;
    }
}
