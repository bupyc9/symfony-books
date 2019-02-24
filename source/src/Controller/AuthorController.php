<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @Route("/authors", name="authors", methods={"GET"})
     * @throws \LogicException
     */
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $authors = $repository->findAll();

        return $this->render('author/index.html.twig', ['authors' => $authors]);
    }
}
