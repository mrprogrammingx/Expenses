<?php

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }
    public function make(string $name, User $user): Category
    {
        $category = new Category();

        $category->setUser($user);

        return $this->edit($category, $name);
    }

    public function create(string $name, User $user): Category
    {
        $category = $this->make($name, $user);

        $this->flush($category);

        return $category;
    }

    public function flush(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    public function getPaginatedCategories(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);
        
        $orderBy = in_array($params->orderBy, ['name', 'createdAt', 'updatedAt']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm))
        {
            $search = addcslashes($params->searchTerm,'%_');
            $query->where('c.name LIKE :name')->setParameter(
                'name',
                "%$search%"
            );
        }

        $query->orderBy('c.' . $orderBy, $orderDir);
        
        return new Paginator($query);
    }
    public function delete(int $id): void
    {
        $category = $this->entityManager->find(Category::class, $id);

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Category
    {
        return $this->entityManager->find(Category::class, $id);
    }
    public function edit(Category $category, string $name): Category
    {
        $category->setName($name);

        return $category;
    }

    public function update(Category $category, string $name): Category
    {
        $category = $this->edit($category, $name);

        $this->flush($category);

        return $category;
    }

    public function getCategoryName(): array
    {
        return $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select('c.id', 'c.name')
            ->getQuery()
            ->getArrayResult();
    }
}
