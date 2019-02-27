<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthorController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     * @return AuthorController
     *
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

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
     * @param Request $request
     * @return Response
     * @throws \LogicException
     *
     * @Route("/authors/create", name="author_create", methods={"GET", "HEAD", "POST"})
     */
    public function create(Request $request): Response
    {
        $author = new Author();

        $form = $this->createFormBuilder($author)
            ->add('firstName', TextType::class, ['label' => 'author.firstName', 'attr' => ['maxlength' => 255]])
            ->add('lastName', TextType::class, ['label' => 'author.lastName', 'attr' => ['maxlength' => 255]])
            ->add('secondName', TextType::class, ['label' => 'author.secondName', 'required' => false])
            ->add('save', SubmitType::class, ['label' => $this->translator->trans('base.add')])
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

    /**
     * @param Author $author
     * @param Request $request
     * @return Response
     * @throws \LogicException
     *
     * @Route("/authors/{id<\d+>}/edit", name="author_edit", methods={"GET", "HEAD", "POST"})
     */
    public function edit(Author $author, Request $request): Response
    {
        $form = $this->createFormBuilder($author)
            ->add('firstName', TextType::class, ['label' => 'author.firstName', 'attr' => ['maxlength' => 255]])
            ->add('lastName', TextType::class, ['label' => 'author.lastName', 'attr' => ['maxlength' => 255]])
            ->add('secondName', TextType::class, ['label' => 'author.secondName', 'required' => false])
            ->add('save', SubmitType::class, ['label' => $this->translator->trans('base.edit')])
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

        return $this->render('author/edit.html.twig', ['form' => $form->createView(), 'author' => $author]);
    }

    /**
     * @param Author $author
     * @return JsonResponse
     * @throws \LogicException
     *
     * @Route("/authors/{id<\d+>}", name="author_delete", methods={"DELETE", "HEAD"})
     */
    public function delete(Author $author): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($author);
        $em->flush();

        return new JsonResponse();
    }
}
