<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\WebController;

// Home route
Route::get('/', function () {
    return view('welcome');
});

// Static pages
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/faqs', function () {
    return view('faqs');
})->name('faqs');

// Blog list with pagination
Route::get('/blog', [WebController::class, 'index'])->name('posts.index');

// Dynamic posts route based on slug
Route::get('/posts/{slug}', [WebController::class, 'show'])->name('posts.show');

Route::get('/mail', function () {
    Mail::raw('This is a test email from Laravel using Gmail SMTP.', function ($message) {
        $message->to('ganiyumubarak12@gmail.com')
                ->subject('Test Email');
    });

    return 'Mail sent!';
});
