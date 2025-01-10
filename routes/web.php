<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventUserAttendanceController;
use App\Http\Controllers\EventUserRequestController;
use App\Http\Controllers\EventUserInvitationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('events', EventController::class);

Route::resource('attendance', EventUserAttendanceController::class, [
    'only' => ['store', 'destroy'],
]);

Route::resource('requests', EventUserRequestController::class, [
    'only' => ['store'],
]);
Route::delete('/requests/{request}/{decision}', [EventUserRequestController::class, 'destroy'])
    ->name('requests.destroy');

Route::resource('invitations', EventUserInvitationController::class, [
    'only' => ['store'],
]);
Route::delete('/invitations/{invitation}/{decision}', [EventUserInvitationController::class, 'destroy'])
    ->name('invitations.destroy');

require __DIR__.'/auth.php';
