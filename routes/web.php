<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', [UserController::class, 'index'])->name('index');
Route::post('init_table', [UserController::class, 'init_table'])->name('init_table');
Route::post('create', [UserController::class, 'create'])->name('create');
Route::post('read', [UserController::class, 'read'])->name('read');
Route::put('update', [UserController::class, 'update'])->name('update');
Route::delete('delete', [UserController::class, 'delete'])->name('delete');

