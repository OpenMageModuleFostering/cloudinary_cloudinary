<?php

use CloudinaryExtension\Image\SynchronizationChecker as SynchronizationCheckerInterface;

class Cloudinary_Cloudinary_Model_SynchronizationChecker implements SynchronizationCheckerInterface
{
    public function isSynchronized($imageName)
    {
        if (!$imageName) {
            return false;
        }

        if ($this->hasAutoUploadMapping()) {
            return true;
        }

        $coll = Mage::getModel('cloudinary_cloudinary/synchronisation')->getCollection();
        $table = $coll->getMainTable();
        // case sensitive check
        $query = "select count(*) from $table where binary image_name = '$imageName' limit 1";
        return $coll->getConnection()->query($query)->fetchColumn() > 0;
    }

    /**
     * @return bool
     */
    private function hasAutoUploadMapping()
    {
        return Mage::getModel('cloudinary_cloudinary/autoUploadMapping_configuration')->isActive();
    }
}
