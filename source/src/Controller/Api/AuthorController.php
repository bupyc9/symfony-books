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
use App\Service\Cache;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use LogicException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthorController extends AbstractFOSRestController
{
    public const ITEMS_ON_PAGE = 20;

    /**
     * @var PaginationFactory
     */
    private $paginationFactory;

    /**
     * @var TagAwareAdapterInterface
     */
    private $cache;

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
     * @param TagAwareAdapterInterface $cache
     *
     * @return AuthorController
     *
     * @required
     */
    public function setCache(TagAwareAdapterInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @param ParamFetcher $paramFetcher
     *
     * @throws LogicException
     * @throws \InvalidArgumentException
     * @throws CacheException
     * @throws InvalidArgumentException
     *
     * @return View
     *
     * @Rest\Get("/authors", name="api_authors")
     * @Rest\QueryParam(name="page", default="1", allowBlank=false, requirements="\d+")
     * @Rest\QueryParam(name="count", default=AuthorController::ITEMS_ON_PAGE, allowBlank=false, requirements="\d+")
     */
    public function index(ParamFetcher $paramFetcher): View
    {
        $cacheKey = Cache::createKey(__METHOD__);
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            $queryBuilder = $this->getDoctrine()->getRepository(Author::class)->createQueryBuilder('self');

            $dto = new ResultDTO($this->paginationFactory->createCollection($paramFetcher, $queryBuilder, 'api_authors'));

            $item->set($dto);
            $item->tag([Cache::createKey(Author::class)]);
            $this->cache->save($item);
        }
        $dto = $item->get();

        return $this->view($dto);
    }

    /**
     * @param int $id
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Get("/authors/{id<\d+>}", name="api_author_show")
     */
    public function show(int $id): View
    {
        $cacheKey = Cache::createKey(__METHOD__.$id);
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            $author = $this->getDoctrine()->getRepository(Author::class)->find($id);
            if (null === $author) {
                throw new NotFoundHttpException('Author not found');
            }

            $dto = new ResultDTO($author);
            $item->set($dto);
            $item->tag([Cache::createKey(Author::class)]);
            $this->cache->save($item);
        }
        $dto = $item->get();

        return $this->view($dto);
    }

    /**
     * @param Author $author
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     *
     * @return View
     *
     * @Rest\Delete("/authors/{id<\d+>}", name="api_author_destroy")
     */
    public function destroy(Author $author): View
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($author);
        $em->flush();

        $dto = new ResultDTO(new SuccessDTO());

        $this->cache->invalidateTags([Cache::createKey(Author::class)]);

        return $this->view($dto);
    }

    /**
     * @param Request $request
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Form\Exception\LogicException
     *
     * @return View
     *
     * @Rest\Post("/authors", name="api_author_store")
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

        $this->cache->invalidateTags([Cache::createKey(Author::class)]);

        return $this->view($result);
    }

    /**
     * @param Author  $author
     * @param Request $request
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Form\Exception\LogicException
     *
     * @return View
     *
     * @Rest\Put("/authors/{id<\d+>}", name="api_author_update")
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

        $this->cache->invalidateTags([Cache::createKey(Author::class)]);

        return $this->view($result);
    }
}
