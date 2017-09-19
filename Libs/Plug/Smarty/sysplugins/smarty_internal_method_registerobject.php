<?php

/**
 * Smarty Method RegisterObject.
 *
 * Smarty::registerObject() method
 *
 * @author     Uwe Tews
 */
class Smarty_Internal_Method_RegisterObject
{
    /**
     * Valid for Smarty and template object.
     *
     * @var int
     */
    public $objMap = 3;

    /**
     * Registers object to be used in templates.
     *
     * @api  Smarty::registerObject()
     *
     * @link http://www.smarty.net/docs/en/api.register.object.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $object_name
     * @param object                                                          $object                     the
     *                                                                                                    referenced
     *                                                                                                    PHP object to
     *                                                                                                    register
     * @param array                                                           $allowed_methods_properties list of
     *                                                                                                    allowed
     *                                                                                                    methods
     *                                                                                                    (empty = all)
     * @param bool                                                            $format                     smarty
     *                                                                                                    argument
     *                                                                                                    format, else
     *                                                                                                    traditional
     * @param array                                                           $block_methods              list of
     *                                                                                                    block-methods
     *
     * @throws \SmartyException
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function registerObject(Smarty_Internal_TemplateBase $obj, $object_name, $object, $allowed_methods_properties = [], $format = true, $block_methods = [])
    {
        $smarty = isset($obj->smarty) ? $obj->smarty : $obj;
        // test if allowed methods callable
        if (!empty($allowed_methods_properties)) {
            foreach ((array) $allowed_methods_properties as $method) {
                if (!is_callable([$object, $method]) && !property_exists($object, $method)) {
                    throw new SmartyException("Undefined method or property '$method' in registered object");
                }
            }
        }
        // test if block methods callable
        if (!empty($block_methods)) {
            foreach ((array) $block_methods as $method) {
                if (!is_callable([$object, $method])) {
                    throw new SmartyException("Undefined method '$method' in registered object");
                }
            }
        }
        // register the object
        $smarty->registered_objects[$object_name] = [$object, (array) $allowed_methods_properties,
                                                          (bool) $format, (array) $block_methods, ];

        return $obj;
    }
}
