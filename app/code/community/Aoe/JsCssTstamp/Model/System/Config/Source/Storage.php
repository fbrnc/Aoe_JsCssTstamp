<?php

class Aoe_JsCssTstamp_Model_System_Config_Source_Storage
{
    /**@+
     * Supported mode codes
     *
     * @var string
     */
    const DATABASE   = 'db';
    const FILESYSTEM = 'file';
    /**@-*/

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::DATABASE, 'label' => Mage::helper('aoejscsststamp')->__('Database')),
            array('value' => self::FILESYSTEM, 'label' => Mage::helper('aoejscsststamp')->__('Filesystem')),
        );
    }
}
