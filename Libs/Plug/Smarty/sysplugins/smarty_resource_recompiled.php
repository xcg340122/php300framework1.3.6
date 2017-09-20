<?php
/**
 * Smarty Resource Plugin.
 *
 * @author     Rodney Rehm
 */

/**
 * Smarty Resource Plugin
 * Base implementation for resource plugins that don't compile cache.
 */
abstract class Smarty_Resource_Recompiled extends Smarty_Resource
{
    /**
     * Flag that it's an recompiled resource.
     *
     * @var bool
     */
    public $recompiled = true;

    /**
     * Resource does implement populateCompiledFilepath() method.
     *
     * @var bool
     */
    public $hasCompiledHandler = true;

    /**
     * populate Compiled Object with compiled filepath.
     *
     * @param Smarty_Template_Compiled $compiled  compiled object
     * @param Smarty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populateCompiledFilepath(Smarty_Template_Compiled $compiled, Smarty_Internal_Template $_template)
    {
        $compiled->filepath = false;
        $compiled->timestamp = false;
        $compiled->exists = false;
    }
}
