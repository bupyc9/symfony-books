<?php

declare(strict_types=1);

namespace App\DTO;

class CollectionMetaDTO
{
    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $lastPage;

    /**
     * @var int
     */
    private $count;

    public function __construct(int $currentPage, int $lastPage, int $count)
    {
        $this->currentPage = $currentPage;
        $this->lastPage = $lastPage;
        $this->count = $count;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function setLastPage(int $lastPage): self
    {
        $this->lastPage = $lastPage;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
