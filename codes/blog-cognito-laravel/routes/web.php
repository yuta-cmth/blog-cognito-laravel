<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CognitoController;
use App\Models\CognitoUser;

Route::get('/', function (Request $request) {
    $username = $request->session()->get('username');
    $cu = CognitoUser::find($username);
    if ($username && !empty ($cu?->refresh_token)) {
        return "Hello, {$username}!";
    }

    // Not authenticated, redirect to Cognito hosted UI.

    return redirect()->route('login');
})->name('home');

Route::get('/login', function (Request $reqeust) {
    $client_id = config('aws.cognito.client_id');
    $redirect_uri = route('cognito.login-cb');
    $hosted_ui_uri = config('aws.cognito.hosted_ui_domain');
    $cognito_hosted_ui_redirect_url = "https://{$hosted_ui_uri}/login?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}";
    return redirect($cognito_hosted_ui_redirect_url);
})->name('login');

Route::get('/logout', function (Request $reqeust) {
    $client_id = config('aws.cognito.client_id');
    $redirect_uri = route('cognito.login-cb');
    $hosted_ui_uri = config('aws.cognito.hosted_ui_domain');
    $cognito_hosted_ui_redirect_url = "https://{$hosted_ui_uri}/logout?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}";
    return redirect($cognito_hosted_ui_redirect_url);
});

Route::group(['prefix' => 'cognito', 'as' => 'cognito.'], function () {
    Route::get('/login-cb', [CognitoController::class, 'loginCb'])->name('login-cb');
    Route::get('/logout-cb', [CognitoController::class, 'logoutCb'])->name('logout-cb');
});
