<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::apiResource('/notes', NoteController::class)->middleware('auth:sanctum')->only(['index', 'show', 'store']);

Route::post('/notes/{note}', [NoteController::class, 'update'])->middleware('auth:sanctum');

Route::patch('/notes', [NoteController::class, 'softDelete'])->middleware('auth:sanctum');

Route::put('/notes', [NoteController::class, 'pin'])->middleware('auth:sanctum');

Route::delete('/notes', [NoteController::class, 'destroy'])->middleware('auth:sanctum');

