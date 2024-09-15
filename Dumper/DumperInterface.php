<?php

namespace Intaro\TwigSandboxBundle\Dumper;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;

interface DumperInterface
{
    public function dump(SecurityPolicyRules $rules): string;
}
