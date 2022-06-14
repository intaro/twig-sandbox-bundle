<?php

namespace Intaro\TwigSandboxBundle\Dumper;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;

class PhpDumper implements DumperInterface
{
    public function dump(SecurityPolicyRules $rules)
    {
        $result = "<?php\n";
        $result .= "return new Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules(\n";
        $result .= "    array(\n";

        foreach ($rules->getMethods() as $entity => $methods) {
            $result .= "        '$entity' => array(\n";
            if (count($methods) > 0) {
                $result .= "            '" . implode("', '", $methods) . "'\n";
            }
            $result .= "        ),\n";
        }

        $result .= "    ),\n";
        $result .= "    array(\n";

        foreach ($rules->getProperties() as $entity => $properties) {
            $result .= "        '$entity' => array(\n";
            if (count($properties) > 0) {
                $result .= "            '" . implode("', '", $properties) . "'\n";
            }
            $result .= "        ),\n";
        }

        $result .= "    )\n";
        $result .= ");\n";

        return $result;
    }
}
