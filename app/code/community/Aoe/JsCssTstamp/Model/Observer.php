<?php

class Aoe_JsCssTstamp_Model_Observer
{

    /**
     * Clean media cache after event
     *
     * @return void
     */
    public function clean_media_cache_after()
    {
        Mage::app()->removeCache(Aoe_JsCssTstamp_Model_Package::CACHEKEY);

        // clean files from database
        $dbStorage = Mage::helper('core/file_storage_database')->getStorageDatabaseModel();
        /* @var $dbStorage Mage_Core_Model_File_Storage_Database */
        $dbStorage->getDirectoryModel()
            ->deleteDirectory('js')
            ->deleteDirectory('css');
    }

}