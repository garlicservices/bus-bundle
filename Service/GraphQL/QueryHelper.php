<?php
    
namespace Garlic\Bus\Service\GraphQL;


class QueryHelper
{
    /**
     * Parse query name
     *
     * @return array
     */
    public function parsQueryName(string $from): array 
    {
        $meta = explode('.', $from);
        if(count($meta) < 2) {
            throw new GraphQLQueryException('From part of query must contains service and query name separated by dot');
        }
        
        return [
            'service' => $meta[0],
            'query' => $meta[1],
        ];
    }
}