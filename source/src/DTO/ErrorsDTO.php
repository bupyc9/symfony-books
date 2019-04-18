<?php

declare(strict_types=1);

namespace App\DTO;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class ErrorsDTO
{
    /**
     * @var ErrorDTO
     *
     * @SWG\Property(ref=@Model(type=ErrorDTO::class), property="error")
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
