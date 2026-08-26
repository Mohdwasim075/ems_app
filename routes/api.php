<?php

use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/events/featured', [EventController::class, 'featured']);
Route::get('/events/upcoming', [EventController::class, 'upcoming']);


Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
    

// Route::get('/events', function(){
//     return response()->json([
//         'message' =>'Events API'
//     ]);
// });