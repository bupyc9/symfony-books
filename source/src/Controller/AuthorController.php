<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @Route("/authors", name="authors", methods={"GET", "HEAD"})
     * @throws \LogicException
     */
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $authors = $repository->findAll();

        return $this->render('author/index.html.twig', ['authors' => $authors]);
    }


    /**
     * @Route("/authors/create", name="author_create", methods={"GET", "POST", "HEAD"})
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     */
    public function create(Request $request): Response
    {
        $author = new Author();

        $form = $this->createFormBuilder($author)
            ->add('firstName', TextType::class, ['label' => 'author.firstName', 'attr' => ['maxlength' => 255]])
            ->add('lastName', TextType::class, ['label' => 'author.lastName', 'attr' => ['maxlength' => 255]])
            ->add('secondName', TextType::class, ['label' => 'author.secondName', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Добавить'])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('authors');
        }

        return $this->render('author/create.html.twig', ['form' => $form->createView()]);
    }
}
