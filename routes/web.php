<?php

use App\Livewire\Auth\LoginForm;
use App\Livewire\Support\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/login', LoginForm::class)
    ->middleware('guest')
    ->name('login');

Route::get('/login/supports', function () {
    return redirect()->route('login');
});

Route::get('/phpinfo', function () {
    return view('livewire.support.phpinfo');
})
    ->middleware('auth')
    ->name('phpinfo');

Route::get('/dashboard', Dashboard::class)
    ->middleware('auth')
    ->name('dashboard');

Route::get('/modulos', function () {
    return redirect()->route('dashboard');
});

Route::get('/support', function () {
    return redirect()->route('dashboard');
});
