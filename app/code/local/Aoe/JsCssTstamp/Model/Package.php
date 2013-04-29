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
	protected $addTstampToAssets;
	protected $storeMinifiedCssFolder;
	protected $storeMinifiedJsFolder;

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
		$this->addTstampToAssets = Mage::getStoreConfig('dev/css/addTstampToAssets');
		$this->storeMinifiedCssFolder = rtrim(Mage::getBaseDir(), DS)
			. DS . trim(Mage::getStoreConfig('dev/css/storeMinifiedCssFolder'), DS);
		$this->storeMinifiedJsFolder = rtrim(Mage::getBaseDir(), DS)
			. DS . trim(Mage::getStoreConfig('dev/js/storeMinifiedJsFolder'), DS);
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

		$versionKey = $this->getVersionKey();
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.js';
		$targetDir = $this->_initMergerDir('js');
		if (!$targetDir) {
			return '';
		}

		$path = $targetDir . DS . $targetFilename;

		// relative path
		$relativePath = str_replace(Mage::getBaseDir('media'), '', $path);
		$relativePath = ltrim($relativePath, DS);

		/* @var $dbStorage Mage_Core_Model_File_Storage_Database */
		$dbStorage = $this->getDbStorage();

		$mergedJsUrl = Mage::getBaseUrl('media') . 'js' . DS . $targetFilename;

		if (!$dbStorage->fileExists($relativePath)) {
			$coreHelper = Mage::helper('core');
			/* @var $coreHelper Mage_Core_Helper_Data */
			if (!$coreHelper->mergeFiles($files, $path, FALSE, array($this, 'beforeMergeJs'), 'js')) {
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
		$minContent = $this->useMinifiedVersion($file);
		if ($minContent !== FALSE) {
			$contents = $minContent;
		}

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
		$minContent = $this->useMinifiedVersion($file);
		if ($minContent !== FALSE) {
			$contents = $minContent;
		}

		$contents = "\n\n/* FILE: " . basename($file) . " */\n" . $contents;
		return parent::beforeMergeCss($file, $contents);
	}

	/**
	 * Checks if minified version of the given file exist. And if returns its content
	 *
	 * @param string $file
	 * @return string|bool the content of the file else false
	 */
	protected function useMinifiedVersion($file) {
		$parts = pathinfo($file);
		// Add .min to the extension of the original filename
		$minFile = $parts['dirname'] . DS . $parts['filename'] . '.min.' . $parts['extension'];

		if (file_exists($minFile)) {
			// return the content of the min file @see Mage_Core_Helper_Data -> mergeFiles()
			return file_get_contents($minFile) . "\n";
		} else {
			$pathRelativeToBase = str_replace(Mage::getBaseDir(), '', $parts['dirname']);
			$pathRelativeToBase = ltrim($pathRelativeToBase, DS);

			switch ($parts['extension']) {
				case 'js':
					$minFile = $this->storeMinifiedJsFolder . DS . $pathRelativeToBase
						. DS . $parts['filename'] . '.min.' . $parts['extension'];
					break;
				case 'css':
				default:
					$minFile = $this->storeMinifiedCssFolder . DS . $pathRelativeToBase
						. DS . $parts['filename'] . '.min.' . $parts['extension'];
				break;
			}

			if (file_exists($minFile)) {
				// return the content of the min file @see Mage_Core_Helper_Data -> mergeFiles()
				return file_get_contents($minFile) . "\n";
			}
		}

		return FALSE;
	}

	/**
	 * Overwrite original method in order to add a version key
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedCssUrl($files) {

		$versionKey = $this->getVersionKey();
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.css';
		$targetDir = $this->_initMergerDir('css');
		if (!$targetDir) {
			return '';
		}

		$path = $targetDir . DS . $targetFilename;

		// relative path
		$relativePath = str_replace(Mage::getBaseDir('media'), '', $path);
		$relativePath = ltrim($relativePath, DS);

		$dbStorage = $this->getDbStorage();
		/* @var $dbStorage Mage_Core_Model_File_Storage_Database */

		$mergedCssUrl = Mage::getBaseUrl('media') . 'css' . DS . $targetFilename;

		if (!$dbStorage->fileExists($relativePath)) {
			$coreHelper = Mage::helper('core');
			/* @var $coreHelper Mage_Core_Helper_Data */
			if (!$coreHelper->mergeFiles($files, $path, FALSE, array($this, 'beforeMergeCss'), 'css')) {
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
	protected function getVersionKey() {
		$tstamp = Mage::app()->loadCache(self::CACHEKEY);
		if (empty($tstamp)) {
			$tstamp = time();
			Mage::app()->saveCache($tstamp, self::CACHEKEY, array(), NULL);
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
