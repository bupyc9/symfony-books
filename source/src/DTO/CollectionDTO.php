<?php

declare(strict_types=1);

namespace App\DTO;

use Pagerfanta\Pagerfanta;

class CollectionDTO
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var CollectionMetaDTO
     */
    private $meta;

    /**
     * @var LinksDTO
     */
    private $links;

    public function __construct(Pagerfanta $pagination)
    {
        $this->items = \iterator_to_array($pagination->getCurrentPageResults());
        $this->meta = new CollectionMetaDTO(
            $pagination->getCurrentPage(),
            $pagination->getNbPages(),
            $pagination->getNbResults()
        );
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getMeta(): CollectionMetaDTO
    {
        return $this->meta;
    }

    public function setMeta(CollectionMetaDTO $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getLinks(): LinksDTO
    {
        return $this->links;
    }

    public function setLinks(LinksDTO $links): self
    {
        $this->links = $links;

        return $this;
    }
}
