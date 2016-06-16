<?php

namespace Intaro\TwigSandboxBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;

class TwigSandboxValidator extends ConstraintValidator
{
    private $builder;

    public function __construct(EnvironmentBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getTwig()
    {
        return $this->builder->getSandboxEnvironment();
    }

     public function validate($value, Constraint $constraint)
     {
         if (!$value) {
             return;
         }

         $twig = $this->getTwig();

         try {
             $twig->render($value);
         }
         catch (\Twig_Sandbox_SecurityError $e) {
             $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

             $this->context->addViolation($constraint->message, array(
                '{{ syntax_error }}' => $message,
             ));
         }
         catch (\Twig_Error_Syntax $e) {
             $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

             $this->context->addViolation($constraint->message, array(
                '{{ syntax_error }}' => $message,
             ));
         }
     }
}