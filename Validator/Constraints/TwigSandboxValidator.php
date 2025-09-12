<?php

namespace Intaro\TwigSandboxBundle\Validator\Constraints;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Intaro\TwigSandboxBundle\Builder\TwigAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Sandbox\SecurityError;

class TwigSandboxValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EnvironmentBuilder $builder,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getTwig(array $params = []): TwigAdapter
    {
        return $this->builder->getSandboxEnvironment($params);
    }

    /**
     * @param TwigSandbox $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        $twig = $this->getTwig([
            'strict_variables' => $constraint->strict,
        ]);

        $vars = [];
        foreach ($constraint->vars as $name => $className) {
            if (!class_exists($className)) {
                throw new \RuntimeException(sprintf('Class "%s" does not exist', $className));
            }
            $vars[$name] = new $className();
        }

        try {
            $twig->render((string) $value, $vars);
        } catch (SecurityError|SyntaxError|RuntimeError $e) {
            $message = mb_strlen($e->getMessage()) > 150 ? mb_substr($e->getMessage(), 0, 150) . '…' : $e->getMessage();

            $this->context->addViolation($constraint->message, [
                '{{ syntax_error }}' => $message,
            ]);
        } catch (\Error|\Exception) {
            $this->context->addViolation($constraint->criticalErrorMessage);
        }
    }
}
