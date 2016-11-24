<?php

/**
 * Generic JS/CSS Container
 * (e.g. for scripts added to the end of the body)
 *
 * @author Fabrizio Branca
 * @since 2011-12-08
 */
class Aoe_JsCssTstamp_Block_JsCssContainer extends Aoe_JsCssTstamp_Block_Head
{

    /**
     * We don't need a template here.
     * Bypassing the parent's _toHtml() method and rendering the JS/CSS content right away
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getCssJsHtml();
    }

}
