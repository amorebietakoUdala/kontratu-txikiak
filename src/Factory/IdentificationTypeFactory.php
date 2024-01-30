<?php

namespace App\Factory;

use App\Entity\IdentificationType;
use App\Repository\IdentificationTypeRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<IdentificationType>
 *
 * @method static IdentificationType|Proxy createOne(array $attributes = [])
 * @method static IdentificationType[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static IdentificationType|Proxy find(object|array|mixed $criteria)
 * @method static IdentificationType|Proxy findOrCreate(array $attributes)
 * @method static IdentificationType|Proxy first(string $sortedField = 'id')
 * @method static IdentificationType|Proxy last(string $sortedField = 'id')
 * @method static IdentificationType|Proxy random(array $attributes = [])
 * @method static IdentificationType|Proxy randomOrCreate(array $attributes = [])
 * @method static IdentificationType[]|Proxy[] all()
 * @method static IdentificationType[]|Proxy[] findBy(array $attributes)
 * @method static IdentificationType[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static IdentificationType[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static IdentificationTypeRepository|RepositoryProxy repository()
 * @method IdentificationType|Proxy create(array|callable $attributes = [])
 */
final class IdentificationTypeFactory extends ModelFactory
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
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(IdentificationType $identificationType): void {})
        ;
    }

    protected static function getClass(): string
    {
        return IdentificationType::class;
    }
}
