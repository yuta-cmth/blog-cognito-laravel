<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CognitoController;
use App\Models\CognitoUser;

Route::get('/', function (Request $request) {
    $username = $request->session()->get('username');
    $cu = CognitoUser::find($username);
    $user = [];
    if ($username && !empty ($cu?->refresh_token)) {
        $user['username'] = $username;
    }

    // Not authenticated, redirect to Cognito hosted UI.

    return view('home', ['user' => $user]);
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
    $redirect_uri = route('cognito.logout-cb');
    $hosted_ui_uri = config('aws.cognito.hosted_ui_domain');
    $cognito_hosted_ui_redirect_url = "https://{$hosted_ui_uri}/logout?client_id={$client_id}&logout_uri={$redirect_uri}";
    return redirect($cognito_hosted_ui_redirect_url);
})->name('logout');

Route::group(['prefix' => 'cognito', 'as' => 'cognito.'], function () {
    Route::get('/login-cb', [CognitoController::class, 'loginCb'])->name('login-cb');
    Route::get('/logout-cb', [CognitoController::class, 'logoutCb'])->name('logout-cb');
});
