<?php

declare(strict_types=1);

namespace App\DTO;

class ErrorDTO
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    public function __construct(int $status, string $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
