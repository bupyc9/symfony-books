<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Author;
use App\Entity\Book;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BookController extends AbstractController
{
    private const ITEMS_ON_PAGE = 10;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @param TranslatorInterface $translator
     *
     * @return BookController
     *
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @param PaginatorInterface $paginator
     *
     * @return BookController
     *
     * @required
     */
    public function setPaginator(PaginatorInterface $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return Response
     * @Route("/books", name="books", methods={"GET", "HEAD"})
     */
    public function index(Request $request): Response
    {
        $query = $this->getDoctrine()->getRepository(Book::class)->createQueryBuilder('self')->getQuery();
        $page = $request->query->getInt('page', 1);
        $books = $this->paginator->paginate($query, $page, self::ITEMS_ON_PAGE);

        return $this->render('book/index.html.twig', ['books' => $books]);
    }

    /**
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return Response
     *
     * @Route("/books/create", name="book_create", methods={"GET", "HEAD", "POST"})
     */
    public function create(Request $request): Response
    {
        $book = new Book();
        $authors = $this->getDoctrine()->getRepository(Author::class)->findAll();

        $form = $this->createFormBuilder($book)
            ->add('name', TextType::class, ['label' => 'book.name', 'attr' => ['maxlength' => 255]])
            ->add('author', EntityType::class, ['label' => 'book.author', 'class' => Author::class, 'choices' => $authors])
            ->add('year', IntegerType::class, ['label' => 'book.year'])
            ->add('pages', IntegerType::class, ['label' => 'book.pages'])
            ->add('save', SubmitType::class, ['label' => $this->translator->trans('base.add')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('books');
        }

        return $this->render('book/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Book    $book
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return Response
     *
     * @Route("/books/{id<\d+>}/edit", name="book_edit", methods={"GET", "HEAD", "POST"})
     */
    public function edit(Book $book, Request $request): Response
    {
        $authors = $this->getDoctrine()->getRepository(Author::class)->findAll();

        $form = $this->createFormBuilder($book)
            ->add('name', TextType::class, ['label' => 'book.name', 'attr' => ['maxlength' => 255]])
            ->add('author', EntityType::class, ['label' => 'book.author', 'class' => Author::class, 'choices' => $authors])
            ->add('year', IntegerType::class, ['label' => 'book.year'])
            ->add('pages', IntegerType::class, ['label' => 'book.pages'])
            ->add('save', SubmitType::class, ['label' => $this->translator->trans('base.edit')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('books');
        }

        return $this->render('book/edit.html.twig', ['form' => $form->createView(), 'book' => $book]);
    }

    /**
     * @param Book $book
     *
     * @throws \LogicException
     *
     * @return JsonResponse
     *
     * @Route("/books/{id<\d+>}", name="book_delete", methods={"DELETE", "HEAD"})
     */
    public function delete(Book $book): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();

        return new JsonResponse();
    }
}
