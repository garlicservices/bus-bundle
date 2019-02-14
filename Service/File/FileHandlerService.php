<?php

namespace Garlic\Bus\Service\File;

use Garlic\Bus\Exceptions\FileUploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    public function __construct($hostUrl, $uploadDir = null)
    {
        $this->hostUrl = $hostUrl;
        $this->uploadDir = $uploadDir ?? getenv('DOCUMENT_ROOT') . "/upload/";
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
            /**@var $file  UploadedFile */
            if ($file->getError() == UPLOAD_ERR_OK) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                /**@var $newFile  File */
                if ($newFile = $file->move($this->uploadDir, md5(time().basename($originalName)) . '.' . $extension)) {
                    $metadata[$originalName] = [
                        'host_url'    => $this->hostUrl,
                        'path'        => $newFile->getPathname(),
                        'origin_name' => $originalName,
                        'type'        => $newFile->getMimeType(),
                        'size'        => $newFile->getSize(),
                    ];
                } else {
                    throw new FileUploadException('File upload error');
                }
            }
        }

        return $metadata;
    }
}
