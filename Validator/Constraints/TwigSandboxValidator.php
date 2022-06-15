<?php

namespace Intaro\TwigSandboxBundle\Validator\Constraints;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
    public function validate($value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        $twig = $this->getTwig();

        try {
            $twig->createTemplate($value);
        } catch (\Twig\Sandbox\SecurityError $e) {
            $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

            $this->context->addViolation($constraint->message, [
                '{{ syntax_error }}' => $message,
             ]);
        } catch (\Twig\Error\SyntaxError $e) {
            $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

            $this->context->addViolation($constraint->message, [
                '{{ syntax_error }}' => $message,
             ]);
        } catch (\Error $e) {
            goto ex_r;
        } catch (\Exception $e) {
            ex_r:

             $this->context->addViolation($constraint->criticalErrorMessage);
        }
    }
}
