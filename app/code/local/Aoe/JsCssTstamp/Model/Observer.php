<?php

class Aoe_JsCssTstamp_Model_Observer {

	public function clean_media_cache_after() {
		Mage::app()->removeCache(Aoe_JsCssTstamp_Model_Package::CACHEKEY);
	}

}