<?php

namespace App\Controller;

use App\Entity\Series;
use App\Entity\Season;
use App\Form\SeriesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


/**
 * @Route("/series")
 */
class SeriesController extends AbstractController
{
    /**
     * @Route("/", name="series_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        $donnees = $entityManager
            ->getRepository(Series::class)
            ->findBy(
                [],
                ["title" => "ASC"]
            );
        
        $series = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            10
        );

        $serie = new Series();
        $form = $this->createForm(SeriesType::class, $serie, [
            'action' => $this->generateUrl('search'),
        ]);

        return $this->render('series/index.html.twig', [
            'series' => $series,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/series/research ", name="search", methods={"GET"})
     */
    public function search(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    { 
        $query = $entityManager->createQuery(
            "SELECT s
            FROM App:Series s
            WHERE s.title LIKE :title");

        dump($query);
        
        $query->setParameter("title", $_GET['series']['title'].'%');
        $donnees = $query->getResult();

        $series = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            10
        );

        $serie = new Series();
        $form = $this->createForm(SeriesType::class, $serie, [
            'action' => $this->generateUrl('search'),
        ]);

        return $this->render('series/index.html.twig', [
            'series' => $series,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="series_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $series = new Series();
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($series);
            $entityManager->flush();

            return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('series/new.html.twig', [
            'series' => $series,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="series_show", methods={"GET"})
     */
    public function show(Series $serie): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Season::class);
        $seasons = $repository->findBy(['series' => $serie->getId()], ['number' => 'ASC']);

        return $this->render('series/show.html.twig', [
            'serie' => $serie,
            'seasons' => $seasons,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="series_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('series/edit.html.twig', [
            'series' => $series,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="series_delete", methods={"POST"})
     */
    public function delete(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$series->getId(), $request->request->get('_token'))) {
            $entityManager->remove($series);
            $entityManager->flush();
        }

        return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/series/poster/{id}", name="poster_series_show", methods={"GET"})
     */
    public function poster(Series $serie): Response
    {
        return new Response(stream_get_contents($serie->getPoster()), 200, array('content-type' => 'image/jpeg', ));
    }

    
}
