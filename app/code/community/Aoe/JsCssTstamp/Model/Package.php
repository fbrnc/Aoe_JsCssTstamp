<?php

// the ugliest hack to resolve class rewrite conflict with Aoe_DesignFallback without adding dependency on it
if (!Mage::helper('aoejscsststamp')->isModuleEnabled('Aoe_DesignFallback')) {
    class Aoe_DesignFallback_Model_Design_Package extends Mage_Core_Model_Design_Package { }
}

/**
 * Rewriting package class to add some custom version key to bundled files
 *
 * @author Fabrizio Branca
 */
class Aoe_JsCssTstamp_Model_Package extends Aoe_DesignFallback_Model_Design_Package
{
    const CACHEKEY = 'aoe_jscsststamp_versionkey';

    protected $cssProtocolRelativeUris;
    protected $jsProtocolRelativeUris;
    protected $dbStorage;
    protected $addTstampToAssets;
    protected $addTstampToAssetsCss;
    protected $addTstampToAssetsJs;
    protected $cssCreateHashFromFileContent;
    protected $jsCreateHashFromFileContent;
    protected $cssAddStoreIdToHash;
    protected $storeMinifiedCssFolder;
    protected $storeMinifiedJsFolder;

    /**
     * Constructor
     *
     * Hint: Parent class is a plain php class not extending anything. So don't try to move this content to _construct()
     */
    public function __construct()
    {
        $this->cssProtocolRelativeUris = Mage::getStoreConfig('dev/css/protocolRelativeUris');
        $this->jsProtocolRelativeUris = Mage::getStoreConfig('dev/js/protocolRelativeUris');
        $this->addTstampToAssets = Mage::getStoreConfig('dev/css/addTstampToAssets');
        $this->addTstampToAssetsCss = Mage::getStoreConfig('dev/css/addTstampToCssFiles');
        $this->addTstampToAssetsJs = Mage::getStoreConfig('dev/js/addTstampToJsFiles');
        $this->cssCreateHashFromFileContent = Mage::getStoreConfig('dev/css/createHashFromFileContent');
        $this->jsCreateHashFromFileContent = Mage::getStoreConfig('dev/js/createHashFromFileContent');
        $this->cssAddStoreIdToHash = Mage::getStoreConfig('dev/css/addStoreIdToHash');
        $this->storeMinifiedCssFolder = rtrim(Mage::getBaseDir(), DS) . DS . trim(Mage::getStoreConfig('dev/css/storeMinifiedCssFolder'), DS);
        $this->storeMinifiedJsFolder = rtrim(Mage::getBaseDir(), DS) . DS . trim(Mage::getStoreConfig('dev/js/storeMinifiedJsFolder'), DS);

        // is_callable is aware of context, and you can ask it things like parent::__construct from within
        // a child constructor, see http://php.net/manual/en/function.is-callable.php#104632
        if (is_callable('parent::__construct')) {
            parent::__construct();
        }
    }

    /**
     * Get db storage
     *
     * @return Mage_Core_Model_File_Storage_Database
     */
    protected function getDbStorage()
    {
        if (is_null($this->dbStorage)) {
            $this->dbStorage = Mage::helper('core/file_storage_database')->getStorageDatabaseModel();
        }

        return $this->dbStorage;
    }

    /**
     * Overwrite original method in order to add a version key
     *
     * @param array $files
     *
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        $versionKey = '';
        if ($this->addTstampToAssetsJs) {
            $versionKey = '.' . $this->getVersionKey();
        }

        $hashContent = array();
        if ($this->jsCreateHashFromFileContent) {
            foreach ($files as $file) {
                if (!file_exists($file)) {
                    continue;
                }
                $hashContent[] = md5_file($file);
            }
        } else {
            $hashContent = $files;
        }

        $targetFilename = md5(implode(',', $hashContent)) . $versionKey . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }

        $mergedJsUrl = $this->generateMergedUrl('js', $files, $targetDir, $targetFilename);
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
     *
     * @return string
     */
    public function beforeMergeJs($file, $contents)
    {
        $minContent = $this->useMinifiedVersion($file);
        if ($minContent !== false) {
            $contents = $minContent;
        }

        $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
        $contents = "\n\n/* FILE: " . basename($file) . " */\n/* HANDLES: " . implode(",", $handles) . " */\n" . $contents;

        return $contents;
    }

