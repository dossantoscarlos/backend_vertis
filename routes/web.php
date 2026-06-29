<?php

use App\Livewire\Auth\LoginForm;
use App\Livewire\Support\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('support.dashboard');
});

Route::get('/home', function () {
    return redirect()->route('support.dashboard');
})->name('home');

Route::get('/login/supports', LoginForm::class)
    ->middleware('guest')
    ->name('login');

Route::get('/login', function () {
    return redirect()->route('login');
});

Route::get('/support/phpinfo', function () {
    return view('livewire.support.phpinfo');
})
    ->middleware('auth')
    ->name('support.phpinfo');

Route::get('/support', Dashboard::class)
    ->middleware('auth')
    ->name('support.dashboard');
