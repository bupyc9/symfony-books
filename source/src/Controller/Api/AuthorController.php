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
use App\Repository\AuthorRepository;
use App\Service\Cache;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use LogicException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Swagger\Annotations as SWG;
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
     * @var AuthorRepository
     */
    private $repository;

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
     * @param AuthorRepository $repository
     *
     * @return AuthorController
     *
     * @required
     */
    public function setRepository(AuthorRepository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * List of authors.
     *
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
     *
     * @SWG\Tag(name="Authors")
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     default="1",
     * )
     * @SWG\Parameter(
     *     name="count",
     *     in="query",
     *     type="integer",
     *     default=AuthorController::ITEMS_ON_PAGE,
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return author list",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             type="object",
     *             @SWG\Property(
     *                 property="items", @SWG\Items(ref=@Model(type=Author::class)),
     *             ),
     *             @SWG\Property(
     *                 property="meta", ref=@Model(type=\App\DTO\CollectionMetaDTO::class),
     *             ),
     *             @SWG\Property(
     *                 property="links", ref=@Model(type=\App\DTO\LinksDTO::class),
     *             ),
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Page not found",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
     */
    public function index(ParamFetcher $paramFetcher): View
    {
        $cacheKey = Cache::createKey(__METHOD__);
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            $queryBuilder = $this->repository->createQueryBuilder('self');

            $dto = new ResultDTO($this->paginationFactory->createCollection($paramFetcher, $queryBuilder, 'api_authors'));

            $item->set($dto);
            $item->tag([Cache::createKey(Author::class)]);
            $this->cache->save($item);
        }
        $dto = $item->get();

        return $this->view($dto);
    }

    /**
     * Author detail.
     *
     * @param int $id
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     *
     * @return View
     *
     * @Rest\Get("/authors/{id<\d+>}", name="api_author_show")
     *
     * @SWG\Tag(name="Authors")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Author ID",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return author detail",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=Author::class)
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Author not found",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
     */
    public function show(int $id): View
    {
        $cacheKey = Cache::createKey(__METHOD__.$id);
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            $author = $this->repository->find($id);
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
     * Remove author.
     *
     * @param Author $author
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     *
     * @return View
     *
     * @Rest\Delete("/authors/{id<\d+>}", name="api_author_destroy")
     *
     * @SWG\Tag(name="Authors")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Author ID",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=SuccessDTO::class),
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Author not found",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
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
     * Create author.
     *
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
     *
     * @SWG\Tag(name="Authors")
     *
     * @SWG\Parameter(
     *     name="first_name",
     *     type="string",
     *     in="formData",
     *     required=true,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Parameter(
     *     name="last_name",
     *     type="string",
     *     in="formData",
     *     required=true,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Parameter(
     *     name="second_name",
     *     type="string",
     *     in="formData",
     *     required=false,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return author detail",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=Author::class)
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=422,
     *     description="Validation error",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
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
     * Edit author.
     *
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
     *
     * @SWG\Tag(name="Authors")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Author ID",
     * )
     * @SWG\Parameter(
     *     name="first_name",
     *     type="string",
     *     in="formData",
     *     required=true,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Parameter(
     *     name="last_name",
     *     type="string",
     *     in="formData",
     *     required=true,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Parameter(
     *     name="second_name",
     *     type="string",
     *     in="formData",
     *     required=false,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return author detail",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=Author::class)
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=422,
     *     description="Validation error",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
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
