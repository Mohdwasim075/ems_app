<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoginUserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Usercontroller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/logout', [
    LoginUserController::class,
    'destroy',
])->name('logout');

// public routes
Route::get('/events/featured', [EventController::class, 'featured']);
Route::get('/events/upcoming', [EventController::class, 'upcoming']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Attendee authenticated routes
Route::middleware(['auth:sanctum', 'attendee'])->group(function () {

    Route::get('/myevents', [Usercontroller::class, 'getmyEvents'])->name('myevents');
    Route::get('/profile', [Usercontroller::class, 'getProfile'])->name('myprofile');
    Route::patch('/profile/update', [Usercontroller::class, 'updateProfile'])->name('updateProfile');
    Route::patch('/password/update', [Usercontroller::class, 'updatePassword'])->name('updatePassword');

    Route::post('/event/register', [BookingController::class, 'store']);
    //
});

// Admin authenticated routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard/report', [AdminController::class, 'getReport']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/categories/list', [CategoryController::class, 'categoryList']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    Route::post('/category/create', [CategoryController::class, 'createCategory']);
    Route::post('/category/update/{id}', [CategoryController::class, 'updateCategory']);
    Route::post('/category/delete/{id}', [CategoryController::class, 'deleteCategory']);

    // Events

    Route::get('/events', [EventController::class, 'getEvents']);
    Route::post('/events/create', [EventController::class, 'createEvent']);
    Route::get('/events/{id}', [EventController::class, 'getEvent']);
    Route::patch('/events/update/{id}', [EventController::class, 'updateEvent']);
    Route::post('/events/delete/{id}', [EventController::class, 'deleteEvent']);

    // Bookings

    Route::get('/bookings', [BookingController::class, 'getbookings']);
    Route::get('/bookings/get/{id}', [BookingController::class, 'getbooking']);
    Route::post('/booking/delete/{id}', [BookingController::class, 'deletebooking']);

    // Users

    Route::get('/users', [Usercontroller::class, 'getusers']);
    Route::get('/user/roles', [Usercontroller::class, 'getRoles']);
    Route::post('/user/create', [Usercontroller::class, 'createUser']);

    // Wildcard routes 
    Route::get('/user/{id}', [Usercontroller::class, 'getuser']);
    Route::post('/user/update/{id}', [Usercontroller::class, 'updateUser']);
    Route::post('/user/delete/{id}', [Usercontroller::class, 'deleteuser']);

});
