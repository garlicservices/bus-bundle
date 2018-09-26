<?php

namespace Garlic\Bus\Service;

use Garlic\Bus\Service\GraphQL\RequestBuilder;

class GraphQLService
{
    /**
     * @var array
     */
    private $requests = [];
    
    /**
     * @var CommunicatorService CommunicatorService
     */
    private $communicatorService;
    
    /**
     * GraphQLService constructor.
     * @param CommunicatorService $communicatorService
     */
    public function __construct(CommunicatorService $communicatorService)
    {
        $this->communicatorService = $communicatorService;
    }
    
    /**
     * Create GraphQlQueryBuilder
     *
     * @param $serviceName
     * @return RequestBuilder
     */
    public function createRequestBuilder($serviceName)
    {
        $this->requests[$serviceName] = new RequestBuilder();
        
        return $this->requests[$serviceName];
    }
    
    /**
     * Execute queries and returns received data
     *
     * @return array
     */
    public function fetch(): array
    {
        $data = [];
        
        /**
         * @var string $serviceName
         * @var RequestBuilder $request
         */
        foreach ($this->requests as $serviceName => $request){
            $data[$serviceName] = $this->communicatorService
                ->request($serviceName)
                ->graphql([], ['query' => implode("\n", $request->getQueries())])
                ->getData()
            ;
        }
        
        return $data;
    }
}