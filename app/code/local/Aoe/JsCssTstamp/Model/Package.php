<?php

/**
 * Rewriting package class to add some custom version key to bundled files
 *
 * @author Fabrizio Branca
 */
class Aoe_JsCssTstamp_Model_Package extends Mage_Core_Model_Design_Package {

	const CACHEKEY = 'aoe_jscsststamp_versionkey';

	protected $cssProtocolRelativeUris;
	protected $jsProtocolRelativeUris;

	/**
	 * Compress
	 */
	public function __construct() {
		$this->cssProtocolRelativeUris = Mage::getStoreConfig('dev/css/protocolRelativeUris');
		$this->jsProtocolRelativeUris = Mage::getStoreConfig('dev/js/protocolRelativeUris');
	}

	/**
	 * Overwrite original method in order to add a version key
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedJsUrl($files) {

		$versionKey = $this->getVersionKey($files);
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.js';
		$targetDir = $this->_initMergerDir('js');
		if (!$targetDir) {
			return '';
		}

		$path = $targetDir . DS . $targetFilename;

		// relative path
		$relativePath = str_replace(Mage::getBaseDir('media'), '', $path);
		$relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

		$dbHelper = Mage::helper('core/file_storage_database'); /* @var $dbHelper Mage_Core_Helper_File_Storage_Database */
		$fileModel = $dbHelper->getStorageDatabaseModel(); /* @var $fileModel Mage_Core_Model_File_Storage_Database */

		// this needs to be done only once and might go into a setup script
		$fileModel->getDirectoryModel()->prepareStorage();
		$fileModel->prepareStorage();

		$mergedJsUrl = Mage::getBaseUrl('media') . 'js/' . $targetFilename;

		if (!$fileModel->fileExists($relativePath)) {
			$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
			if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
				Mage::throwException('Error while merging files!');
			}
			$fileModel->saveFile($relativePath);
		}

		if ($this->jsProtocolRelativeUris) {
			$mergedJsUrl = $this->convertToProtocolRelativeUri($mergedJsUrl);
		}

		return $mergedJsUrl;
	}


	/**
	 * Before merge JS callback function
	 *
	 * @param string $file
	 * @param string $contents
	 * @return string
	 */
	public function beforeMergeJs($file, $contents) {
		$contents = "\n\n/* FILE: " . basename($file) . " */\n" . $contents;
		return $contents;
	}



	/**
	 * Before merge CSS callback function
	 *
	 * @param string $file
	 * @param string $contents
	 * @return string
	 */
	public function beforeMergeCss($file, $contents) {
		$contents = "\n\n/* FILE: " . basename($file) . " */\n" . $contents;
		return parent::beforeMergeCss($file, $contents);
	}

	/**
	 * Overwrite original method in order to add a version key
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedCssUrl($files) {
		$versionKey = $this->getVersionKey($files);
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.css';
		$targetDir = $this->_initMergerDir('css');
		if (!$targetDir) {
			return '';
		}

		$path = $targetDir . DS . $targetFilename;

		// check cdn (if available)
		$mergedCssUrl = Mage::helper('aoejscsststamp')->getCdnUrl($path);
		if (!$mergedCssUrl) {

			$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
			if ($coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeCss'), 'css')) {
				$mergedCssUrl = Mage::getBaseUrl('media') . 'css/' . $targetFilename;
			}

			// store file to cdn (if available)
			$cdnUrl = Mage::helper('aoejscsststamp')->storeInCdn($path);
			if ($cdnUrl) {
				$mergedCssUrl = $cdnUrl;
			}
		}

		if ($this->cssProtocolRelativeUris) {
			$mergedCssUrl = $this->convertToProtocolRelativeUri($mergedCssUrl);
		}

		return $mergedCssUrl;
	}

	/**
	 * Get a cached timestamp as version key
	 *
	 * @param array $files
	 * @return int tstamp
	 */
	protected function getVersionKey($files) {
		$tstamp = Mage::app()->loadCache(self::CACHEKEY);
		if (empty($tstamp)) {
			$tstamp = time();
			Mage::app()->saveCache($tstamp, self::CACHEKEY);
		}
		return $tstamp;
	}

	/**
	 * Convert uri to protocol independent uri
	 * E.g. http://example.com -> //example.com
	 *
	 * @param string $uri
	 * @return string
	 */
	protected function convertToProtocolRelativeUri($uri) {
		return preg_replace('/^https?:/i', '', $uri);
	}

	/**
	 * Convert uri to protocol independent uri
	 * E.g. http://example.com -> //example.com
	 *
	 * @param $uri
	 * @return mixed
	 */
	protected function _prepareUrl($uri) {
		$uri = parent::_prepareUrl($uri);
		if ($this->cssProtocolRelativeUris) {
			$uri = $this->convertToProtocolRelativeUri($uri);
		}
		return $uri;
	}

}