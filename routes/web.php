<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;

Route::get('/', [IndexController::class,'index']);
Route::get('/t/{key}', [IndexController::class,'t']);
Route::get('/q', [IndexController::class,'q']);
Route::get('/z/{key}', [IndexController::class,'z']);
Route::get('/h/{key}', [IndexController::class,'h']);
//Route::get('/', function (){
//    dd(opcache_get_status());
//    return view('welcome');
//});
