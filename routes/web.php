<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\PostController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   
    // dd($user[1]->name);
    // $user = User::all();
    return view('attendee.home');
})->name('home');


Route::get('/events', [EventController::class, 'index'])
    ->name('events.index');


Route::get('/login', function(){
    return view('login');
});

//Route::get('/post/detail/{id}', [PostController::class, 'index'])->where('id', '[0-9]+');