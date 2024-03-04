<?php

namespace Parsing\Api;

use http\Exception\BadUrlException;

class OlxApi
{
    public ?array $cache = null;
    const string API_URL = "https://m.olx.ua/api/v1/offers/%s";

    /**
     * @param string $productId
     */
    public function __construct(private readonly string $productId)
    {
    }

    /**
     * @return array|null
     */
    private function getCache(): ?array
    {
        return $this->cache;
    }

    /**
     * @return bool
     */
    private function isCacheExists(): bool
    {
        return $this->cache !== null;
    }

    /**
     * @param $value
     * @return void
     */
    private function setCache($value): void
    {
        $this->cache = $value;
    }

    /**
     * @return string
     */
    private function getApiUrl(): string
    {
        return sprintf(
            self::API_URL,
            $this->productId
        );
    }

    private function initCache(): void
    {
        if (!$this->isCacheExists()) {
            $apiData = file_get_contents($this->getApiUrl());
            $this->setCache(json_decode($apiData, true));
        }
    }

    /**
     * @return int|null
     */
    public function getPrice(): ?int
    {
        $this->initCache();

        foreach ($this->getCache()['data']['params'] ?? [] as $param) {
            if ($param['type'] === 'price') {
                $price = $param['value']['value'] ?? null;
                return $price ? (int) $price : null;
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        $this->initCache();

        return $this->getCache()['data']['title'] ?? null;
    }
}