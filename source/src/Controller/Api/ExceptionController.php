<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\ErrorDTO;
use App\DTO\ErrorsDTO;
use FOS\RestBundle\Controller\ExceptionController as BaseExceptionController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionController extends BaseExceptionController
{
    protected function createView(\Exception $exception, $code, array $templateData, Request $request, $showException): View
    {
        $headers = [];
        if ($exception instanceof HttpExceptionInterface) {
            $headers = $exception->getHeaders();
        }

        $dto = new ErrorsDTO(new ErrorDTO($code, $exception->getMessage()));
        $view = new View($dto, $code, $headers);
        $view->setTemplateVar('raw_exception');
        $view->setTemplateData($templateData);

        return $view;
    }
}
