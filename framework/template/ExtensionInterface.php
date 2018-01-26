<?php

interface ExtensionInterface
{
    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Initializes the extension.
     */
    public function initialize(Engine $engine);
}
