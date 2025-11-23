<?php

use App\Http\Controllers\ProductCardController;
use App\Http\Controllers\Api\VoiceCorrectionController;
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

// Product Card
Route::get('/product-card/{photoBatch}', [ProductCardController::class, 'show'])->name('product-card');

// Product Card API
Route::prefix('api/card/{photoBatch}')->group(function () {
    Route::post('/save', [ProductCardController::class, 'save']);
    Route::post('/generate-summary', [ProductCardController::class, 'generateSummary']);
    Route::post('/scan-barcodes', [ProductCardController::class, 'scanBarcodes']);
    Route::post('/search-ebay', [ProductCardController::class, 'searchEbay']);
    Route::post('/send-pochtoy', [ProductCardController::class, 'sendToPochtoy']);
    Route::post('/reorder-photos', [ProductCardController::class, 'reorderPhotos']);
    Route::post('/add-barcode', [ProductCardController::class, 'addBarcode']);
    Route::delete('/delete', [ProductCardController::class, 'deleteCard']);
});

// Photo API
Route::prefix('api/photo/{photo}')->group(function () {
    Route::post('/rotate', [ProductCardController::class, 'rotatePhoto']);
    Route::post('/set-main', [ProductCardController::class, 'setMainPhoto']);
    Route::delete('/delete', [ProductCardController::class, 'deletePhoto']);
});

// Voice Correction API
Route::post('/api/voice-correction', [VoiceCorrectionController::class, 'apply']);
