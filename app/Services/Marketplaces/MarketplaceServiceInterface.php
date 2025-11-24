<?php

namespace App\Services\Marketplaces;

use App\Models\PhotoBatch;
use App\Models\ProductListing;

interface MarketplaceServiceInterface
{
    /**
     * Get platform identifier
     */
    public function getPlatform(): string;

    /**
     * Get human-readable platform name
     */
    public function getPlatformName(): string;

    /**
     * Check if the service is properly configured
     */
    public function isConfigured(): bool;

    /**
     * Publish a product to the marketplace
     *
     * @param PhotoBatch $batch The product batch to publish
     * @param array $options Additional options (price override, etc.)
     * @return ListingResult
     */
    public function publish(PhotoBatch $batch, array $options = []): ListingResult;

    /**
     * Update an existing listing
     *
     * @param ProductListing $listing The listing to update
     * @param array $data Data to update
     * @return ListingResult
     */
    public function update(ProductListing $listing, array $data = []): ListingResult;

    /**
     * Delete/unpublish a listing from the marketplace
     *
     * @param ProductListing $listing
     * @return ListingResult
     */
    public function delete(ProductListing $listing): ListingResult;

    /**
     * Check if a listing still exists and get its current status
     *
     * @param ProductListing $listing
     * @return ListingResult
     */
    public function checkStatus(ProductListing $listing): ListingResult;
}
