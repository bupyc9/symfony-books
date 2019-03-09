<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\Input\StoreBookDTO;
use App\DTO\ResultDTO;
use App\DTO\SuccessDTO;
use App\Entity\Book;
use App\Exception\FormValidationException;
use App\Form\StoreBookForm;
use App\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use LogicException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param Book $book
     *
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Delete("/books/{id<\d+>}", name="api_book_destroy")
     */
    public function destroy(Book $book): View
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();

        $dto = new ResultDTO(new SuccessDTO());

        return $this->view($dto);
    }

    /**
     * @param Request $request
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Post("/books", name="api_book_store")
     */
    public function store(Request $request): View
    {
        $dto = new StoreBookDTO();
        $form = $this->createForm(StoreBookForm::class, $dto);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }

        $book = new Book();
        $book
            ->setName($dto->getName())
            ->setAuthor($dto->getAuthor())
            ->setYear($dto->getYear())
            ->setPages($dto->getPages())
        ;

        $em = $this->getDoctrine()->getManager();
        $em->persist($book);
        $em->flush();

        $result = new ResultDTO($book);

        return $this->view($result);
    }
}
