<?php

declare(strict_types=1);

namespace App\DTO;

use Swagger\Annotations as SWG;

class ErrorDTO
{
    /**
     * @var int
     *
     * @SWG\Property(type="integer", property="status")
     */
    private $status;

    /**
     * @var string
     *
     * @SWG\Property(type="string", property="message")
     */
    private $message;

    /**
     * @var array
     *
     * @SWG\Property(
     *     type="array",
     *     property="errors",
     *     @SWG\Items(type="string"), example={"message", "code": {"message 1", "message 2"}},
     * )
     */
    private $errors = [];

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

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
