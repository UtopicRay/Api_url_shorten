<?php

namespace App\DTO;

use JsonSerializable;

class UrlDto implements JsonSerializable
{
private int $id;
private string $originalUrl;
private string $shortedUrl;
private \DateTimeImmutable $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    public function setOriginalUrl(string $originalUrl): void
    {
        $this->originalUrl = $originalUrl;
    }

    public function getShortedUrl(): string
    {
        return $this->shortedUrl;
    }

    public function setShortedUrl(string $shortedUrl): void
    {
        $this->shortedUrl = $shortedUrl;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}