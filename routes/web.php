<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController as B;
use App\Http\Controllers\AccountController as A;
Route::middleware(\App\Http\Middleware\BudgetSecurityHeaders::class)->group(function(){
    Route::get('/', [B::class,'home'])->name('home');
    Route::get('/login', [B::class,'home'])->name('login');
    Route::middleware('guest')->group(function(){
        Route::post('/login',[B::class,'login'])->middleware('throttle:5,1');
        Route::post('/register',[B::class,'register'])->middleware('throttle:5,1');
        Route::get('/forgot-password',[A::class,'forgot'])->name('password.request');
        Route::post('/forgot-password',[A::class,'sendReset'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password/{token}',[A::class,'resetForm'])->name('password.reset');
        Route::post('/reset-password',[A::class,'reset'])->middleware('throttle:5,1')->name('password.update');
    });
    Route::middleware(['auth','auth.session','throttle:120,1'])->group(function(){
        Route::post('/logout',[B::class,'logout']);
        Route::get('/email/verify',[A::class,'notice'])->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}',[A::class,'verify'])->middleware(['signed','throttle:6,1'])->name('verification.verify');
        Route::post('/email/verification-notification',[A::class,'resend'])->middleware('throttle:3,1')->name('verification.send');
        Route::middleware('verified')->group(function(){
            Route::post('/family',[B::class,'family']);
            Route::post('/invite',[B::class,'invite'])->middleware('throttle:10,1');
            Route::post('/join',[B::class,'join'])->middleware('throttle:10,1');
            Route::post('/transactions',[B::class,'store']);
            Route::post('/transactions/{id}',[B::class,'update'])->whereNumber('id');
            Route::delete('/transactions/{id}',[B::class,'destroy'])->whereNumber('id');
            Route::post('/budget',[B::class,'budget']);
            Route::get('/export',[B::class,'export']);
            Route::post('/import',[B::class,'import']);
        });
    });
});
