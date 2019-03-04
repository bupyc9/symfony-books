<?php

declare(strict_types=1);

namespace App\DTO;

class ErrorsDTO
{
    /**
     * @var ErrorDTO
     */
    private $error;

    public function __construct(ErrorDTO $error)
    {
        $this->error = $error;
    }

    public function getError(): ErrorDTO
    {
        return $this->error;
    }

    public function setError(ErrorDTO $error): self
    {
        $this->error = $error;

        return $this;
    }
}
