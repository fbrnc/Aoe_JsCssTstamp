<?php

/**
 * Cache cleaner helper
 * 
 * @author Fabrizio Branca
 * @since 2011-02-15
 */
class Aoe_JsCssTstamp_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * @var OnePica_ImageCdn_Model_Adapter_Abstract
	 */
	protected $cdn;

	/**
	 * Checks if a given path is available in the cdn and returns the url
	 *
	 * @param $path
	 * @return false|string cdn url or false if no cdn is available
	 */
	public function getCdnUrl($path) {
		$cdnUrl = false;
		$cdn = $this->getCdn();
		if ($cdn && $cdn->fileExists($path)) {
			$cdnUrl = $cdn->getUrl($path);
		}
		return $cdnUrl;
	}

	/**
	 * Stores file in cdn and return the cdn url
	 *
	 * @param string $path
	 * @return false|string cdn url or false if no cdn is available
	 */
	public function storeInCdn($path) {
		$cdnUrl = false;
		$cdn = $this->getCdn();
		if ($cdn) {
			$cdn->save($path, $path);
			$cdnUrl = $cdn->getUrl($path);
		}
		return $cdnUrl;
	}

	/**
	 * Get ImageCdn (from OnePice_ImageCdn)
	 * Requires module OnePice_ImageCdn to be available and enabled
	 *
	 * @return false|OnePica_ImageCdn_Model_Adapter_Abstract
	 */
	public function getCdn() {
		if (is_null($this->cdn)) {
			$this->cdn = false;
			$onePicaAvailable = Mage::getConfig()->getModuleConfig('OnePica_ImageCdn')->is('active', 'true');
			if ($onePicaAvailable) {
				$imageCdnHelper = Mage::Helper('imagecdn'); /* @var $imageCdnHelper OnePica_ImageCdn_Helper_Data */
				$cdn = $imageCdnHelper->factory(); /* @var $cdn OnePica_ImageCdn_Model_Adapter_Abstract */
				if ($cdn->useCdn()) {
					$this->cdn = $cdn;
				}
			}
		}
		return $this->cdn;
	}

}