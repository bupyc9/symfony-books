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
use App\Service\Cache;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
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
     */
    public function index(ParamFetcher $paramFetcher): View
    {
        $cacheKey = Cache::createKey(__METHOD__);
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit()) {
            $queryBuilder = $this->getDoctrine()->getRepository(Book::class)->createQueryBuilder('self')
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
     * @param int $id
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     * @throws \LogicException
     *
     * @return View
     *
     * @Rest\Get("/books/{id<\d+>}", name="api_book_show")
     */
    public function show(int $id): View
    {
        $cacheKey = Cache::createKey(__METHOD__.$id);
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit()) {
            $book = $this->getDoctrine()->getRepository(Book::class)->createQueryBuilder('self')
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
     * @param Book $book
     *
     * @throws InvalidArgumentException
     * @throws \LogicException
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

        $this->cache->invalidateTags([Cache::createKey(Book::class), Cache::createKey(Author::class)]);

        return $this->view($dto);
    }

    /**
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
