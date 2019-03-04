<?php

declare(strict_types=1);

namespace App\Pagination;

use App\DTO\CollectionDTO;
use App\DTO\LinksDTO;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcher;
use InvalidArgumentException;
use LogicException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     *
     * @return PaginationFactory
     *
     * @required
     */
    public function setRouter(RouterInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @param ParamFetcher $paramFetcher
     * @param QueryBuilder $queryBuilder
     * @param string       $routeName
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     *
     * @return CollectionDTO
     */
    public function createCollection(ParamFetcher $paramFetcher, QueryBuilder $queryBuilder, string $routeName): CollectionDTO
    {
        $page = (int) $paramFetcher->get('page');
        $count = (int) $paramFetcher->get('count');
        $count > 100 && $count = 100;

        $adapter = new DoctrineORMAdapter($queryBuilder, false);
        $pagination = new Pagerfanta($adapter);
        $pagination
            ->setMaxPerPage($count)
            ->setCurrentPage($page);

        $collectionDTO = new CollectionDTO($pagination);
        $collectionDTO->setLinks($this->generateLinksDTO($routeName, $count, $pagination));

        return $collectionDTO;
    }

    /**
     * @param string     $routeName
     * @param int        $count
     * @param Pagerfanta $pagination
     *
     * @throws \Pagerfanta\Exception\LogicException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     *
     * @return LinksDTO
     */
    protected function generateLinksDTO(string $routeName, int $count, Pagerfanta $pagination): LinksDTO
    {
        $linksDTO = new LinksDTO();
        $linksDTO
            ->setFirst($this->router->generate($routeName, ['page' => 1, 'count' => $count]))
            ->setLast($this->router->generate($routeName, ['page' => $pagination->getNbPages(), 'count' => $count]));
        if ($pagination->hasNextPage()) {
            $linksDTO->setNext(
                $this->router->generate($routeName, ['page' => $pagination->getNextPage(), 'count' => $count])
            );
        }
        if ($pagination->hasPreviousPage()) {
            $linksDTO->setNext(
                $this->router->generate($routeName, ['page' => $pagination->getPreviousPage(), 'count' => $count])
            );
        }

        return $linksDTO;
    }
}
