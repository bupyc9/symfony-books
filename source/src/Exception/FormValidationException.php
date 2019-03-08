<?php

declare(strict_types=1);

namespace App\Exception;

use LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormValidationException extends HttpException
{
    /**
     * @var FormInterface
     */
    private $form;

    public function __construct(FormInterface $form, \Exception $previous = null, ?int $code = 0)
    {
        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, 'Validation error', $previous, [], $code);
        $this->form = $form;
    }

    /**
     * @throws LogicException
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->getErrorsMessage($this->form);
    }

    /**
     * @param FormInterface $form
     *
     * @throws LogicException
     *
     * @return array
     */
    protected function getErrorsMessage(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $name => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $name => $child) {
            if ($child->isSubmitted() && $child->isValid()) {
                continue;
            }

            $child->count() > 0 && $errors[$name] = $this->getErrorsMessage($child);

            foreach ($child->getErrors() as $error) {
                $errors[$name][] = $error->getMessage();
            }
        }

        return $errors;
    }
}
