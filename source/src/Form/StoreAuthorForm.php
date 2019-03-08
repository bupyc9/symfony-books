<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class StoreAuthorForm extends AbstractForm
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, ['constraints' => [new NotBlank(), new Length(['max' => 255])]])
            ->add('last_name', TextType::class, ['constraints' => [new NotBlank(), new Length(['max' => 255])]])
            ->add('second_name', TextType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
