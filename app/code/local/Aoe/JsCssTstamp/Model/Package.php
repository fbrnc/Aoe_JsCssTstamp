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
	protected $storeCssInDb;
	protected $storeJsInDb;
	protected $dbStorage;

	/**
	 * Constructor
	 *
	 * Hint: Parent class is a plain php class not extending anything. So don't try to move this content to _construct()
	 */
	public function __construct() {
		$this->cssProtocolRelativeUris = Mage::getStoreConfig('dev/css/protocolRelativeUris');
		$this->jsProtocolRelativeUris = Mage::getStoreConfig('dev/js/protocolRelativeUris');
		$this->storeCssInDb = Mage::getStoreConfig('dev/css/storeInDb');
		$this->storeJsInDb = Mage::getStoreConfig('dev/js/storeInDb');
	}

	/**
	 * Get db storage
	 *
	 * @return Mage_Core_Model_File_Storage_Database
	 */
	protected function getDbStorage() {
		if (is_null($this->dbStorage)) {
			$this->dbStorage = Mage::helper('core/file_storage_database')->getStorageDatabaseModel();
		}
		return $this->dbStorage;
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

		$dbStorage = $this->getDbStorage(); /* @var $dbStorage Mage_Core_Model_File_Storage_Database */

		$mergedJsUrl = Mage::getBaseUrl('media') . 'js/' . $targetFilename;

		if (!$dbStorage->fileExists($relativePath)) {
			$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
			if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
				Mage::throwException('Error while merging js files to path ' . $relativePath);
			}
			$dbStorage->saveFile($relativePath);
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

		// relative path
		$relativePath = str_replace(Mage::getBaseDir('media'), '', $path);
		$relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

		$dbStorage = $this->getDbStorage(); /* @var $dbStorage Mage_Core_Model_File_Storage_Database */

		$mergedCssUrl = Mage::getBaseUrl('media') . 'css/' . $targetFilename;

		if (!$dbStorage->fileExists($relativePath)) {
			$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
			if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeCss'), 'css')) {
				Mage::throwException('Error while merging css files to path: ' . $relativePath);
			}
			$dbStorage->saveFile($relativePath);
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
	 * @param string $uri
	 * @return string
	 */
	protected function _prepareUrl($uri) {
		$uri = parent::_prepareUrl($uri);
		if ($this->cssProtocolRelativeUris) {
			$uri = $this->convertToProtocolRelativeUri($uri);
		}
		return $uri;
	}

}