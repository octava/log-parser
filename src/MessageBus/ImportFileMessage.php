<?php

namespace App\MessageBus;

class ImportFileMessage
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var bool
     */
    protected $truncate;

    public function __construct(string $filename, string $providerName, bool $truncate)
    {
        $this->filename = $filename;
        $this->providerName = $providerName;
        $this->truncate = $truncate;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @return bool
     */
    public function isTruncate(): bool
    {
        return $this->truncate;
    }
}
