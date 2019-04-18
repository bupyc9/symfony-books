<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\Input\StoreBookDTO;
use App\DTO\ResultDTO;
use App\DTO\SuccessDTO;
use App\Entity\Author;
use App\Entity\Book;
use App\Exception\FormValidationException;
use App\Form\StoreBookForm;
use App\Pagination\PaginationFactory;
use App\Repository\BookRepository;
use App\Service\Cache;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Swagger\Annotations as SWG;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookController extends AbstractFOSRestController
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
     * @var BookRepository
     */
    private $repository;

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
     * @param TagAwareAdapterInterface $cache
     *
     * @return BookController
     *
     * @required
     */
    public function setCache(TagAwareAdapterInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @param BookRepository $repository
     *
     * @return BookController
     *
     * @required
     */
    public function setRepository(BookRepository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * List of books.
     *
     * @param ParamFetcher $paramFetcher
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return View
     *
     * @Rest\Get("/books", name="api_books")
     * @Rest\QueryParam(name="page", default="1", allowBlank=false, requirements="\d+")
     * @Rest\QueryParam(name="count", default=BookController::ITEMS_ON_PAGE, allowBlank=false, requirements="\d+")
     *
     * @SWG\Tag(name="Books")
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
     *     default=BookController::ITEMS_ON_PAGE,
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return book list",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             type="object",
     *             @SWG\Property(
     *                 property="items", @SWG\Items(ref=@Model(type=Book::class)),
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
        $cacheKey = Cache::createKey(__METHOD__, $paramFetcher->all());
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit()) {
            $queryBuilder = $this->repository->createQueryBuilder('self')
                ->join('self.author', 'author')
                ->addSelect('author')
                ->addOrderBy('self.createdAt', 'ASC')
            ;

            $dto = new ResultDTO($this->paginationFactory->createCollection($paramFetcher, $queryBuilder, 'api_books'));

            $item->set($dto);
            $item->tag([Cache::createKey(Book::class), Cache::createKey(Author::class)]);
            $this->cache->save($item);
        }

        $dto = $item->get();

        return $this->view($dto);
    }

    /**
     * Book detail.
     *
     * @param int $id
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     *
     * @return View
     *
     * @Rest\Get("/books/{id<\d+>}", name="api_book_show")
     *
     * @SWG\Tag(name="Books")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Book ID",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return book detail",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=Book::class)
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Book not found",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
     */
    public function show(int $id): View
    {
        $cacheKey = Cache::createKey(__METHOD__.$id);
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit()) {
            $book = $this->repository->createQueryBuilder('self')
                ->join('self.author', 'author')
                ->addSelect('author')
                ->where('self.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult()
            ;
            if (null === $book) {
                throw new NotFoundHttpException('Book not found');
            }

            $dto = new ResultDTO($book);
            $item->set($dto);
            $item->tag([Cache::createKey(Author::class)]);
            $this->cache->save($item);
        }
        $dto = $item->get();

        return $this->view($dto);
    }

    /**
     * Remove book.
     *
     * @param Book $book
     *
     * @throws InvalidArgumentException
     * @throws \LogicException
     *
     * @return View
     *
     * @Rest\Delete("/books/{id<\d+>}", name="api_book_destroy")
     *
     * @SWG\Tag(name="Books")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Book ID",
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
     *     description="Book not found",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
     */
    public function destroy(Book $book): View
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();

        $dto = new ResultDTO(new SuccessDTO());

        $this->cache->invalidateTags([Cache::createKey(Book::class), Cache::createKey(Author::class)]);

        return $this->view($dto);
    }

    /**
     * Create book.
     *
     * @param Request $request
     *
     * @throws AlreadySubmittedException
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Post("/books", name="api_book_store")
     *
     * @SWG\Tag(name="Books")
     *
     * @SWG\Parameter(
     *     name="name",
     *     type="string",
     *     in="formData",
     *     required=true,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Parameter(
     *     name="author",
     *     type="integer",
     *     in="formData",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="year",
     *     type="integer",
     *     in="formData",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="pages",
     *     type="integer",
     *     in="formData",
     *     required=true,
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return book detail",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=Book::class)
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

        $this->cache->invalidateTags([Cache::createKey(Book::class), Cache::createKey(Author::class)]);

        return $this->view($result);
    }

    /**
     * Edit book.
     *
     * @param Book    $book
     * @param Request $request
     *
     * @throws AlreadySubmittedException
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws LogicException
     *
     * @return View
     *
     * @Rest\Put("/books/{id<\d+>}", name="api_book_update")
     *
     * @SWG\Tag(name="Books")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Book ID",
     * )
     * @SWG\Parameter(
     *     name="name",
     *     type="string",
     *     in="formData",
     *     required=true,
     *     description="Min length - 1, Max length - 255",
     * )
     * @SWG\Parameter(
     *     name="author",
     *     type="integer",
     *     in="formData",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="year",
     *     type="integer",
     *     in="formData",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="pages",
     *     type="integer",
     *     in="formData",
     *     required=true,
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return book detail",
     *     @SWG\Schema(
     *         type=ResultDTO::class,
     *         @SWG\Property(
     *             property="data",
     *             ref=@Model(type=Book::class)
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=422,
     *     description="Validation error",
     *     @SWG\Schema(ref=@Model(type=App\DTO\ErrorsDTO::class)),
     * )
     */
    public function update(Book $book, Request $request): View
    {
        $dto = new StoreBookDTO();
        $form = $this->createForm(StoreBookForm::class, $dto);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }

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

        $this->cache->invalidateTags([Cache::createKey(Book::class), Cache::createKey(Author::class)]);

        return $this->view($result);
    }
}
