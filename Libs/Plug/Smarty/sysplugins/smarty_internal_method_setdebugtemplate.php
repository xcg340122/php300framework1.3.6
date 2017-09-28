<?php

/**
 * Smarty Method SetDebugTemplate.
 *
 * Smarty::setDebugTemplate() method
 *
 * @author     Uwe Tews
 */
class Smarty_Internal_Method_SetDebugTemplate
{
    /**
     * Valid for Smarty and template object.
     *
     * @var int
     */
    public $objMap = 3;

    /**
     * set the debug template.
     *
     * @api Smarty::setDebugTemplate()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $tpl_name
     *
     * @throws SmartyException if file is not readable
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function setDebugTemplate(Smarty_Internal_TemplateBase $obj, $tpl_name)
    {
        $smarty = isset($obj->smarty) ? $obj->smarty : $obj;
        if (!is_readable($tpl_name)) {
            throw new SmartyException("Unknown file '{$tpl_name}'");
        }
        $smarty->debug_tpl = $tpl_name;

        return $obj;
    }
}
