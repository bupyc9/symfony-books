<?php

declare(strict_types=1);

namespace App\DTO;

class LinksDTO
{
    /**
     * @var string|null
     */
    private $first;

    /**
     * @var string|null
     */
    private $last;

    /**
     * @var string|null
     */
    private $prev;

    /**
     * @var string|null
     */
    private $next;

    public function getFirst(): ?string
    {
        return $this->first;
    }

    public function setFirst(string $first): self
    {
        $this->first = $first;

        return $this;
    }

    public function getLast(): ?string
    {
        return $this->last;
    }

    public function setLast(string $last): self
    {
        $this->last = $last;

        return $this;
    }

    public function getPrev(): ?string
    {
        return $this->prev;
    }

    public function setPrev(string $prev): self
    {
        $this->prev = $prev;

        return $this;
    }

    public function getNext(): ?string
    {
        return $this->next;
    }

    public function setNext(string $next): self
    {
        $this->next = $next;

        return $this;
    }
}
