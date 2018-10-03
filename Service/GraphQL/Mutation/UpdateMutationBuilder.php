<?php

namespace Garlic\Bus\Service\GraphQL\Mutation;

use Dflydev\DotAccessData\Data;
use Garlic\Bus\Service\GraphQL\AbstractQueryBuilder;
use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;
use Garlic\Bus\Service\GraphQL\SetQueryTrait;
use Garlic\Bus\Service\GraphQL\WhereQueryTrait;

class UpdateMutationBuilder extends AbstractQueryBuilder
{
    use SetQueryTrait, WhereQueryTrait;
    
    /**
     * Create query as a string
     *
     * @return string
     * @throws GraphQLQueryException
     */
    public function getQuery(): string
    {
        if(empty($this->query)) {
            throw new GraphQLQueryException('Select section not found in the the mutation.');
        }
        
        if(count($this->fields) <= 0) {
            throw new GraphQLQueryException('Mutation must contains at least one selected field. Use method select() to set it.');
        }
        
        if(count($this->values) <= 0) {
            throw new GraphQLQueryException('Updates mutation must contains at least one field to update. Use method set() to set it.');
        }
        
        if(count($this->arguments) <= 0) {
            throw new GraphQLQueryException('Updates mutation must contains at least one argument. Use method where() to set it.');
        }
        
        return "mutation {".$this->query."(arguments: {".$this->createArguments($this->arguments)."}, values: {".$this->createArguments($this->values)."}){".$this->createFields($this->fields)."}}";
    }
}
