<?php

namespace App\Http\Controllers;

use App\Payload;
use App\TransactionLog;
use Carbon\Carbon;
use GuzzleHttp\Client;
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

        $data = ['mobile_number' => $phoneNumber, 'message' => $text, 'service_code' => $serviceCode, 'session_id' => $sessionId];

        $payload = new Payload();
        $payload->data = \GuzzleHttp\json_encode($data);
        $payload->save();

//        $exp_service_code = explode("*",$serviceCode);

        if(!empty($text)){
            $amount = $text;
            self::stkPush($phoneNumber,$amount);
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

    public function stkPush($phone,$amount){

        $client = new Client();
        $mat_invoice ="MATINV".rand(0,999999);
        $res = $client->post('https://api.lipisha.com/v2/request_money', [
            'form_params' => [
                "api_key"=>env('LIPISHA_API_KEY'),
                "api_signature"=>env('LIPISHA_API_SIGNATURE'),
                "account_number"=>"30439",
                "mobile_number"=>"254".substr($phone,-9),
                "method"=>"Paybill (M-Pesa)",
                "amount"=>$amount,
                "currency"=>"KES",
                "reference"=>$mat_invoice
            ]
        ]);

        $result = $res->getBody()->getContents();

        $format_res = json_decode($result,true);
        if ($format_res['status']['status'] = "SUCCESS"){
            $trx = new TransactionLog();
            $trx->reference_no =$format_res['content']['reference'];
            $trx->phone =$format_res['content']['mobile_number'];
            $trx->amount =$format_res['content']['amount'];
            $trx->transaction_type = 1;
            $trx->save();

            $tout_sms = new NotifyController();
            $tout_sms->sendSms('0715576111','Received Ksh.'.$amount.' at '.Carbon::now()->format('d-m-Y H:i:s').' Receipt Number: '.$mat_invoice);

            $owner_sms = new NotifyController();
            $owner_sms->sendSms('0717277588','KBP 170J Received Ksh.'.$amount.' at '.Carbon::now()->format('d-m-Y H:i:s').' Receipt Number: '.$mat_invoice);
        }
    }

    public function lipishaReceiver(Request $request){
        $payload = new Payload();
        $payload->data = \GuzzleHttp\json_encode($request->all());
        $payload->save();
    }
}
