<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\ResultDTO;
use App\Entity\Book;
use App\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;

class BookController extends AbstractFOSRestController
{
    public const ITEMS_ON_PAGE = 20;

    /**
     * @var PaginationFactory
     */
    private $paginationFactory;

    /**
     * @param PaginationFactory $paginationFactory
     *
     * @return BookController
     *
     * @required
     */
    public function setPaginationFactory(PaginationFactory $paginationFactory): self
    {
        $this->paginationFactory = $paginationFactory;

        return $this;
    }

    /**
     * @param ParamFetcher $paramFetcher
     *
     * @throws \Throwable
     *
     * @return View
     *
     * @Rest\Get("/books", name="api_books")
     * @Rest\QueryParam(name="page", default="1", allowBlank=false, requirements="\d+")
     * @Rest\QueryParam(name="count", default=BookController::ITEMS_ON_PAGE, allowBlank=false, requirements="\d+")
     */
    public function index(ParamFetcher $paramFetcher): View
    {
        $queryBuilder = $this->getDoctrine()->getRepository(Book::class)->createQueryBuilder('self');

        $dto = new ResultDTO($this->paginationFactory->createCollection($paramFetcher, $queryBuilder, 'api_books'));

        return $this->view($dto);
    }

    /**
     * @param Book $book
     *
     * @return View
     *
     * @Rest\Get("/books/{id<\d+>}", name="api_book_show")
     */
    public function show(Book $book): View
    {
        $dto = new ResultDTO($book);

        return $this->view($dto);
    }
}
