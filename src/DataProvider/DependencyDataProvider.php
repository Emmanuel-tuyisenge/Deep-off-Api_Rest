<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Dependency;
use App\Repository\DependencyRepository;
use Ramsey\Uuid\Uuid;

class DependencyDataProvider implements
    CollectionDataProviderInterface,
    RestrictedDataProviderInterface,
    ItemDataProviderInterface
{

    public function __construct(private DependencyRepository $repository)
    {
    }

    public function getCollection(
        string $resourceClass,
        ?string $operationName = null,
        array $context = []
    ) {
        return $this->repository->findAll();
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return $resourceClass == Dependency::class;
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = [])
    {
        return $this->repository->find(($id));
    }
}
