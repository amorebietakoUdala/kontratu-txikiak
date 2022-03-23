<?php

namespace App\Factory;

use App\Entity\ContractType;
use App\Repository\ContractTypeRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<ContractType>
 *
 * @method static ContractType|Proxy createOne(array $attributes = [])
 * @method static ContractType[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ContractType|Proxy find(object|array|mixed $criteria)
 * @method static ContractType|Proxy findOrCreate(array $attributes)
 * @method static ContractType|Proxy first(string $sortedField = 'id')
 * @method static ContractType|Proxy last(string $sortedField = 'id')
 * @method static ContractType|Proxy random(array $attributes = [])
 * @method static ContractType|Proxy randomOrCreate(array $attributes = [])
 * @method static ContractType[]|Proxy[] all()
 * @method static ContractType[]|Proxy[] findBy(array $attributes)
 * @method static ContractType[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static ContractType[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static ContractTypeRepository|RepositoryProxy repository()
 * @method ContractType|Proxy create(array|callable $attributes = [])
 */
final class ContractTypeFactory extends ModelFactory
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
            'name' => self::faker()->text(),
            'maxAmount' => self::faker()->numberBetween(15000, 48400),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(ContractType $contractType): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ContractType::class;
    }
}
