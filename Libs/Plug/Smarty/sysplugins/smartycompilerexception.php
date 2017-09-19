<?php

/**
 * Smarty compiler exception class.
 */
class smartycompilerexception extends SmartyException
{
    public function __toString()
    {
        return ' --> Smarty Compiler: '.$this->message.' <-- ';
    }

    /**
     * The line number of the template error.
     *
     * @var int|null
     */
    public $line = null;
    /**
     * The template source snippet relating to the error.
     *
     * @var string|null
     */
    public $source = null;
    /**
     * The raw text of the error message.
     *
     * @var string|null
     */
    public $desc = null;
    /**
     * The resource identifier or template name.
     *
     * @var string|null
     */
    public $template = null;
}
