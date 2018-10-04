<?php

namespace Garlic\Bus\Service\GraphQL;

use Dflydev\DotAccessData\Data;
use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;

class QueryBuilder extends QueryGenerator
{
    /** @var array */
    private $arguments = [];
    
    /** @var string */
    private $query;
    
    /** @var array */
    private $fields = [];
    
    /** @var array  */
    private $stitches = [];
    
    /** @var string */
    private $result;
    
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
     * @return QueryBuilder
     */
    public function select($select = null): QueryBuilder
    {
        $selects = is_array($select) ? $select : func_get_args();
        $this->fields = $this->prepareFields($selects);
        
        return $this;
    }
    
    /**
     * Set current query name
     *
     * @param $name
     * @return QueryBuilder
     */
    public function from($name): QueryBuilder
    {
        $this->query = $name;
        
        return $this;
    }
    
    /**
     * Add arguments array to argument stack
     *
     * @param array $arguments
     * @return QueryBuilder
     */
    public function where($arguments = null): QueryBuilder
    {
        if(!is_array($arguments)) {
            $arguments = func_get_args();
            $arguments = $this->prepareArguments($arguments);
        }
        
        $this->arguments = array_merge(
            $this->arguments,
            $arguments
        );
        
        return $this;
    }
    
    /**
     * Stitch a query to the current query
     *
     * @param QueryBuilder $query
     * @param string $alias
     * @param string $current
     * @param string $target
     * @param int $type
     * @return QueryBuilder
     */
    public function stitch(
        QueryBuilder $query,
        string $alias,
        string $current,
        string $target,
        int $type = QueryRelation::TYPE_ONE
    ): QueryBuilder
    {
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
     * @param QueryBuilder $query
     * @param string $alias
     * @param string $currentField
     * @param string $targetField
     * @return QueryBuilder
     */
    public function stitchOne(QueryBuilder $query, string $alias, string $currentField, string $targetField)
    {
        return $this->stitch($query, $alias, $currentField, $targetField, QueryRelation::TYPE_ONE);
    }
    
    /**
     * Make One to Many relation between queries
     * Stitch list of results
     *
     * @param QueryBuilder $query
     * @param string $alias
     * @param string $currentField
     * @param string $targetField
     * @return QueryBuilder
     */
    public function stitchMany(QueryBuilder $query, string $alias, string $currentField, string $targetField)
    {
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
    public function setResult($result): QueryBuilder
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
     * Return query result
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
    public function getQuery(): string
    {
        if(empty($this->query)) {
            throw new GraphQLQueryException('Select section not found in the query.');
        }
        
        if(count($this->fields) <= 0) {
            throw new GraphQLQueryException('Query myst contains at least one requested field. Use method select() to set it.');
        }
        
        return "{".$this->query."(".$this->createArguments($this->arguments)."){".$this->createFields($this->fields)."}}";
    }
    
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
