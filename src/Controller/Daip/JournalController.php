<?php

namespace App\Controller\Daip;

use App\Repository\JournalActiviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/daip/journal', name: 'daip_journal_')]
class JournalController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(JournalActiviteRepository $journalActiviteRepository): Response
    {
        $logs = $journalActiviteRepository->findBy([], ['date' => 'DESC'], 100);

        return $this->render('daip/journal/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}