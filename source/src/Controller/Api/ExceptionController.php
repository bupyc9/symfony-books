<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\ErrorDTO;
use App\DTO\ErrorsDTO;
use App\Exception\FormValidationException;
use Exception;
use FOS\RestBundle\Controller\ExceptionController as BaseExceptionController;
use FOS\RestBundle\View\View;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionController extends BaseExceptionController
{
    /**
     * @param Exception $exception
     * @param int       $code
     * @param array     $templateData
     * @param Request   $request
     * @param bool      $showException
     *
     * @throws LogicException
     *
     * @return View
     */
    protected function createView(Exception $exception, $code, array $templateData, Request $request, $showException): View
    {
        $headers = [];
        if ($exception instanceof HttpExceptionInterface) {
            $headers = $exception->getHeaders();
        }

        $errorDTO = new ErrorDTO($code, $exception->getMessage());

        if ($exception instanceof FormValidationException) {
            $errorDTO->setErrors($exception->errors());
        }

        $dto = new ErrorsDTO($errorDTO);
        $view = new View($dto, $code, $headers);
        $view->setTemplateVar('raw_exception');
        $view->setTemplateData($templateData);

        return $view;
    }
}
