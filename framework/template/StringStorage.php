<?php

class StringStorage extends Storage
{
    /**
     * @{inheritdoc}
     */
    public function getContent()
    {
        return $this->template;
    }
}
