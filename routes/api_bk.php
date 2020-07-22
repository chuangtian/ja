<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('send/erc20', 'ApiController@send');
Route::post('wsend/erc20', 'ApiController@wsend');
Route::post('csend/erc20', 'ApiController@csend');
Route::post('getBalance', 'ApiController@getBalance2');
Route::get('updateBalance', 'ApiController@updateBalance');
Route::post('test', 'ApiController@test');

Route::get('receiveERC', 'JobController@receiveERC');
Route::get('jobtest', 'JobController@test');
Route::get('getFee', 'JobController@getFee');
Route::get('updateBlock', 'JobController@updateBlock');
Route::get('findERC20TransactionByHash', 'JobController@findERC20TransactionByHash');
Route::get('dow/test', 'DowController@test');
Route::get('dow/download', 'DowController@download');
