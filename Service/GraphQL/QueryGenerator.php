<?php
    
namespace Garlic\Bus\Service\GraphQL;

use Dflydev\DotAccessData\Data;

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
    
    /**
     * Prepare query arguments
     *
     * @param array $arguments
     * @return array
     */
    public function prepareArguments(array $arguments): array
    {
        foreach ($arguments as $key => $incoming){
            $argument = explode('=', $incoming);
        
            switch (true) {
                case (is_numeric($argument[1])):
                    $arguments[trim($argument[0])] = (int)$argument[1];
                    break;
            
                case ($this->checkBool($argument[1])):
                    $arguments[trim($argument[0])] = (boolean)$argument[1];
                    break;
            
                default:
                    $arguments[trim($argument[0])] = trim($argument[1]);
            }
        
            unset($arguments[$key]);
        }
    
        $argumetsData = new Data();
        foreach ($arguments as $key => $value) {
            $argumetsData->set($key, $value);
        }
    
        return $argumetsData->export();
    }
    
    /**
     * Conver field names from dotted to array type
     *
     * @param array $fields
     * @return mixed
     */
    public function prepareFields(array $fields)
    {
        $data = new Data();
        foreach ($fields as $value) {
            $data->set($value);
        }
    
        $result = $data->export();
        array_walk_recursive($result, function(&$item, $key){
            if(empty($item) && !empty($key)) {
                $item = $key;
            }
        });
        
        return $result;
    }
    
    /**
     * Check if string is boolean
     *
     * @param $string
     * @return bool
     */
    private function checkBool($string)
    {
        return (in_array(strtolower($string), ["true", "false", "1", "0", "yes", "no"], true));
    }
}