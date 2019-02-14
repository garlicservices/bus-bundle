<?php

namespace Garlic\Bus\Entity\Traits;

use Garlic\Bus\Service\File\FileHandlerService;
use Doctrine\Common\Annotations\Annotation\Required;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait FileHandlerTrait
 *
 * @package Garlic\Bus\Entity\Traits
 */
trait FileHandlerTrait
{
    /**
     * @var FileHandlerService
     */
    private $handlerService;

    /**
     * @param FileHandlerService $handlerService
     * @required
     */
    public function setHandlerService(FileHandlerService $handlerService): void
    {
        $this->handlerService = $handlerService;
    }

    /**
     * check files in request, handle it to upload folder and add meta-data to headers
     * @param Request $request
     *
     * @throws \Garlic\Bus\Exceptions\FileUploadException
     */
    public function handleFiles(Request &$request)
    {
        $files = $request->files->all();
        if(!empty($files)) {
            $metadata = $this->handlerService->handleFiles($files);
            $request->headers->add(['file-meta-data' => $metadata]);
        }
    }
}