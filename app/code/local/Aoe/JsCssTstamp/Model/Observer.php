<?php

class Aoe_JsCssTstamp_Model_Observer {

	/**
	 * Clean media cache after event
	 *
	 * @return void
	 */
	public function clean_media_cache_after() {
		// TODO: clean files from databasem
		Mage::app()->removeCache(Aoe_JsCssTstamp_Model_Package::CACHEKEY);
	}

}