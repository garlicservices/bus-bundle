<?php

namespace Garlic\Bus\Service\GraphQL\Mutation;

use Dflydev\DotAccessData\Data;
use Garlic\Bus\Service\GraphQL\AbstractQueryBuilder;
use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;
use Garlic\Bus\Service\GraphQL\QueryBuilderInterface;
use Garlic\Bus\Service\GraphQL\WhereQueryTrait;

class MutationBuilder extends AbstractQueryBuilder implements QueryBuilderInterface
{
    use WhereQueryTrait;
    
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
            throw new GraphQLQueryException('Query must contains at least one selected field. Use method select() to set it.');
        }
        
        if(count($this->arguments) <= 0) {
            throw new GraphQLQueryException('Mutation must contains at least one argument. Use method where() to set them.');
        }
        
        return "mutation {".$this->query." (".$this->createArguments($this->arguments).") {".$this->createFields($this->fields)."}}";
    }
}