    /**
     * Before merge CSS callback function
     *
     * @param string $file
     * @param string $contents
     *
     * @return string
     */
    public function beforeMergeCss($file, $contents)
    {
        $minContent = $this->useMinifiedVersion($file);
        if ($minContent !== false) {
            $contents = $minContent;
        }

        $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
        $contents = "\n\n/* FILE: " . basename($file) . " */\n/* HANDLES: " . implode(",", $handles) . " */\n" . $contents;

        return parent::beforeMergeCss($file, $contents);
    }

    /**
     * Checks if minified version of the given file exist. And if returns its content
     *
     * @param string $file
     * @return string|bool the content of the file else false
     */
    protected function useMinifiedVersion($file)
    {
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

        return false;
    }

    /**
     * Overwrite original method in order to add a version key
     *
     * @param array $files
     *
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        $versionKey = '';
        if ($this->addTstampToAssetsCss) {
            $versionKey = '.' . $this->getVersionKey();
        }

        $hashContent = array();
        if ($this->cssCreateHashFromFileContent) {
            foreach ($files as $file) {
                if (!file_exists($file)) {
                    continue;
                }
                $hashContent[] = md5_file($file);
            }
        } else {
            $hashContent = $files;
        }

        if ($this->cssAddStoreIdToHash) {
            array_push($hashContent, $this->getStore()->getId());
        }

        $targetFilename = md5(implode(',', $hashContent)) . $versionKey . '.css';
        $targetDir = $this->_initMergerDir('css');
        if (!$targetDir) {
            return '';
        }

        $mergedCssUrl = $this->generateMergedUrl('css', $files, $targetDir, $targetFilename);
        if ($this->cssProtocolRelativeUris) {
            $mergedCssUrl = $this->convertToProtocolRelativeUri($mergedCssUrl);
        }

        return $mergedCssUrl;
    }

    /**
     * Generate url for merged file of given $type
     *
     * @param string $type
     * @param array $files
     * @param string $targetDir
     * @param string $targetFilename
     *
     * @return string
     */
    protected function generateMergedUrl($type, array $files, $targetDir, $targetFilename)
    {
        Varien_Profiler::start('generateMergedUrl: ' . $type);
        $targetFilename = $this->getProtocolSpecificTargetFileName($targetFilename);
        $path = $targetDir . DS . $targetFilename;

        // relative path
        $relativePath = ltrim(str_replace(Mage::getBaseDir('media'), '', $path), DS);

        $mergedUrl = Mage::getBaseUrl('media') . $type . DS . $targetFilename;
        $storage = Mage::getStoreConfig('dev/' . $type . '/storage');

        /* @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        switch ($storage) {
            case Aoe_JsCssTstamp_Model_System_Config_Source_Storage::FILESYSTEM;
                /**
                 * Using the file system to store the file
                 */
                if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMerge' . ucfirst($type)), $type)) {
                    Mage::throwException("Error while merging {$type} files " . print_r($files, true) . " to path: " . $relativePath);
                }
                break;
            case Aoe_JsCssTstamp_Model_System_Config_Source_Storage::DATABASE:
                /**
                 * Using the database to store the files.
                 * First check if the file exists in the datase. If it exists, no further action is required.
                 * The file will be delivered directly by a mod_rewrite rule pointing to get.php
                 */
                /* @var $dbStorage Mage_Core_Model_File_Storage_Database */
                $dbStorage = $this->getDbStorage();
                if (!$dbStorage->fileExists($relativePath)) {
                    if (!$coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMerge' . ucfirst($type)), $type)) {
                        Mage::throwException("Error while merging {$type} files " . print_r($files, true) . " to path: " . $relativePath);
                    }
                    $dbStorage->saveFile($relativePath);
                }
                break;
            default:
                Mage::throwException('Unsupported storage mode');
                break;
        }
        Varien_Profiler::stop('generateMergedUrl: ' . $type);
        return $mergedUrl;
    }

    /**
     * Get a cached timestamp as version key
     *
     * @return int timestamp
     */
    protected function getVersionKey()
    {
        $timestamp = Mage::app()->loadCache(self::CACHEKEY);
        if (empty($timestamp)) {
            $timestamp = time();
            Mage::app()->saveCache($timestamp, self::CACHEKEY, array(), null);
        }

        return $timestamp;
    }

    /**
     * Convert uri to protocol independent uri
     * E.g. http://example.com -> //example.com
     *
     * @param string $uri
     * @return string
     */
    protected function convertToProtocolRelativeUri($uri)
    {
        return preg_replace('/^https?:/i', '', $uri);
    }

    /**
     * Convert uri to protocol independent uri
     * E.g. http://example.com -> //example.com
     *
     * @param string $uri
     * @return string
     */
    protected function _prepareUrl($uri)
    {
        $uri = parent::_prepareUrl($uri);
        if ($this->cssProtocolRelativeUris) {
            $uri = $this->convertToProtocolRelativeUri($uri);
        }

        if ($this->addTstampToAssets) {
            if (Mage::getStoreConfigFlag('dev/log/aoeJsCssTstampActive')) {
                Mage::log('Aoe_JsCssTstamp: ' . $uri);
            }
            $matches = array();
            if (preg_match('/(.*)\.(gif|png|jpg)$/i', $uri, $matches)) {
                $uri = $matches[1] . '.' . $this->getVersionKey() . '.' . $matches[2];
            }
        }

        return $uri;
    }

    /**
     * This is to fix the secure/unsecure URL problem
     *
     * @param string $targetFilename
     * @return string
     */
    protected function getProtocolSpecificTargetFileName($targetFilename)
    {
        $store = $this->getStore();
        if ($store->isAdmin()) {
            $secure = $store->isAdminUrlSecure();
        } else {
            $secure = $store->isFrontUrlSecure() && Mage::app()->getRequest()->isSecure();
        }
        return ($secure ? 's' : 'u') . '.' . $targetFilename;
    }

    /**
     * Add version to js/css files
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file = null, array $params = array())
    {
        $result = parent::getSkinUrl($file, $params);

        if ($this->addTstampToAssetsCss) {
            $matches = array();
            if (preg_match('/(.*)\.(css)$/i', $result, $matches)) {
                $result = $matches[1] . '.' . $this->getVersionKey() . '.' . $matches[2];
            }

            if ($this->cssProtocolRelativeUris) {
                $result = $this->convertToProtocolRelativeUri($result);
            }
        }
        if ($this->addTstampToAssetsJs) {
            $matches = array();
            if (preg_match('/(.*)\.(js)$/i', $result, $matches)) {
                $result = $matches[1] . '.' . $this->getVersionKey() . '.' . $matches[2];
            }

            if ($this->jsProtocolRelativeUris) {
                $result = $this->convertToProtocolRelativeUri($result);
            }
        }
        if ($this->addTstampToAssets) {
            $matches = array();
            if (preg_match('/(.*)\.(gif|png|jpg)$/i', $result, $matches)) {
                $result = $matches[1] . '.' . $this->getVersionKey() . '.' . $matches[2];
            }

            if ($this->cssProtocolRelativeUris) {
                $result = $this->convertToProtocolRelativeUri($result);
            }
        }

        return $result;
    }
}
