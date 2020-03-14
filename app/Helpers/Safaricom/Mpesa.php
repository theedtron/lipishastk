<?php

namespace App\Helpers\Safaricom;

use App\ApiLog;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class Mpesa
{
    /**
     * Generates token for Safaricom Daraja Requests
     *
     * @return mixed
     */
    public static function generateToken()
    {
        $client = new Client();
        $baseUrl = env('SAFARICOM_BASE_URL');
        $credentials = base64_encode(env('SAFARICOM_KEY').':'.env('SAFARICOM_SECRET'));

        try {
            $response = $client->get($baseUrl.'oauth/v1/generate?grant_type=client_credentials', [
                'headers' => [
                    'Authorization' => 'Basic '.$credentials,
                    'Content-Type' => 'application/json',
                ]
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (BadResponseException $exception)
        {
            return json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }
    }

    /**
     * Performs a 'GET' request to Safaricom Daraja
     *
     * @param $endpoint
     * @return mixed
     */
    public static function get($endpoint)
    {
        $client = new Client();
        $baseUrl = env('SAFARICOM_BASE_URL');
        $token = Settings::get('mpesa-api.token');

        try {
            $response = $client->get($baseUrl.$endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ]
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (BadResponseException $exception)
        {
            return json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }
    }

    /**
     * Performs a 'POST' request to Safaricom Daraja
     *
     * @param $endpoint
     * @param $requestBody
     * @return mixed
     */
    public static function post($endpoint, $requestBody)
    {
        $client = new Client();
        $baseUrl = env('SAFARICOM_BASE_URL');
        $token = Settings::get('mpesa-api.token');

        try {
            $response = $client->post($baseUrl.$endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (BadResponseException $exception)
        {
            return json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }
    }

    /**
     * Performs an STK Push request
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function requestSTKPush($data)
    {
        try {
            $shortCode = env('SAFARICOM_PAYBILL');
            $passKey = env('SAFARICOM_PASS');
            $callbackUrl = 'http://37.139.17.247/mpesa/receiver';
//            $callbackUrl = url('http://stuff.com/mpesa/receive/stk/push/response');
            $time = Carbon::now()->format('YmdHis');

            $requestBody = [
                'BusinessShortCode' => $shortCode,
                'Password' => base64_encode($shortCode.$passKey.$time),
                'Timestamp' => $time,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $data['amount'],
                'PartyA' => $data['phone'],
                'PartyB' => $shortCode,
                'PhoneNumber' => $data['phone'],
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $data['account'],
                'TransactionDesc' => $data['description']
            ];

            $response = Mpesa::post('mpesa/stkpush/v1/processrequest', $requestBody);

            return response()->json(['status' => 'success', 'data' => $response]);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], $exception->getCode());
        }
    }
    /**
     * Performs a B2B request
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function requestB2B($data)
    {
        try {
            $shortCode = env('SAFARICOM_PAYBILL');
            $passKey = env('SAFARICOM_PASS');
            $callbackUrl = 'http://37.139.17.247/mpesa/receiver';
//            $callbackUrl = url('http://stuff.com/mpesa/receive/stk/push/response');
            $time = Carbon::now()->format('YmdHis');

            $requestBody = [
                'BusinessShortCode' => $shortCode,
                'Password' => base64_encode($shortCode.$passKey.$time),
                'Timestamp' => $time,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $data['amount'],
                'PartyA' => $data['phone'],
                'PartyB' => $shortCode,
                'PhoneNumber' => $data['phone'],
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $data['account'],
                'TransactionDesc' => $data['description']
            ];

            $response = Mpesa::post('mpesa/stkpush/v1/processrequest', $requestBody);

            return response()->json(['status' => 'success', 'data' => $response]);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], $exception->getCode());
        }
    }

    public static function settleRevenue()
    {
        $rand = rand(123456, 654321);
        $spId = rand(123456, 654321);
        $originId = $spId.'_amka_'.$rand;

        $body = [
            'api_key' => 'nHAUXTyFn3nMNca3',
            'OriginatorConversationID' => $originId,
        ];

        $url = env('revenue_settlement_endpoint');
        $client = new Client(['verify' => false]);

        try {
//            Loan::query()->where('mifos_loan_id', '=', $loanId)->update(['conversation_id' => $originId]);

            $data = $client->post($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($body)
                ]
            );

            $response = $data->getBody()->getContents();
        } catch (BadResponseException $exception) {
            $response = json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }

        ApiLog::query()->create([
            'request_url' => $url,
            'request_type' => 'POST',
            'request_body' => json_encode($body),
            'response_body' => $response
        ]);
    }

    public static function c2bTob2bTransfer($amount)
    {
        $rand = rand(123456, 654321);
        $spId = rand(123456, 654321);
        $originId = $spId.'_amka_'.$rand;

        $body = [
            'api_key' => 'nHAUXTyFn3nMNca3',
            'OriginatorConversationID' => $originId,
            'command_id' => "BusinessToBusinessTransfer",
            'primary_party' => env('PAYMENT_PAYBILL'),
            'receiver_party' => env('B2C_Paybill'),
            'amount' => $amount,
        ];

        $url = env('b2b_endpoint');
        $client = new Client(['verify' => false]);

        try {
//            Loan::query()->where('mifos_loan_id', '=', $loanId)->update(['conversation_id' => $originId]);

            $data = $client->post($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($body)
                ]
            );

            $response = $data->getBody()->getContents();
        } catch (BadResponseException $exception) {
            $response = json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }

        ApiLog::query()->create([
            'request_url' => $url,
            'request_type' => 'POST',
            'request_body' => json_encode($body),
            'response_body' => $response
        ]);
    }

    public static function mmfToUtilityTransfer($amount)
    {
        $rand = rand(123456, 654321);
        $spId = rand(123456, 654321);
        $originId = $spId.'_amka_'.$rand;

        $body = [
            'api_key' => 'nHAUXTyFn3nMNca3',
            'OriginatorConversationID' => $originId,
            'command_id' => "BusinessTransferFromMMFToUtility",
            'primary_party' => env('B2C_Paybill'),
            'receiver_party' => env('B2C_Paybill'),
            'amount' => $amount,
        ];

        $url = env('b2b_endpoint');
        $client = new Client(['verify' => false]);

        try {
//            Loan::query()->where('mifos_loan_id', '=', $loanId)->update(['conversation_id' => $originId]);

            $data = $client->post($url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($body)
                ]
            );

            $response = $data->getBody()->getContents();
        } catch (BadResponseException $exception) {
            $response = json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }

        ApiLog::query()->create([
            'request_url' => $url,
            'request_type' => 'POST',
            'request_body' => json_encode($body),
            'response_body' => $response
        ]);
    }
}
