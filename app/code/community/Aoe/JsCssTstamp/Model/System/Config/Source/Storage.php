<?php

class Aoe_JsCssTstamp_Model_System_Config_Source_Storage
{

    CONST DATABASE = 'db';
    CONST FILESYSTEM = 'file';
    CONST CDN = 'CDN';

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
            array('value' => self::CDN, 'label' => Mage::helper('aoejscsststamp')->__('CDN')),
        );
    }

}
