<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$repo = $kernel->getContainer()->get('doctrine')->getRepository(App\Entity\Offre::class);
dump(count($repo->rechercherOffresPubliees(['q' => 'Gimenez Bouvet S.A.S.'])));
