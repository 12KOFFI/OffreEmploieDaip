<?php

namespace App\DataFixtures;

use App\Entity\Entreprise;
use App\Entity\Metier;
use App\Entity\Offre;
use App\Entity\OffreMetier;
use App\Entity\User;
use App\Enum\Diplome;
use App\Enum\NiveauEtude;
use App\Enum\StatutOffre;
use App\Enum\TypeContrat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $userRepo = $manager->getRepository(User::class);
        $metierRepo = $manager->getRepository(Metier::class);

        // 1. Récupérer les Métiers existants
        $metiers = $metierRepo->findAll();
        if (empty($metiers)) {
            throw new \RuntimeException('Aucun métier trouvé en base de données. Veuillez en ajouter d\'abord.');
        }

        // 2. Créer quelques Entreprises (et Utilisateurs liés)
        $entreprises = [];
        for ($i = 0; $i < 5; $i++) {
            $email = "entreprise{$i}@test.com";
            $user = $userRepo->findOneBy(['email' => $email]);
            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setRoles(['ROLE_ENTREPRISE']);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
                $manager->persist($user);

                $entreprise = new Entreprise();
                $entreprise->setNom($faker->company());
                $entreprise->setDescription($faker->paragraph(3));
                $entreprise->setContact($faker->phoneNumber());
                $entreprise->setUser($user);
                
                $manager->persist($entreprise);
            } else {
                $entreprise = $user->getEntreprise();
            }
            $entreprises[] = $entreprise;
        }

        // 3. Créer des Offres (> 10)
        $niveaux = NiveauEtude::cases();
        $diplomes = Diplome::cases();
        $contrats = TypeContrat::cases();

        for ($i = 0; $i < 20; $i++) {
            $offre = new Offre();
            $offre->setTitre($faker->jobTitle());
            $offre->setDescription($faker->paragraphs(2, true));
            $offre->setEntreprise($faker->randomElement($entreprises));
            
            // 80% publiées, 20% brouillons
            if ($faker->boolean(80)) {
                $offre->setStatut(StatutOffre::PUBLIEE);
                $offre->setDatePublication(new \DateTimeImmutable());
            } else {
                $offre->setStatut(StatutOffre::BROUILLON);
            }

            // Ajouter 1 à 3 postes/métiers pour cette offre
            $nbMetiers = $faker->numberBetween(1, 3);
            for ($j = 0; $j < $nbMetiers; $j++) {
                $om = new OffreMetier();
                $om->setOffre($offre);
                $om->setMetier($faker->randomElement($metiers));
                $om->setNombrePostes($faker->numberBetween(1, 5));
                $om->setVille($faker->city());
                
                if ($faker->boolean(70)) $om->setTypeContrat($faker->randomElement($contrats));
                if ($faker->boolean(50)) $om->setNiveauEtude($faker->randomElement($niveaux));
                if ($faker->boolean(50)) $om->setDiplome($faker->randomElement($diplomes));
                if ($faker->boolean(50)) $om->setNbAnneesExperience($faker->numberBetween(1, 10));
                if ($faker->boolean(50)) {
                    $min = $faker->numberBetween(100, 500) * 1000;
                    $om->setSalaireMin($min);
                    $om->setSalaireMax($min + $faker->numberBetween(50, 200) * 1000);
                }
                
                $manager->persist($om);
            }

            $manager->persist($offre);
        }

        $manager->flush();
    }
}
