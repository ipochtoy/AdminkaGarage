<?php

use App\Http\Controllers\ProductCardController;
use App\Models\PhotoBuffer;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/admin');
});

// API for bulk upload
Route::post('/api/upload-photo', function (Request $request) {
    $request->validate(['photo' => 'required|image|max:20480']);

    $path = $request->file('photo')->store('buffer/' . date('Y/m/d'), 'public');

    PhotoBuffer::create([
        'file_id' => uniqid('upload_'),
        'message_id' => 0,
        'chat_id' => 0,
        'image' => $path,
        'uploaded_at' => now(),
    ]);

    return response()->json(['success' => true, 'path' => $path]);
});

Route::get('/product-card/{photoBatch}', [ProductCardController::class, 'show'])->name('product-card');
Route::post('/product-card/{photoBatch}', [ProductCardController::class, 'update'])->name('product-card.update');
Route::post('/product-card/{photoBatch}/photo/{photoId}/main', [ProductCardController::class, 'setMainPhoto'])->name('product-card.set-main');
Route::delete('/product-card/{photoBatch}/photo/{photoId}', [ProductCardController::class, 'deletePhoto'])->name('product-card.delete-photo');
