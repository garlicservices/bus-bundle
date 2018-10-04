<?php

namespace Garlic\Bus\Service\GraphQL;

use Dflydev\DotAccessData\Data;
use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;
use Garlic\Bus\Service\GraphQL\Query\QueryBuilder;

abstract class AbstractQueryBuilder extends QueryGenerator
{
    /** @var string */
    protected $query;
    
    /** @var array */
    protected $fields = [];
    
    /** @var array  */
    protected $stitches = [];
    
    /** @var string */
    protected $result;
    
    /**
     * QueryBuilder constructor.
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
    }
    
    /**
     * Add query fields to select
     *
     * @param null $select
     * @return $this
     */
    public function select($select = null)
    {
        $selects = is_array($select) ? $select : func_get_args();
        $this->fields = $this->prepareFields($selects);
        
        return $this;
    }
    
    /**
     * Set current query name
     *
     * @param $name
     * @return $this
     */
    public function from($name)
    {
        $this->query = $name;
        
        return $this;
    }
    
    /**
     * Stitch a query to the current query
     *
     * @param QueryBuilderInterface $query
     * @param string $alias
     * @param string $current
     * @param string $target
     * @param int $type
     * @return $this
     */
    public function stitch(
        QueryBuilderInterface $query,
        string $alias,
        string $current,
        string $target,
        int $type = QueryRelation::TYPE_ONE
    ){
        $relation = new QueryRelation();
        $relation->setQuery($query)
            ->setAlias($alias)
            ->setCurrent($current)
            ->setTarget($target)
            ->setType($type);
        
        $this->stitches[] = $relation;
        
        return $this;
    }
    
    /**
     * Make One to One relation between queries
     * Stitch first found result
     *
     * @param QueryBuilderInterface $query
     * @param string $alias
     * @param string $currentField
     * @param string $targetField
     * @return $this
     */
    public function stitchOne(
        QueryBuilderInterface $query,
        string $alias,
        string $currentField,
        string $targetField
    ){
        return $this->stitch($query, $alias, $currentField, $targetField, QueryRelation::TYPE_ONE);
    }
    
    /**
     * Make One to Many relation between queries
     * Stitch list of results
     *
     * @param QueryBuilderInterface $query
     * @param string $alias
     * @param string $currentField
     * @param string $targetField
     * @return $this
     */
    public function stitchMany(
        QueryBuilderInterface $query,
        string $alias,
        string $currentField,
        string $targetField
    ) {
        return $this->stitch($query, $alias, $currentField, $targetField, QueryRelation::TYPE_MANY);
    }
    
    /**
     * Returns list of stitched queries
     *
     * @return array
     */
    public function getStitched()
    {
        return $this->stitches;
    }
    
    /**
     * Set query result
     *
     * @param $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        
        return $this;
    }
    
    /**
     * Get "dot path" oriented result
     * Example of path: parentArrayKey.childArrayKey
     *
     * @return Data
     */
    public function getResult()
    {
        return new Data($this->result);
    }
    
    /**
     * Return query result as array
     *
     * @return string
     */
    public function getArrayResult()
    {
        return $this->result;
    }
    
    /**
     * Create query as a string
     *
     * @return string
     * @throws GraphQLQueryException
     */
    abstract public function getQuery(): string;
    
    /**
     * Return object as string
     *
     * @return string
     * @throws GraphQLQueryException
     */
    public function __toString()
    {
        return $this->getQuery();
    }
}
