<?php

namespace App\Factory;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use DateTime;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Contract>
 *
 * @method static Contract|Proxy createOne(array $attributes = [])
 * @method static Contract[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Contract|Proxy find(object|array|mixed $criteria)
 * @method static Contract|Proxy findOrCreate(array $attributes)
 * @method static Contract|Proxy first(string $sortedField = 'id')
 * @method static Contract|Proxy last(string $sortedField = 'id')
 * @method static Contract|Proxy random(array $attributes = [])
 * @method static Contract|Proxy randomOrCreate(array $attributes = [])
 * @method static Contract[]|Proxy[] all()
 * @method static Contract[]|Proxy[] findBy(array $attributes)
 * @method static Contract[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Contract[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static ContractRepository|RepositoryProxy repository()
 * @method Contract|Proxy create(array|callable $attributes = [])
 */
final class ContractFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            // TODO add your default values here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories)
            'code' => 'AYT/'.self::faker()->numberBetween(1,200).'/'.self::faker()->numberBetween(2021,2022),
            'subjectEs' => self::faker()->text(),
            'subjectEu' => self::faker()->text(),
            'amountWithoutVAT' => self::faker()->randomFloat(2, 1000, 100000),
            'amountWithVAT' => self::faker()->randomFloat(2, 1000, 100000),
            'duration' => self::faker()->randomFloat(2, 1, 720),
            'idNumber' => self::faker()->word(),
            'enterprise' => self::faker()->text(),
            'awardDate' => self::faker()->datetime(),
            'notified' => self::faker()->boolean(0.5),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Contract $contract): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Contract::class;
    }
}

