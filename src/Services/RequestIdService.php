<?php

namespace Laikmosh\Plog\Services;

use Illuminate\Support\Str;

class RequestIdService
{
    protected $requestId;

    public function generateRequestId(): string
    {
        $this->requestId = Str::uuid()->toString();
        return $this->requestId;
    }

    public function getRequestId(): ?string
    {
        if (!$this->requestId) {
            $this->generateRequestId();
        }
        return $this->requestId;
    }

    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function hasRequestId(): bool
    {
        return !empty($this->requestId);
    }
}