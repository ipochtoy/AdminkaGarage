<?php

namespace App\Services\Marketplaces;

class ListingResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $externalId = null,
        public readonly ?string $externalUrl = null,
        public readonly ?string $error = null,
        public readonly array $platformData = [],
    ) {}

    public static function success(
        ?string $externalId = null,
        ?string $externalUrl = null,
        array $platformData = []
    ): self {
        return new self(
            success: true,
            externalId: $externalId,
            externalUrl: $externalUrl,
            platformData: $platformData,
        );
    }

    public static function failure(string $error, array $platformData = []): self
    {
        return new self(
            success: false,
            error: $error,
            platformData: $platformData,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'external_id' => $this->externalId,
            'external_url' => $this->externalUrl,
            'error' => $this->error,
            'platform_data' => $this->platformData,
        ];
    }
}
