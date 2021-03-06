<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::any('ussdpro','UssdController@index');
Route::post('lipisha/receiver','UssdController@lipishaReceiver');
Route::get('lipisha/pusher/{phone}/{amount}/{extension}','UssdController@stkPush');
Route::get('lipisha/sms/{phone}/{message}','NotifyController@sendSms');
