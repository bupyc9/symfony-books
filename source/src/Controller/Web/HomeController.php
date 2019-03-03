<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Book;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private const ITEMS_ON_PAGE = 9;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @param PaginatorInterface $paginator
     *
     * @return HomeController
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
     *
     * @Route("/", name="home", methods={"GET", "HEAD"})
     */
    public function index(Request $request): Response
    {
        $query = $this->getDoctrine()->getRepository(Book::class)->createQueryBuilder('self')->getQuery();
        $page = $request->query->getInt('page', 1);
        $books = $this->paginator->paginate($query, $page, self::ITEMS_ON_PAGE);

        return $this->render('home/index.html.twig', ['books' => $books]);
    }
}
