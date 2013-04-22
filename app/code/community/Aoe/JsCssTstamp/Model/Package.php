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

	protected $storageCss;
	protected $storageJs;

	protected $addTstampToAssets;

	protected $dbStorage;

	/**
	 * Constructor
	 *
	 * Hint: Parent class is a plain php class not extending anything. So don't try to move this content to _construct()
	 */
	public function __construct() {
		$this->cssProtocolRelativeUris = Mage::getStoreConfig('dev/css/protocolRelativeUris');
		$this->jsProtocolRelativeUris = Mage::getStoreConfig('dev/js/protocolRelativeUris');

		$this->storageCss = Mage::getStoreConfig('dev/css/storage');
		$this->storageJs = Mage::getStoreConfig('dev/js/storage');

		$this->addTstampToAssets = Mage::getStoreConfig('dev/css/addTstampToAssets');
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

		$mergedJsUrl = Mage::getBaseUrl('media') . 'js/' . $targetFilename;

		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */

		if ($this->storageJs == Aoe_JsCssTstamp_Model_System_Config_Source_Storage::DATABASE) {

			/**
			 * Using the database to store the files.
			 * First check if the file exists in the datase. If it exists, no further action is required.
			 * The file will be delivered directly by a mod_rewrite rule pointing to get.php
			 */

			$dbStorage = $this->getDbStorage(); /* @var $dbStorage Mage_Core_Model_File_Storage_Database */

			if (!$dbStorage->fileExists($relativePath)) {
				if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
					Mage::throwException('Error while merging js files to path ' . $relativePath);
				}
				$dbStorage->saveFile($relativePath);
			}

		} elseif ($this->storageJs == Aoe_JsCssTstamp_Model_System_Config_Source_Storage::FILESYSTEM) {

			/**
			 * Using the file system to store the file
			 */
			if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
				Mage::throwException('Error while merging js files to path ' . $relativePath);
			}

		} elseif ($this->storageJs == Aoe_JsCssTstamp_Model_System_Config_Source_Storage::CDN) {

			/**
			 * Using the cdn to store the file.
			 * Make sure to point the urls correctly to the cdn so that files will be delivered directly from there
			 * Also note, that Cloudfront using an Amazon S3 bucket does not support compression!
			 */
			// check cdn (if available)
			$cdnUrl = Mage::helper('aoejscsststamp')->getCdnUrl($path);

			if (!$cdnUrl) {

				if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
					Mage::throwException('Error while merging js files to path ' . $relativePath);
				}

				// store file to cdn (if available)
				$cdnUrl = Mage::helper('aoejscsststamp')->storeInCdn($path);

			}

			if ($cdnUrl) {
				$mergedJsUrl = $cdnUrl;
			} else {
				Mage::throwException('Error while processsing url');
			}

		} else {

			Mage::throwException('Unsupported storage mode');

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

		$mergedCssUrl = Mage::getBaseUrl('media') . 'css/' . $targetFilename;
		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */

		$dbStorage = $this->getDbStorage(); /* @var $dbStorage Mage_Core_Model_File_Storage_Database */

		if ($this->storageCss == Aoe_JsCssTstamp_Model_System_Config_Source_Storage::DATABASE) {

			if (!$dbStorage->fileExists($relativePath)) {

				if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeCss'), 'css')) {
					Mage::throwException('Error while merging css files to path: ' . $relativePath);
				}
				$dbStorage->saveFile($relativePath);
			}

		} elseif ($this->storageCss == Aoe_JsCssTstamp_Model_System_Config_Source_Storage::FILESYSTEM) {

			/**
			 * Using the file system to store the file
			 */
			if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeCss'), 'css')) {
				Mage::throwException('Error while merging css files to path ' . $relativePath);
			}

		} elseif ($this->storageCss == Aoe_JsCssTstamp_Model_System_Config_Source_Storage::CDN) {

			// check cdn (if available)
			$cdnUrl = Mage::helper('aoejscsststamp')->getCdnUrl($path);
			if (!$cdnUrl) {

				$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
				if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeCss'), 'css')) {
					Mage::throwException('Error while merging css files to path ' . $relativePath);
				}

				// store file to cdn (if available)
				$cdnUrl = Mage::helper('aoejscsststamp')->storeInCdn($path);
			}

			if ($cdnUrl) {
				$mergedJsUrl = $cdnUrl;
			} else {
				Mage::throwException('Error while processsing url');
			}


		} else {

			Mage::throwException('Unsupported storage mode');

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
	protected function getVersionKey($files=array()) {
		$tstamp = Mage::app()->loadCache(self::CACHEKEY);
		if (empty($tstamp)) {
			$tstamp = time();
			Mage::app()->saveCache($tstamp, self::CACHEKEY, array(), null);
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

		if ($this->addTstampToAssets) {
			Mage::log('Aoe_JsCssTsamp: ' . $uri);
			$matches = array();
			if (preg_match('/(.*)\.(gif|png|jpg)$/i', $uri, $matches)) {
				$uri = $matches[1] . '.' . $this->getVersionKey() . '.' . $matches[2];
			}
		}
		return $uri;
	}

}