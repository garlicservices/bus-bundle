<?php

namespace Garlic\Bus\Service\File;

use Garlic\Bus\Exceptions\FileUploadException;

/**
 * Class FileHandlerService
 *
 * @package Garlic\Bus\Service\File
 */
class FileHandlerService
{
    /**
     * @var string
     */
    private $hostUrl;
    /**
     * @var string
     */
    private $uploadDir;

    /**
     * FileHandlerService constructor.
     *
     * @param $hostUrl
     * @param $uploadDir
     */
    public function __construct($hostUrl, $uploadDir)
    {
        $this->hostUrl = $hostUrl;
        $this->uploadDir = $uploadDir;
    }

    /**
     * @param array $files
     *
     * @return array
     * @throws FileUploadException
     */
    public function handleFiles(array $files): array
    {
        $metadata = [];
        foreach ($files as $file) {
            if ($file['error'] == UPLOAD_ERR_OK) {
                $filepath = getenv('UPLOAD_DIR') . md5(time() . basename($file["name"]));
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $metadata['origin_name'] = [
                        'host_url'      => $this->hostUrl,
                        'path'          => $filepath,
                        'origin_name'   => $file['name'],
                        'type'          => $file['type'],
                        'size'          => $file['size']
                    ];
                } else {
                    throw new FileUploadException('File upload error');
                }
            }
        }

        return $metadata;
    }
}
