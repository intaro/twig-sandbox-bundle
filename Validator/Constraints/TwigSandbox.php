<?php

namespace Intaro\TwigSandboxBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TwigSandbox extends Constraint
{
    public string $message = 'This value is not a valid Twig template. The parsing error is: {{ syntax_error }}';
    public string $criticalErrorMessage = 'Critical error occurred while rendering the template. Please check the correctness of template syntax.';

    public function validatedBy(): string
    {
        return 'twig_sandbox';
    }
}
