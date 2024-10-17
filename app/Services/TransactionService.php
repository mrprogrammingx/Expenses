<?php

declare(strict_types=1);

namespace App\Services;
use App\Entity\User;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManager;
use App\DataObjects\TransactionData;
use App\DataObjects\DataTableQueryParams;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(TransactionData $transactionData, User $user): Transaction
    {
        $transaction = new Transaction();

        $transaction->setUser($user);

        return $this->update($transaction, $transactionData);
    }

    public function getPaginatedTransactions(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy, ['description', 'amount', 'date', 'category'])
            ? $params->orderBy
            : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (!empty($params->searchTerm)) {
            $query->where('t.description LIKE :description')
                ->setParameter('description', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        if ($orderBy === 'category') {
            $query->orderBy('c.name', $orderDir);
        } else {
            $query->orderBy('t.' . $orderBy, $orderDir);
        }

        error_log($query->getQuery()->getSQL());

        return new Paginator($query);
    }

    public function delete(int $id)
    {
        $transaction = $this->entityManager->find(Transaction::class, $id);

        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Transaction
    {
        $query = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.category', 'c');

        error_log($query->getQuery()->getSQL());
        error_log(implode(',',$query->getQuery()->getArrayResult()));

        return $query->getQuery()->getOneOrNullResult();
    }

    public function update(Transaction $transaction, TransactionData $transactionData)
    {
        $transaction->setDescription(($transactionData->description));
        $transaction->setAmount($transactionData->amount);
        $transaction->setDate($transactionData->date);
        $transaction->setCategory($transactionData->category);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }
}
