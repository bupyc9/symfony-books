<?php

declare(strict_types=1);

namespace App\DTO;

use Swagger\Annotations as SWG;

class CollectionMetaDTO
{
    /**
     * @var int
     *
     * @SWG\Property(type="integer", property="current_page")
     */
    private $currentPage;

    /**
     * @var int
     *
     * @SWG\Property(type="integer", property="last_page")
     */
    private $lastPage;

    /**
     * @var int
     *
     * @SWG\Property(type="integer", property="count")
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
