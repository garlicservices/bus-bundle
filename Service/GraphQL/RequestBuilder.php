<?php

namespace Garlic\Bus\Service\GraphQL;


class RequestBuilder
{
    /**
     * @var
     */
    private $queries = [];
    
    /**
     * Add query name that will be used on requested service
     *
     * @return QueryBuilder
     */
    public function createQuery(string $from): QueryBuilder
    {
        $qid = uniqid('gqid', true);
        $this->queries[$qid] = new QueryBuilder($from);

        return $this->queries[$qid];
    }
    
    /**
     * Return array of queries in a request
     *
     * @return array
     * @throws Exceptions\GraphQLQueryException
     */
    public function getQueries(): array
    {
        $queries = [];
        /** @var QueryBuilder $queryBuilder */
        foreach ($this->queries as $queryBuilder) {
            $queries[] = $queryBuilder->getQuery();
        }
        
        return $queries;
    }
}