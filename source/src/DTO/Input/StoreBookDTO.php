<?php

declare(strict_types=1);

namespace App\DTO\Input;

use App\Entity\Author;
use JMS\Serializer\Annotation as Serializer;

class StoreBookDTO
{
    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    private $name;

    /**
     * @var Author|null
     *
     * @Serializer\Type(Author::class)
     */
    private $author;

    /**
     * @var int|null
     *
     * @Serializer\Type("integer")
     */
    private $year;

    /**
     * @var int|null
     *
     * @Serializer\Type("integer")
     */
    private $pages;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(?int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }
}
