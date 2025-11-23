<?php

namespace App\Http\Controllers;

use App\Models\PhotoBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductCardController extends Controller
{
    public function show(PhotoBatch $photoBatch)
    {
        $photoBatch->load(['photos.barcodes']);

        return view('product-card', [
            'card' => $photoBatch,
            'photos' => $photoBatch->photos()->orderBy('is_main', 'desc')->orderBy('order')->get(),
            'ggLabels' => $photoBatch->getGgLabels(),
            'allBarcodes' => $photoBatch->getAllBarcodes(),
        ]);
    }

    public function setMainPhoto(PhotoBatch $photoBatch, $photoId)
    {
        $photo = $photoBatch->photos()->findOrFail($photoId);
        $photo->update(['is_main' => true]);

        return back()->with('success', 'Главное фото обновлено');
    }

    public function deletePhoto(PhotoBatch $photoBatch, $photoId)
    {
        $photo = $photoBatch->photos()->findOrFail($photoId);

        if ($photo->image && Storage::disk('public')->exists($photo->image)) {
            Storage::disk('public')->delete($photo->image);
        }

        $photo->delete();

        return back()->with('success', 'Фото удалено');
    }

    public function update(Request $request, PhotoBatch $photoBatch)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'condition' => 'nullable|in:new,used,refurbished',
            'category' => 'nullable|string|max:200',
            'brand' => 'nullable|string|max:200',
            'size' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:200',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $photoBatch->update($validated);

        return back()->with('success', 'Данные сохранены');
    }
}
