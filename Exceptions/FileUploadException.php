<?php


namespace Garlic\Bus\Exceptions;


use Throwable;

/**
 * Class FileNotFoundException
 *
 * @package Garlic\Bus\Exceptions
 */
class FileUploadException extends \Exception
{
    /**
     * @var string
     */
    private $fileName;


    /**
     * FileNotFoundException constructor.
     *
     * @param string         $fileName
     * @param Throwable|null $previous
     */
    public function __construct(string $fileName, Throwable $previous = null)
    {
        $this->fileName = $fileName;
        parent::__construct("File $fileName not found", 404, $previous);
    }
}