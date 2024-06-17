<?php

return new Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules(
    [
        'Intaro\TwigSandboxBundle\Tests\fixtures\Entity\Product' => [
            'getId', 'getName',
        ],
    ],
    [
    ]
);
