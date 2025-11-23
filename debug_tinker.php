$product = App\Models\Product::with('photos')->first();
if ($product) {
echo "Product ID: " . $product->id . "\n";
echo "Photos relation count: " . $product->photos->count() . "\n";
foreach ($product->photos as $photo) {
echo "Photo ID: " . $photo->id . "\n";
echo "Photo image_path: " . $photo->image_path . "\n";
echo "Photo product_id: " . $photo->product_id . "\n";
}
} else {
echo "No products found.\n";
}