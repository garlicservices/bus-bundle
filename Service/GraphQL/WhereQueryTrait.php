<?php

namespace Garlic\Bus\Service\GraphQL;


trait WhereQueryTrait
{
    /** @var array */
    protected $arguments = [];
    
    /**
     * Add arguments for searche
     *
     * @param array $arguments
     * @return $this
     */
    public function where($arguments = null)
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
}