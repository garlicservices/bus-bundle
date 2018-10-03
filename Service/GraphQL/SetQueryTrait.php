<?php

namespace Garlic\Bus\Service\GraphQL;

trait SetQueryTrait
{
    /** @var array  */
    protected $values = [];
    
    /**
     * Set arguments for update
     *
     * @param null $arguments
     * @return $this
     */
    public function set($arguments = null)
    {
        if(!is_array($arguments)) {
            $arguments = func_get_args();
            $arguments = $this->prepareArguments($arguments);
        }
    
        $this->values = array_merge(
            $this->values,
            $arguments
        );
        
        return $this;
    }
}