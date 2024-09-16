<?php

namespace Intaro\TwigSandboxBundle\Validator\Constraints;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Intaro\TwigSandboxBundle\Builder\TwigAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TwigSandboxValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EnvironmentBuilder $builder,
    ) {
    }

    public function getTwig(): TwigAdapter
    {
        return $this->builder->getSandboxEnvironment();
    }

    /**
     * @param TwigSandbox $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        $twig = $this->getTwig();

        try {
            $twig->createTemplate((string) $value);
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
        } catch (\Error|\Exception) {
            $this->context->addViolation($constraint->criticalErrorMessage);
        }
    }
}
