<?php

namespace Intaro\TwigSandboxBundle\Tests\fixtures\Entity;

use Intaro\TwigSandboxBundle\Attribute\Sandbox;

class Product
{
    private $id;
    private $name;
    private $quantity;

    /**
     * Get id
     *
     * @return int
     */
    #[Sandbox(type: 'int')]
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    #[Sandbox]
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set quantity
     *
     * @param bool $quantity
     *
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
