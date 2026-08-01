<?php

namespace App\Controller\Daip;

use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/daip/entreprises', name: 'daip_entreprises_')]
#[IsGranted('ROLE_DAIP')]
class EntrepriseController extends AbstractController
{
    private const PAR_PAGE = 12;

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, EntrepriseRepository $entrepriseRepository): Response
    {
        $search = trim((string) $request->query->get('q', ''));

        $page = max(1, $request->query->getInt('page', 1));

        if ($search !== '') {
            $qb = $entrepriseRepository->createQueryBuilder('e')
                ->leftJoin('e.user', 'u')->addSelect('u')
                ->where('LOWER(e.nom) LIKE :q')
                ->orWhere('LOWER(u.email) LIKE :q')
                ->setParameter('q', '%' . strtolower($search) . '%')
                ->orderBy('e.nom', 'ASC');
        } else {
            $qb = $entrepriseRepository->createQueryBuilder('e')
                ->leftJoin('e.user', 'u')->addSelect('u')
                ->orderBy('e.nom', 'ASC');
        }

        $total = (int) $qb->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        $totalPages = max(1, (int) ceil($total / self::PAR_PAGE));
        $page = min($page, $totalPages);

        $entreprises = $entrepriseRepository->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')->addSelect('u')
            ->orderBy('e.nom', 'ASC');

        if ($search !== '') {
            $entreprises->where('LOWER(e.nom) LIKE :q')
                ->orWhere('LOWER(u.email) LIKE :q')
                ->setParameter('q', '%' . strtolower($search) . '%');
        }

        $entreprises = $entreprises->setFirstResult(($page - 1) * self::PAR_PAGE)
            ->setMaxResults(self::PAR_PAGE)
            ->getQuery()
            ->getResult();

        return $this->render('daip/entreprises/index.html.twig', [
            'entreprises' => $entreprises,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Entreprise $entreprise): Response
    {
        return $this->render('daip/entreprises/show.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }
}