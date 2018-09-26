<?php

namespace Garlic\Bus\Service\GraphQL;

use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;

class QueryBuilder extends QueryGenerator
{
    /**
     * @var array
     */
    private $arguments = [];
    
    /**
     * @var string
     */
    private $query;
    
    /**
     * @var array
     */
    private $fields = [];
    
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
        $this->fields = $selects;
        
        return $this;
    }
    
    /**
     * Add select
     *
     * @param string $name
     * @param $value
     * @return QueryBuilder
     */
    public function addSelect(string $name, $value): QueryBuilder
    {
        $this->fields[$name] = $value;
        
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
    public function where(array $arguments): QueryBuilder
    {
        $this->arguments = array_merge(
            $this->arguments,
            $arguments
        );
        
        return $this;
    }
    
    /**
     * Add argument to the query
     *
     * @param string $name
     * @param $value
     * @return QueryBuilder
     */
    public function addWhere(string $name, $value): QueryBuilder
    {
        $this->arguments[$name] = $value;
        
        return $this;
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
}
