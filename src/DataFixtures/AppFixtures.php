<?php

namespace App\DataFixtures;

use App\Factory\ContractFactory;
use App\Factory\ContractTypeFactory;
use App\Factory\DurationTypeFactory;
use App\Factory\IdentificationTypeFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        ContractTypeFactory::createOne([
            'id' => 1,
            'name' => 'Obrak / Obras'
        ]);
        ContractTypeFactory::createOne([
            'id' => 2,
            'name' => 'Zerbitzuak / Servicios'
        ]);
        ContractTypeFactory::createOne([
            'id' => 3,
            'name' => 'Hornikuntza / Suministros'
        ]);
        ContractTypeFactory::createOne([
            'id' => 8,
            'name' => 'Harpidetzak / Suscripciones'
        ]);

        DurationTypeFactory::createOne([
            'id' => 2,
            'name' => 'Egunak / Días'
        ]);

        DurationTypeFactory::createOne([
            'id' => 3,
            'name' => 'Asteak / Semanas'
        ]);

        DurationTypeFactory::createOne([
            'id' => 4,
            'name' => 'Hilabeteak / Meses'
        ]);

        DurationTypeFactory::createOne([
            'id' => 5,
            'name' => 'Urteak / Años'
        ]);

        IdentificationTypeFactory::createOne([
            'id' => 1,
            'name' => 'IFK / CIF'
        ]);

        IdentificationTypeFactory::createOne([
            'id' => 2,
            'name' => 'IFZ-AIZ / NIF-NIE'
        ]);

        IdentificationTypeFactory::createOne([
            'id' => 3,
            'name' => 'Atzerritarra / Extranjero'
        ]);

        UserFactory::createOne([
            'username' => 'ibilbao',
            'email' => 'ibilbao@amorebieta.eus',
            'roles' => ['ROLE_ADMIN']
        ]);        

        UserFactory::createMany(5);        

        ContractFactory::createMany(100, fn() => [
            'type' => ContractTypeFactory::random(),
            'durationType' => DurationTypeFactory::random(),
            'identificationType' => IdentificationTypeFactory::random(),
            'user' => UserFactory::random(),
        ]);

        $manager->flush();
    }
}
