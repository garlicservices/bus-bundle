<?php

namespace MessageBusBundle\Service\Abstracts;


abstract class ProducerAbstract
{
    const QUEUE_SUFFIX = 'queue';
    const NAME_SPACE_SEPARATOR = '.';
    const QUEUE_REPLY_SUFFIX = 'reply';

    /** @var string  */
    protected $currentServiceName;

    /** @var string */
    protected $nameSpace;

    /** @var  string */
    protected $targetServiceName;

    /**
     * ProducerAbstract constructor.
     * @param string $currentServiceName
     * @param string $nameSpace
     */
    public function __construct($currentServiceName, $nameSpace)
    {
        $this->currentServiceName = $currentServiceName;
        $this->nameSpace = $nameSpace;
    }

    /**
     * Create service name with namespace
     *
     * @return string
     */
    protected function getCurrentServiceName()
    {
        return self::generateServiceName(
            $this->currentServiceName,
            $this->nameSpace,
            static::$type
        );
    }

    /**
     * Reply service name
     *
     * @return string
     */
    protected function getReplyServiceName()
    {
        return $this->nameSpace.
            self::NAME_SPACE_SEPARATOR.
            $this->getCurrentServiceName().
            self::NAME_SPACE_SEPARATOR.
            self::QUEUE_REPLY_SUFFIX.
            self::QUEUE_SUFFIX;
    }

    /**
     * Get targetServiceName
     *
     * @return string
     */
    public function getTargetServiceName(): string
    {
        return self::generateServiceName(
            $this->targetServiceName,
            $this->nameSpace,
            static::$type
        );
    }

    /**
     * Set targetServiceName
     *
     * @param string $targetServiceName
     * @return $this
     */
    public function setTargetServiceName(string $targetServiceName)
    {
        $this->targetServiceName = $targetServiceName;

        return $this;
    }

    /**
     * Create service name in right format
     *
     * @param $name
     * @param $namespace
     * @param $type
     * @return string
     */
    public static function generateServiceName($name, $namespace, $type)
    {
        return $namespace.
            self::NAME_SPACE_SEPARATOR.
            $name.
            self::NAME_SPACE_SEPARATOR.
            $type.
            self::NAME_SPACE_SEPARATOR.
            self::QUEUE_SUFFIX;
    }
}