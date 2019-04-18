<?php

declare(strict_types=1);

namespace App\DTO;

use Swagger\Annotations as SWG;

class LinksDTO
{
    /**
     * @var string|null
     *
     * @SWG\Property(type="string", property="first", example="/link?page=1&count=20")
     */
    private $first;

    /**
     * @var string|null
     *
     * @SWG\Property(type="string", property="last", example="/link?page=5&count=20")
     */
    private $last;

    /**
     * @var string|null
     *
     * @SWG\Property(type="string", property="prev", example="/link?page=1&count=20")
     */
    private $prev;

    /**
     * @var string|null
     *
     * @SWG\Property(type="string", property="next", example="/link?page=2&count=20")
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
