<?php

use App\Http\Controllers\Admin\PhotoBulkUploadController;
use App\Http\Controllers\Admin\PhotoTagController;
use App\Http\Controllers\Archive\PhotoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MembershipController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SuggestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/verein/impressum', fn (PageController $pages) => $pages->show('impressum'))->name('impressum');
Route::get('/verein/datenschutz', fn (PageController $pages) => $pages->show('datenschutz'))->name('datenschutz');
Route::get('/verein/satzung', fn (PageController $pages) => $pages->show('satzung'))->name('satzung');

Route::get('/verein/aufnahmeantrag', [MembershipController::class, 'show'])->name('aufnahmeantrag');
Route::post('/verein/aufnahmeantrag', [MembershipController::class, 'store'])->name('aufnahmeantrag.store');

Route::get('/kontakt', [ContactController::class, 'show'])->name('kontakt');
Route::post('/kontakt', [ContactController::class, 'store'])->name('kontakt.store');

Route::prefix('archiv')->name('archive.')->group(function () {
    Route::get('/', [PhotoController::class, 'index'])->name('index');
    Route::get('/person/{person}', [PhotoController::class, 'person'])->name('person');
    Route::get('/{photo:slug}', [PhotoController::class, 'show'])->name('show');
    Route::post('/{photo:slug}/vorschlag', [PhotoController::class, 'suggestTag'])
        ->middleware('auth')
        ->name('suggest-tag');
});

Route::get('/dashboard', [PhotoController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/meine-vorschlaege', [SuggestionController::class, 'index'])->name('suggestions.index');
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::post('/photos/bulk-upload', [PhotoBulkUploadController::class, 'store'])->name('admin.photos.bulk-upload.store');
    Route::post('/photos/{photo}/tags', [PhotoTagController::class, 'store'])->name('admin.photos.tags.store');
    Route::put('/photos/{photo}/tags/{tag}', [PhotoTagController::class, 'update'])->name('admin.photos.tags.update');
    Route::delete('/photos/{photo}/tags/{tag}', [PhotoTagController::class, 'destroy'])->name('admin.photos.tags.destroy');
});

require __DIR__.'/auth.php';
