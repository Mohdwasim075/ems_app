<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\LoginUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Usercontroller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


    
Route::middleware('auth:sanctum')->post('/logout', [
    LoginUserController::class,
    'destroy'
])->name('logout');

 //public routes
    Route::get('/events/featured', [EventController::class, 'featured']);
    Route::get('/events/upcoming', [EventController::class, 'upcoming']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);



//Attendee authenticated routes
Route::middleware(['auth:sanctum','attendee'])->group(function () {

  
    Route::get('/myevents', [LoginUserController::class,"getmyEvents"] )->name('myevents');
    Route::get('/profile', [LoginUserController::class, 'getProfile'])->name('myprofile');
    Route::patch('/profile/update', [Usercontroller::class, 'updateProfile'])->name('updateProfile');
    Route::patch('/password/update', [Usercontroller::class, 'updatePassword'])->name('updatePassword');

    Route::post('/event/register',[TicketController::class, 'store']);
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
   
    Route::get('/events', [AdminController::class, 'getEvents']);
    Route::post('/events/create', [AdminController::class, 'createEvent']); // Keep static endpoints above {id}
    Route::get('/events/{id}', [AdminController::class, 'getEvent']);
    Route::patch('/events/update/{id}', [AdminController::class, 'updateEvent']);
    Route::post('/events/delete/{id}', [AdminController::class, 'deleteEvent']);

   
    // Bookings
  
    Route::get('/bookings', [AdminController::class, 'getbookings']);
    Route::get('/bookings/get/{id}', [AdminController::class, 'getbooking']);
    Route::post('/booking/delete/{id}', [AdminController::class, 'deletebooking']);

   
    // Users
  
    Route::get('/users', [AdminController::class, 'getusers']);
    Route::get('/user/roles', [AdminController::class, 'getRoles']); 
    
    // Wildcard routes go AFTER static routes
    Route::get('/user/{id}', [AdminController::class, 'getuser']);
    Route::post('/user/update/{id}', [AdminController::class, 'updateUser']);
    Route::post('/user/delete/{id}', [AdminController::class, 'deleteuser']);

    
   

   
});
