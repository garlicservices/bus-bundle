<?php

namespace Garlic\Bus\Service\File;

use Garlic\Bus\Exceptions\FileUploadException;

/**
 * Class ScpFileUploadService
 *
 * @package Garlic\Bus\Service\File
 */
class ScpFileUploadService
{
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $uploadDir;

    /**
     * ScpFileUploadService constructor.
     *
     * @param $username
     * @param $password
     * @param $uploadDir
     */
    public function __construct($username, $password, $uploadDir)
    {
        $this->username = $username;
        $this->password = $password;
        $this->uploadDir = $uploadDir ?? getenv('DOCUMENT_ROOT') . "/upload/";
    }

    /**
     * @param array $metadata
     *
     * @return string
     * @throws FileUploadException
     * @throws \HttpException
     */
    public function getFile(array $metadata)
    {
        $filePath = $this->uploadDir.md5(time().$metadata['origin_name']).'.'.$metadata['extension'];
        try {
            exec('sshpass -p "'. getenv('SCP_PASSWORD').'" scp -v  '.getenv('SCP_USERNAME').'@'.$metadata['host_url'].':'.$metadata['path'].' ' . $filePath);
        } catch (\Exception $e) {
            throw new \HttpException($e->getMessage(), $e->getCode());
        }
        if (!file_exists($filePath)) {
            throw new FileUploadException($metadata['path']);
        }

        return $filePath;
    }
}
