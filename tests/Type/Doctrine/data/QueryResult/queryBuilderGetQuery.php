<?php declare(strict_types = 1);

namespace QueryResult\CreateQuery;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use QueryResult\Entities\Many;
use function PHPStan\Testing\assertType;

class QueryBuilderGetQuery
{
	private function getQueryBuilderMany(EntityManagerInterface $em): QueryBuilder
	{
		return $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm');
	}

	public function addAndWhereAndGetQuery(EntityManagerInterface $em): void
	{
		$qb = $this->getQueryBuilderMany($em)->andWhere('m.intColumn = 1');
		assertType('list<QueryResult\Entities\Many>', $qb->getQuery()->getResult());
	}

	public function getQueryDirectly(EntityManagerInterface $em): void
	{
		assertType('list<QueryResult\Entities\Many>', $this->getQueryBuilderMany($em)->getQuery()->getResult());
	}

	public function testQueryTypeParametersAreInfered(EntityManagerInterface $em): void
	{
		$query = $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm')
			->getQuery();

		assertType('Doctrine\ORM\Query<null, QueryResult\Entities\Many>', $query);

		$query = $em->createQueryBuilder()
			->select(['m.intColumn', 'm.stringNullColumn'])
			->from(Many::class, 'm')
			->getQuery();

		assertType('Doctrine\ORM\Query<null, array{intColumn: int, stringNullColumn: string|null}>', $query);
	}

	public function testIndexByInfering(EntityManagerInterface $em): void
	{
		$query = $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm', 'm.intColumn')
			->getQuery();

		assertType('Doctrine\ORM\Query<int, QueryResult\Entities\Many>', $query);

		$query = $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm', 'm.stringColumn')
			->getQuery();

		assertType('Doctrine\ORM\Query<string, QueryResult\Entities\Many>', $query);

		$query = $em->createQueryBuilder()
			->select(['m.intColumn', 'm.stringNullColumn'])
			->from(Many::class, 'm')
			->indexBy('m', 'm.stringColumn')
			->getQuery();

		assertType('Doctrine\ORM\Query<string, array{intColumn: int, stringNullColumn: string|null}>', $query);
	}

	public function testIndexByResultInfering(EntityManagerInterface $em): void
	{
		$result = $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm', 'm.intColumn')
			->getQuery()
			->getResult();

		assertType('array<int, QueryResult\Entities\Many>', $result);

		$result = $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm', 'm.stringColumn')
			->getQuery()
			->getResult();

		assertType('array<string, QueryResult\Entities\Many>', $result);

		$result = $em->createQueryBuilder()
			->select(['m.intColumn', 'm.stringNullColumn'])
			->from(Many::class, 'm')
			->indexBy('m', 'm.stringColumn')
			->getQuery()
			->getResult();

		assertType('array<string, array{intColumn: int, stringNullColumn: string|null}>', $result);
	}

	public function testQueryResultTypeIsMixedWhenDQLIsNotKnown(QueryBuilder $builder): void
	{
		$query = $builder->getQuery();

		assertType('Doctrine\ORM\Query<null, mixed>', $query);
	}

	public function testQueryResultTypeIsMixedWhenDQLIsInvalid(EntityManagerInterface $em): void
	{
		$query = $em->createQueryBuilder()
			->select('invalid')
			->from(Many::class, 'm')
			->getQuery();

		assertType('Doctrine\ORM\Query<mixed, mixed>', $query);
	}

	public function testQueryResultTypeIsVoidWithDeleteOrUpdate(EntityManagerInterface $em): void
	{
		$query = $em->getRepository(Many::class)
				 ->createQueryBuilder('m')
				 ->where('m.id IN (:ids)')
				 ->setParameter('ids', $ids)
				 ->delete()
				 ->getQuery();

		assertType('Doctrine\ORM\Query<void, void>', $query);

		$query = $em->getRepository(Many::class)
				 ->createQueryBuilder('m')
				 ->where('m.id IN (:ids)')
				 ->setParameter('ids', $ids)
				 ->update()
				 ->set('m.intColumn', '42')
				 ->getQuery();

		assertType('Doctrine\ORM\Query<void, void>', $query);

	}

	public function testQueryTypeIsInferredOnAcrossMethods(EntityManagerInterface $em): void
	{
		$query = $this->getQueryBuilder($em)
			->getQuery();

		assertType('Doctrine\ORM\Query<null, QueryResult\Entities\Many>', $query);
	}

	private function getQueryBuilder(EntityManagerInterface $em): QueryBuilder
	{
		return $em->createQueryBuilder()
			->select('m')
			->from(Many::class, 'm');
	}
}
