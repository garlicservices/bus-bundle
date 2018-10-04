<?php

namespace Garlic\Bus\Service\GraphQL;

class QueryRelation
{
    /** Relation types */
    const TYPE_ONE = 1;
    const TYPE_MANY = 2;
    
    /** @var string */
    private $current;
    
    /** @var string */
    private $target;
    
    /** @var QueryBuilder */
    private $query;
    
    /** @var string */
    private $alias;
    
    /** @var int */
    private $type;
    
    /**
     * Set current
     *
     * @return string
     */
    public function getCurrent(): string
    {
        return $this->current;
    }
    
    /**
     * Set current
     *
     * @param string $current
     * @return QueryRelation
     */
    public function setCurrent(string $current): QueryRelation
    {
        $this->current = $current;
        return $this;
    }
    
    /**
     * Set target
     *
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }
    
    /**
     * Set target
     *
     * @param string $target
     * @return QueryRelation
     */
    public function setTarget(string $target): QueryRelation
    {
        $this->target = $target;
        return $this;
    }
    
    /**
     * Set query
     *
     * @return QueryBuilder
     */
    public function getQuery(): QueryBuilderInterface
    {
        return $this->query;
    }
    
    /**
     * Set query
     *
     * @param QueryBuilderInterface $query
     * @return QueryRelation
     */
    public function setQuery(QueryBuilderInterface $query): QueryRelation
    {
        $this->query = $query;
        return $this;
    }
    
    /**
     * Set alias
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
    
    /**
     * Set alias
     *
     * @param string $alias
     * @return QueryRelation
     */
    public function setAlias(string $alias): QueryRelation
    {
        $this->alias = $alias;
        return $this;
    }
    
    /**
     * Set type
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
    
    /**
     * Set type
     *
     * @param int $type
     * @return QueryRelation
     */
    public function setType(int $type): QueryRelation
    {
        $this->type = $type;
        return $this;
    }
}
