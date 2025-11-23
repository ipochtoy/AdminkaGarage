<?php

use App\Models\Product;

$product = Product::latest()->first();

if (!$product) {
    echo "No products found.\n";
    exit;
}

echo "Product ID: " . $product->id . "\n";
echo "Product Title: " . $product->title . "\n";

$photos = $product->photos;
echo "Photos Count: " . $photos->count() . "\n";

foreach ($photos as $photo) {
    echo "Photo Path: " . $photo->image_path . "\n";
}

echo "Image Paths Accessor: " . json_encode($product->image_paths) . "\n";
