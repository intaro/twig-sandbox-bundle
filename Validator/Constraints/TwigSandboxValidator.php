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

    /**
     * @param \Intaro\TwigSandboxBundle\Validator\Constraints\TwigSandbox $constraint
     */
     public function validate($value, Constraint $constraint)
     {
         if (!$value) {
             return;
         }

         $twig = $this->getTwig();

         try {
             $twig->createTemplate($value);
         }
         catch (\Twig\Sandbox\SecurityError $e) {
             $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

             $this->context->addViolation($constraint->message, array(
                '{{ syntax_error }}' => $message,
             ));
         }
         catch (\Twig\Error\SyntaxError $e) {
             $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

             $this->context->addViolation($constraint->message, array(
                '{{ syntax_error }}' => $message,
             ));
         }
         catch (\Error $e) {
             goto ex_r;
         }
         catch (\Exception $e) {
             ex_r:

             $this->context->addViolation($constraint->criticalErrorMessage);
         }
     }
}
