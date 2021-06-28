<?php

namespace App\Controller;

use App\Entity\Help;
use App\Form\HelpType;
use App\Repository\HelpRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/help", name="help_")
 */
class HelpController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(HelpRepository $helpRepository): Response
    {
        $helps = $helpRepository->findAll();
        return $this->render('help/index.html.twig',
        [
            'helps' => $helps
        ]);
    }

    /**
     * @Route("/new", name="new")
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $help = new Help();

        $form = $this->createForm(HelpType::class, $help);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $help->setCreatedAt(new DateTimeImmutable());
            $help->setActive(true);
            $help->setApplicant($this->getUser());
            $em->persist($help);
            $em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('help/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{help}", name="edit")
     */
    public function edit(Help $help, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(HelpType::class, $help);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $help->setUpdatedAt(new DateTimeImmutable());
            $em->flush();
            $this->addFlash('info', '
            your request for help has been modified.');
            return $this->redirectToRoute('home');
        }

        return $this->render('help/edit.html.twig', [
            'help' => $help,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remove/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Help $help, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $help->getId(), $request->request->get('_token'))) {
            $em->remove($help);
            $em->flush();
            $this->addFlash('danger', 'your request for help has been deleted');
        }

        return $this->redirectToRoute('home');
    }
}