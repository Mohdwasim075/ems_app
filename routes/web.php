<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginUserController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TicketController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

     Route::get('/', function () {  return view('attendee.home');})->name('attendee.home');


    Route::get('/events', [EventController::class, 'index'])
    ->name('events');
   ;

    

Route::middleware(['auth:sanctum','attendee'])->group(function () {

    //  Route::get('/profile', function(){ 
    //         if (Gate::allows('isAdmin', Auth::user())) {
    //             return view('attendee.profile');
    //         }else{
    //         abort(403);
    //      }
    Route::get('/profile', function(){ return view('attendee.profile');
    })->name('profileview');

    Route::get('/myevents', function(){
        return view('attendee.myevents');
    });

    
});
Route::middleware(['auth:sanctum','admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'Dashboard'])->name('admin.dashboard');

    Route::get('/admin/categories', [AdminController::class, 'category'])->name('admin.categories');

    Route::get('/admin/events', function(){ 
        return view('admin.events'); })->name('admin.events');

    Route::get('/admin/bookings', function(){
        return view('admin.bookings'); 
        })->name('admin.bookings');

    
    // Route::get('/admin/events', [AdminController::class, 'index'])->name('admin.events');
 // Route::get('/admin/bookings', [AdminController::class, 'getBookings'])->name('admin.bookings');
    Route::get('/admin/users', function(){
        return view('admin.users');
    })->name('admin.users');
});






Route::get('/login',function(){
    return view('auth.login');
});
  Route::post('/login', [LoginUserController::class, 'login'])
    ->name('login');


Route::get('/register', [RegisterUserController::class,'index']);
Route::post('/register', [RegisterUserController::class, 'store']);

Route::get('/forgot-password', function () {
    return view('auth.forgotpassword');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', [
    PasswordResetController::class,
    'sendResetLink'
])->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.resetpassword', [
        'token' => $token,
        'email' => request('email'),
    ]);
})->name('password.reset');

Route::post('/reset-password', [
    PasswordResetController::class,
    'resetPassword'
])->name('password.update');


// Route::get('/register', [RegisterUserController::class,'create']);
Route::get('/get/bookings',[TicketController::class, 'getBookings']);

Route::get('/lang/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'es', 'ar'])) {
        Session::put('locale', $lang);
    }
    return redirect()->back();
})->name('lang.switch');



   



//Route::get('/post/detail/{id}', [PostController::class, 'index'])->where('id', '[0-9]+');