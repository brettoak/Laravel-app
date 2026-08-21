<?php

use App\Http\Controllers\Conversation;
use App\Http\Controllers\ReverbMultipleJobs;
use App\Http\Controllers\ReverbSingleJob;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

Route::redirect('/register', '/login');

Route::redirect('/', '/login');

Route::redirect('/admin/login', '/login');

Route::group(['prefix' => 'webauthn'], function () {
    Route::post('register/options', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'options'])->name('webauthn.register.options');
    Route::post('register', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'register'])->name('webauthn.register');
    Route::post('login/options', [\App\Http\Controllers\WebAuthn\WebAuthnLoginController::class, 'options'])->name('webauthn.login.options');
    Route::post('login', [\App\Http\Controllers\WebAuthn\WebAuthnLoginController::class, 'login'])->name('webauthn.login');
});

Route::get('/test2', static function () {
    return response()->json([
        'message' => 'test2',
        'code' => 200,
        'data' => [
            'name' => 'test2',
            'age' => 20,
        ],
    ]);
});

Route::get('/login/github', [SocialAuthController::class, 'redirectToGithub'])->name('login.github');
Route::get('/login/github/callback', [SocialAuthController::class, 'handleGithubCallback']);

Route::get('/login/google', [SocialAuthController::class, 'redirectToGoogle'])->name        ('login.google');
Route::get('/login/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::get('/service', [ServiceController::class, 'index']);

    Route::get('/reverb/single/job', [ReverbSingleJob::class, 'index']);

    Route::get('/reverb/multiple/jobs', [ReverbMultipleJobs::class, 'index']);

    Route::get('/conversation', [Conversation::class, 'index']);

    Route::view('/tickets', 'tickets.index')->name('tickets.index');
});
