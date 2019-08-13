<?php

use CloudinaryExtension\Image\SynchronizationChecker as SynchronizationCheckerInterface;

class Cloudinary_Cloudinary_Model_SynchronizationChecker implements SynchronizationCheckerInterface
{
    const CACHE_NAME = 'cloudinary';
    const CACHE_TAG = 'CLOUDINARY';

    /**
     * @param string $imageName
     * @return bool
     */
    public function isSynchronized($imageName)
    {
        if (!$imageName) {
            return false;
        }

        if ($this->hasAutoUploadMapping()) {
            return true;
        }

        $cache = Mage::app()->getCacheInstance();

        if ($cache->canUse(self::CACHE_NAME)) {
            return $this->cachedSynchronizationCheck($cache, $imageName);
        }

        return $this->synchronizationCheck($imageName);
    }

    /**
     * @return bool
     */
    private function hasAutoUploadMapping()
    {
        return Mage::getModel('cloudinary_cloudinary/autoUploadMapping_configuration')->isActive();
    }

    /**
     * @param $imageName
     * @return bool
     */
    private function synchronizationCheck($imageName)
    {
        $coll = Mage::getModel('cloudinary_cloudinary/synchronisation')->getCollection();
        $table = $coll->getMainTable();
        // case sensitive check
        $query = "select count(*) from $table where binary image_name = '$imageName' limit 1";
        return $coll->getConnection()->query($query)->fetchColumn() > 0;
    }

    /**
     * @param string $imageName
     * @return string
     */
    private function cacheKey($imageName)
    {
        return sprintf('cloudinary_%s', md5($imageName));
    }

    /**
     * @param Mage_Core_Model_Cache $cache
     * @param string $imageName
     * @return bool
     */
    private function cachedSynchronizationCheck(Mage_Core_Model_Cache $cache, $imageName)
    {
        $key = $this->cacheKey($imageName);
        $value = $cache->load($key);

        if ($value === false) {
            $value = $this->synchronizationCheck($imageName) ? '1' : '0';
            $cache->save($value, $key, [self::CACHE_TAG]);
        }

        return $value === '1' ? true : false;
    }
}
