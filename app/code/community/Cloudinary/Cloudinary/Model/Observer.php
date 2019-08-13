<?php

use CloudinaryExtension\CloudinaryImageProvider;
use CloudinaryExtension\CredentialValidator;
use CloudinaryExtension\Image;
use CloudinaryExtension\Security\CloudinaryEnvironmentVariable;
use CloudinaryExtension\AutoUploadMapping\RequestProcessor;
use CloudinaryExtension\AutoUploadMapping\ApiClient;
use Mage_Adminhtml_Model_Config_Data as ConfigData;
use Mage_Catalog_Model_Product as Product;
use Varien_Event_Observer as EventObserver;

class Cloudinary_Cloudinary_Model_Observer extends Mage_Core_Model_Abstract
{
    const CLOUDINARY_CONFIG_SECTION = 'cloudinary';
    const ERROR_WRONG_CREDENTIALS = 'There was a problem validating your Cloudinary credentials.';
    const AUTO_UPLOAD_SETUP_FAIL_MESSAGE = 'Error. Unable to setup auto upload mapping.';

    /**
     * @param  EventObserver $event
     *
     * @return EventObserver
     */
    public function loadCustomAutoloaders(EventObserver $event)
    {
        Mage::helper('cloudinary_cloudinary/autoloader')->register();

        return $event;
    }

    /**
     * @param  EventObserver $event
     */
    public function uploadImagesToCloudinary(EventObserver $event)
    {
        if (Mage::getModel('cloudinary_cloudinary/configuration')->isEnabled()) {
            $cloudinaryImage = Mage::getModel('cloudinary_cloudinary/image');

            foreach ($this->getImagesToUpload($event->getProduct()) as $image) {
                $cloudinaryImage->upload($image);
            }
        }
    }

    /**
     * @param  EventObserver $event
     */
    public function deleteImagesFromCloudinary(EventObserver $event)
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = Mage::getModel('cloudinary_cloudinary/configuration');

        if (!$configuration->isEnabled()) {
            return;
        }

        $imageProvider = CloudinaryImageProvider::fromConfiguration($configuration);

        foreach ($this->getImagesToDelete($event->getProduct()) as $image) {
            $migratedPath = $configuration->isFolderedMigration() ? $configuration->getMigratedPath($image['file']) : '';
            $imageProvider->delete(Image::fromPath($image['file'], ltrim($migratedPath, '/')));
        }
    }

    /**
     * @param  EventObserver $observer
     */
    public function validateCloudinaryCredentials(EventObserver $observer)
    {
        $configObject = $observer->getEvent()->getObject();
        if ($configObject->getSection() != self::CLOUDINARY_CONFIG_SECTION) {
            return;
        }

        $configData = $this->flattenConfigData($configObject);
        if ($configData['cloudinary_enabled'] != '1') {
            return;
        }

        $credentialValidator = new CredentialValidator();
        $environmentVariable = CloudinaryEnvironmentVariable::fromString($configData['cloudinary_environment_variable']);

        if (!$credentialValidator->validate($environmentVariable->getCredentials())) {
            throw new Mage_Core_Exception(self::ERROR_WRONG_CREDENTIALS);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function cloudinaryConfigChanged(EventObserver $observer)
    {
        if (!Mage::getModel('cloudinary_cloudinary/configuration')->isEnabled()) {
            return;
        }

        if (!$this->autoUploadRequestProcessor()->handle('media', Mage::getBaseUrl('media'))) {
            Mage::getSingleton('adminhtml/session')->addError(self::AUTO_UPLOAD_SETUP_FAIL_MESSAGE);
        }
    }

    /**
     * @return RequestProcessor
     */
    private function autoUploadRequestProcessor()
    {
        return new RequestProcessor(
            Mage::getModel('cloudinary_cloudinary/autoUploadMapping_configuration'),
            ApiClient::fromConfiguration(Mage::getModel('cloudinary_cloudinary/configuration'))
        );
    }

    /**
     * @param  Product $product
     *
     * @return array
     */
    private function getImagesToUpload(Product $product)
    {
        return Mage::getModel('cloudinary_cloudinary/catalog_product_media')->newImagesForProduct($product);
    }

    /**
     * @param  Product $product
     *
     * @return array
     */
    private function getImagesToDelete(Product $product)
    {
        $productMedia = Mage::getModel('cloudinary_cloudinary/catalog_product_media');
        return $productMedia->removedImagesForProduct($product);
    }

    /**
     * @param  ConfigData $configObject
     *
     * @return array
     */
    private function flattenConfigData(ConfigData $configObject)
    {
        $configData = array();
        $groups = $configObject->getGroups();

        if (array_key_exists('setup', $groups)) {
            $configData = array_map(
                function($field) {
                    return $field['value'];
                },
                $groups['setup']['fields']
            );
        }
        return $configData;
    }
}
