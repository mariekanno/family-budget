<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController as B;
Route::get('/', [B::class,'home'])->name('home');
Route::get('/login', [B::class,'home'])->name('login');
Route::post('/login',[B::class,'login'])->middleware(['guest','throttle:5,1']);
Route::post('/register',[B::class,'register'])->middleware(['guest','throttle:5,1']);
Route::middleware('auth')->group(function(){
    Route::post('/logout',[B::class,'logout']);
    Route::post('/family',[B::class,'family']);
    Route::post('/invite',[B::class,'invite'])->middleware('throttle:10,1');
    Route::post('/join',[B::class,'join'])->middleware('throttle:10,1');
    Route::post('/transactions',[B::class,'store']);
    Route::post('/transactions/{id}',[B::class,'update']);
    Route::delete('/transactions/{id}',[B::class,'destroy']);
    Route::post('/budget',[B::class,'budget']);
    Route::get('/export',[B::class,'export']);
    Route::post('/import',[B::class,'import']);
});
