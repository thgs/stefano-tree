<?php

declare(strict_types=1);

namespace StefanoTree\NestedSet\QueryBuilder;

interface DescendantQueryBuilderInterface
{
    /**
     * Execute query and return result.
     *
     * @param int|string $nodeId
     *
     * @return array
     */
    public function get($nodeId): array;

    /**
     * Exclude fist N level from result.
     *
     * @param int $count
     *
     * @return DescendantQueryBuilderInterface
     */
    public function excludeFirstNLevel(int $count): DescendantQueryBuilderInterface;

    /**
     * Limit number of levels.
     *
     * @param int $count
     *
     * @return DescendantQueryBuilderInterface
     */
    public function limitDepth(int $count): DescendantQueryBuilderInterface;

    /**
     * Exclude specified branch from result.
     *
     * @param $nodeId
     *
     * @return DescendantQueryBuilderInterface
     */
    public function excludeBranch($nodeId): DescendantQueryBuilderInterface;
}
