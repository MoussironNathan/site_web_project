<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Season;
use App\Form\SeasonType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/season")
 */
class SeasonController extends AbstractController
{
    /**
     * @Route("/", name="season_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $seasons = $entityManager
            ->getRepository(Season::class)
            ->findAll();

        return $this->render('season/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }

    /**
     * @Route("/new", name="season_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($season);
            $entityManager->flush();

            return $this->redirectToRoute('season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('season/new.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="season_show", methods={"GET"})
     */
    public function show(Season $season): Response
    {
        $serie = $season->getSeries();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Episode::class);
        $episodes = $repository->findBy(['season' => $season->getId()], ['number' => 'ASC']);

        return $this->render('season/show.html.twig', [
            'serie' => $serie,
            'season' => $season,
            'episodes' => $episodes,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="season_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Season $season, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('season/edit.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="season_delete", methods={"POST"})
     */
    public function delete(Request $request, Season $season, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$season->getId(), $request->request->get('_token'))) {
            $entityManager->remove($season);
            $entityManager->flush();
        }

        return $this->redirectToRoute('season_index', [], Response::HTTP_SEE_OTHER);
    }
}
