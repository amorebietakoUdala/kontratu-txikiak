<?php

namespace App\Factory;

use App\Entity\DurationType;
use App\Repository\DurationTypeRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<DurationType>
 *
 * @method static DurationType|Proxy createOne(array $attributes = [])
 * @method static DurationType[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static DurationType|Proxy find(object|array|mixed $criteria)
 * @method static DurationType|Proxy findOrCreate(array $attributes)
 * @method static DurationType|Proxy first(string $sortedField = 'id')
 * @method static DurationType|Proxy last(string $sortedField = 'id')
 * @method static DurationType|Proxy random(array $attributes = [])
 * @method static DurationType|Proxy randomOrCreate(array $attributes = [])
 * @method static DurationType[]|Proxy[] all()
 * @method static DurationType[]|Proxy[] findBy(array $attributes)
 * @method static DurationType[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static DurationType[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static DurationTypeRepository|RepositoryProxy repository()
 * @method DurationType|Proxy create(array|callable $attributes = [])
 */
final class DurationTypeFactory extends ModelFactory
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
            // ->afterInstantiate(function(DurationType $durationType): void {})
        ;
    }

    protected static function getClass(): string
    {
        return DurationType::class;
    }
}
