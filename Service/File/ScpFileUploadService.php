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
        $this->uploadDir = $uploadDir;
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
        $connection = $this->getSshConnection($metadata);
        $filePath = $this->uploadDir.md5(time().$metadata['origin_name']);
        try {
            ssh2_scp_recv($connection, $metadata['path'], $filePath);
        } catch (\Exception $e) {
            throw new \HttpException($e->getMessage(), $e->getCode());
        }
        if (!file_exists($filePath)) {
            throw new FileUploadException($metadata['path']);
        }

        return $filePath;
    }

    /**
     * Get ssh connection to host with file
     *
     * @param $metadata
     *
     * @return resource
     * @throws \Exception
     */
    private function getSshConnection($metadata)
    {
        $connection = ssh2_connect($metadata['host_url'], 22);
        if (ssh2_auth_password($connection, $this->username, $this->password)) {
        } else {
            throw new \Exception('Authentication Failed');
        }

        return $connection;
    }
}
