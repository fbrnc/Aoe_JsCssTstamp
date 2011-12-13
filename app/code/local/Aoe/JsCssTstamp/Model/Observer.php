<?php

class Aoe_JsCssTstamp_Model_Observer {

	/**
	 * Clean media cache after event
	 *
	 * @return void
	 */
	public function clean_media_cache_after() {
		// TODO: clean files from cdn
		Mage::app()->removeCache(Aoe_JsCssTstamp_Model_Package::CACHEKEY);
	}

	/**
	 * Set additional headers while uploading files to cdn
	 *
	 * @param Varien_Event_Observer $event
	 * @return void
	 */
	public function imagecdn_upload_to_amazons3(Varien_Event_Observer $event) {
		$path = $event->getData('fs_path');
		$wrapper = $event->getData('wrapper'); /* @var $wrapper OnePica_ImageCdn_Model_Adapter_AmazonS3_Wrapper */
		$headers = $wrapper->getHeaders();
		$changed = false;
		if (preg_match('/\/media\/js\/.*\.js$/', $path)) {
			if (Mage::getStoreConfig('dev/js/compress_files')) {
				$headers['Content-Encoding'] = 'gzip';
				$changed = true;
			}
			$lifetime = Mage::getStoreConfig('dev/js/lifetime');
			if (intval($lifetime)) {
				$headers['Cache-Control'] = 'public, max-age=' . $lifetime;
				$headers['Expires'] = gmdate("D, d M Y H:i:s", time() + $lifetime) . " GMT";
				$changed = true;
			}
		}
		if (preg_match('/\/media\/css\/.*\.css$/', $path)) {
			$lifetime = Mage::getStoreConfig('dev/css/lifetime');
			if (intval($lifetime)) {
				$headers['Cache-Control'] = 'public, max-age=' . $lifetime;
				$headers['Expires'] = gmdate("D, d M Y H:i:s", time() + $lifetime) . " GMT";
				$changed = true;
			}
		}
		if ($changed) {
			$wrapper->setHeaders($headers);
			Mage::log('Adding headers for file while uploading to S3: ' . $path);
		}
	}

}