<?php

namespace App\MessageBus;

use Symfony\Component\Console\Style\SymfonyStyle;

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

    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;

    public function __construct(string $filename, string $providerName, bool $truncate)
    {
        $this->filename = $filename;
        $this->providerName = $providerName;
        $this->truncate = $truncate;
    }

    /**
     * @return SymfonyStyle
     */
    public function getSymfonyStyle(): SymfonyStyle
    {
        return $this->symfonyStyle;
    }

    /**
     * @param SymfonyStyle $symfonyStyle
     * @return self
     */
    public function setSymfonyStyle($symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;

        return $this;
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
