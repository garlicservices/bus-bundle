<?php
    
namespace Garlic\Bus\Service\GraphQL;

class QueryGenerator 
{
    /**
     * Return arguments string
     *
     * @param array $arguments
     * @return string
     */
    public function createArguments(array $arguments)
    {
        $result = [];
        foreach ($arguments as $name => $argument) {
            if(is_array($argument)) {
                $result[$name] = "$name: { " . $this->createArguments($argument) . " }";
            } elseif(is_int($argument)) {
                $result[] = "$name: $argument";
            } else {
                $result[] = "$name: \"$argument\"";
            }
        }
        
        return implode(',', $result);
    }
    
    /**
     * Create fields
     *
     * @param array $fields
     * @return string
     */
    public function createFields(array $fields)
    {
        foreach ($fields as $name => $field) {
            if(is_array($field)) {
                $result[$name] = "$name { ".$this->createFields($field)." }";
            } else {
                $result[] = $field;
            }
        }
    
        return implode(',', $result);
    }
}