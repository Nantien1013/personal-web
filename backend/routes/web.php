<?php

use App\Livewire\Actions\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::view('/resume', 'resume')->name('resume');
Route::get('/collection', \App\Livewire\CollectionIndex::class)->name('collection');
Route::get('/vocabulary', \App\Livewire\VocabularyIndex::class)->name('vocabulary');

Route::post('/logout', function (Request $request, Logout $logout) {
    $logout();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
