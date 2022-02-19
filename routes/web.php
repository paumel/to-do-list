<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ToDoController;
use App\Http\Controllers\ToggleToDoFinishedController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->middleware(['guest']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('to-dos', ToDoController::class)->except(['show']);
    Route::put('to-dos/{to_do}/toggle', ToggleToDoFinishedController::class)->name('to-dos.toggle');

    Route::resource('categories', CategoryController::class)->except(['show']);
});

require __DIR__.'/auth.php';
