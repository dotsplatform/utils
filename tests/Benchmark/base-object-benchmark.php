<?php
/**
 * Micro-benchmark for BaseObject::fromArray() and toArray().
 *
 * Run: php tests/Benchmark/base-object-benchmark.php [iterations]
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

require __DIR__ . '/../../vendor/autoload.php';

use Tests\Unit\BaseObject\Objects\TestBaseObject;
use Tests\Unit\BaseObject\Objects\TestBaseObjectWithIntEnum;
use Tests\Unit\BaseObject\Objects\TestCollection;
use Tests\Unit\BaseObject\Objects\TestIntEnum;
use Tests\Unit\BaseObject\Objects\TestObjectWithIncludedObjAndCollection;

/**
 * A DTO shaped like a typical service DTO: many scalar fields, a nested DTO, an enum and a collection.
 */
class BenchmarkWideDTO extends Dots\Data\DTO
{
    protected string $id;
    protected string $accountId;
    protected string $companyId;
    protected ?string $userId = null;
    protected string $name = '';
    protected ?string $description = null;
    protected int $price = 0;
    protected int $oldPrice = 0;
    protected int $count = 1;
    protected float $weight = 0.0;
    protected bool $isActive = true;
    protected bool $isDeleted = false;
    protected array $tags = [];
    protected ?int $createdAt = null;
    protected ?int $updatedAt = null;
    protected TestIntEnum $status = TestIntEnum::ONE;
    protected ?TestBaseObject $owner = null;
    protected ?TestCollection $items = null;
}

$iterations = (int)($argv[1] ?? 100000);

$flatData = [
    'id' => 'f1d2a3b4-0000-4000-8000-000000000001',
    'name' => 'Flat DTO',
];
$enumData = [
    'enum' => 1,
];
$nestedData = [
    'id' => 'f1d2a3b4-0000-4000-8000-000000000002',
    'object' => $flatData,
    'collection' => [1, 2, 3],
];
$wideData = [
    'id' => 'f1d2a3b4-0000-4000-8000-000000000004',
    'accountId' => 'f1d2a3b4-0000-4000-8000-000000000005',
    'companyId' => 'f1d2a3b4-0000-4000-8000-000000000006',
    'name' => 'Wide DTO',
    'price' => 12000,
    'count' => 2,
    'weight' => 0.5,
    'tags' => ['a', 'b'],
    'createdAt' => 1763700000,
    'status' => 2,
    'owner' => $flatData,
    'items' => [1, 2, 3],
];

function measureMicrosecondsPerCall(int $iterations, callable $callback): float
{
    $callback();
    $startedAt = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $callback();
    }
    $elapsedNanoseconds = hrtime(true) - $startedAt;

    return $elapsedNanoseconds / 1000 / $iterations;
}

$flatObject = TestBaseObject::fromArray($flatData);
$nestedObject = TestObjectWithIncludedObjAndCollection::fromArray($nestedData);
$wideObject = BenchmarkWideDTO::fromArray($wideData);

$results = [
    'fromArray flat DTO (2 props)' => measureMicrosecondsPerCall(
        $iterations,
        fn () => TestBaseObject::fromArray($flatData),
    ),
    'fromArray DTO with enum' => measureMicrosecondsPerCall(
        $iterations,
        fn () => TestBaseObjectWithIntEnum::fromArray($enumData),
    ),
    'fromArray nested DTO (3 DTOs + 2 collections)' => measureMicrosecondsPerCall(
        $iterations,
        fn () => TestObjectWithIncludedObjAndCollection::fromArray($nestedData),
    ),
    'fromArray wide DTO (18 props, nested DTO, enum)' => measureMicrosecondsPerCall(
        $iterations,
        fn () => BenchmarkWideDTO::fromArray($wideData),
    ),
    'toArray flat DTO' => measureMicrosecondsPerCall(
        $iterations,
        fn () => $flatObject->toArray(),
    ),
    'toArray nested DTO' => measureMicrosecondsPerCall(
        $iterations,
        fn () => $nestedObject->toArray(),
    ),
    'toArray wide DTO' => measureMicrosecondsPerCall(
        $iterations,
        fn () => $wideObject->toArray(),
    ),
];

printf("PHP %s, %d iterations each\n", PHP_VERSION, $iterations);
foreach ($results as $name => $microsecondsPerCall) {
    printf("%-48s %8.3f us\n", $name, $microsecondsPerCall);
}
