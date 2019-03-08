<?php

declare(strict_types=1);

namespace App\DTO\Input;

use JMS\Serializer\Annotation as Serializer;

class StoreAuthorDTO
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $firstName;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $lastName;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $secondName = '';

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setSecondName(?string $secondName): self
    {
        $this->secondName = $secondName;

        return $this;
    }
}
