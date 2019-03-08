<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\Input\StoreAuthorDTO;
use App\DTO\ResultDTO;
use App\DTO\SuccessDTO;
use App\Entity\Author;
use App\Exception\FormValidationException;
use App\Form\StoreAuthorForm;
use App\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use LogicException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends AbstractFOSRestController
{
    public const ITEMS_ON_PAGE = 20;

    /**
     * @var PaginationFactory
     */
    private $paginationFactory;

    /**
     * @param PaginationFactory $paginationFactory
     *
     * @return AuthorController
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
     * @Rest\Get("/authors", name="api_authors")
     * @Rest\QueryParam(name="page", default="1", allowBlank=false, requirements="\d+")
     * @Rest\QueryParam(name="count", default=AuthorController::ITEMS_ON_PAGE, allowBlank=false, requirements="\d+")
     */
    public function index(ParamFetcher $paramFetcher): View
    {
        $queryBuilder = $this->getDoctrine()->getRepository(Author::class)->createQueryBuilder('self');

        $dto = new ResultDTO($this->paginationFactory->createCollection($paramFetcher, $queryBuilder, 'api_authors'));

        return $this->view($dto);
    }

    /**
     * @param Author $author
     *
     * @return View
     *
     * @Rest\Get("/authors/{id<\d+>}", name="api_author_show")
     */
    public function show(Author $author): View
    {
        $dto = new ResultDTO($author);

        return $this->view($dto);
    }

    /**
     * @param Author $author
     *
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Delete("/authors/{id<\d+>}", name="author_destroy")
     */
    public function destroy(Author $author): View
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($author);
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
     * @Rest\Post("/authors", name="author_store")
     */
    public function store(Request $request): View
    {
        $dto = new StoreAuthorDTO();
        $form = $this->createForm(StoreAuthorForm::class, $dto);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }

        $author = new Author();
        $author
            ->setFirstName($dto->getFirstName())
            ->setLastName($dto->getLastName())
        ;
        if ('' !== $dto->getSecondName()) {
            $author->setSecondName($dto->getSecondName());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($author);
        $em->flush();

        $result = new ResultDTO($author);

        return $this->view($result);
    }

    /**
     * @param Author  $author
     * @param Request $request
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Put("/authors/{id<\d+>}", name="author_update")
     */
    public function update(Author $author, Request $request): View
    {
        $dto = new StoreAuthorDTO();
        $form = $this->createForm(StoreAuthorForm::class, $dto);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }

        $author
            ->setFirstName($dto->getFirstName())
            ->setLastName($dto->getLastName())
        ;
        if ('' !== $dto->getSecondName()) {
            $author->setSecondName($dto->getSecondName());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($author);
        $em->flush();

        $result = new ResultDTO($author);

        return $this->view($result);
    }
}
