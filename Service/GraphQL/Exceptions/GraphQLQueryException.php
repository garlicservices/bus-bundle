<?php
    
    namespace Garlic\Bus\Service\GraphQL\Exceptions;
    
    class GraphQLQueryException extends \Exception
    {
        /**
         * GraphQLQueryException constructor.
         * @param $message
         * @param $code
         */
        public function __construct($message, $code = 500)
        {
            $this->message = "GrapghQL Query Error: $message";
            $this->code = $code;
        }
    }