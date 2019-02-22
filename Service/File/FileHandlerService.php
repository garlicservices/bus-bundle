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
     * @var int files will be deleted after this time(seconds)
     */
    private $fileHandleTime;

    /**
     * FileHandlerService constructor.
     *
     * @param     $hostUrl
     * @param     $uploadDir
     * @param int $timeToDelete
     */
    public function __construct($hostUrl, $uploadDir = null, $timeToDelete = 1800)
    {
        $this->hostUrl = $hostUrl;
        $this->uploadDir = $uploadDir ?? getenv('DOCUMENT_ROOT') . "/upload/";
        $this->fileHandleTime = $timeToDelete;
    }

    /**
     * @param array $files
     *
     * @return array
     * @throws FileUploadException
     */
    public function handleFiles(array $files): array
    {
        $this->removeOldFiles();
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
                        'extension'   => $extension
                    ];
                } else {
                    throw new FileUploadException('File upload error');
                }
            }
        }

        return $metadata;
    }

    /**
     * removes files that created before $fileHandleTime
     */
    private function removeOldFiles()
    {
        if ($handle = opendir($this->uploadDir)) {

            while (false !== ($file = readdir($handle))) {
                if (!is_file($file)) {
                    continue;
                }
                if (filectime($this->uploadDir . $file)< (time() - $this->fileHandleTime)) {
                    unlink($this->uploadDir . $file);
                }
            }
        }
    }
}
