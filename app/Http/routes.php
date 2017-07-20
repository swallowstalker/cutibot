<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', 'ExampleController@index');
$app->get('/manual/updates', 'ExampleController@updates');

$app->get('/update', 'RequestReceiver@update');
$app->post('/update', 'RequestReceiver@update');

$app->get('/webhook/setting', 'ConfigController@setWebhook');
$app->get('/webhook/info', 'ConfigController@getWebhookInfo');

//$app->get('/import/holiday', 'ConfigController@importHolidayData');
