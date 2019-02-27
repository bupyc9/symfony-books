<?php

namespace App\Controller;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends Controller
{
    /**
     * @return Response
     * @throws \LogicException
     *
     * @Route("/books", name="books", methods={"GET", "HEAD"})
     */
    public function index(): Response
    {
        $books = $this->getDoctrine()->getRepository(Book::class)->findAll();

        return $this->render('book/index.html.twig', ['books' => $books]);
    }
}
