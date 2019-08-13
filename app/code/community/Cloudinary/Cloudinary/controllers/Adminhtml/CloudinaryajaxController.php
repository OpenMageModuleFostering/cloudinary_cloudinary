<?php

use CloudinaryExtension\Image\Transformation;
use CloudinaryExtension\Image\Transformation\Freeform;

class Cloudinary_Cloudinary_Adminhtml_CloudinaryajaxController extends Mage_Adminhtml_Controller_Action
{
    public function sampleAction()
    {
        try {
            $freeTransform = $this->getRequest()->getParam('free');
            $freeModel = Mage::getModel('cloudinary_cloudinary/system_config_free');
            $url = $freeModel->sampleImageUrl($this->defaultTransform($freeTransform));
            $this->validate($freeModel, $url);
            $this->jsonReponse(
                200,
                ['url' => $url]
            );
        } catch (\Exception $e) {
            $this->jsonReponse(401, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param int $code
     * @param array $payload
     */
    private function jsonReponse($code, array $payload)
    {
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/json')
            ->setHttpResponseCode($code)
            ->setBody(Mage::helper('core')->jsonEncode($payload));
    }

    /**
     * @param string $freeTransforma
     * @return Transformation
     */
    private function defaultTransform($freeTransform)
    {
        return Mage::getModel('cloudinary_cloudinary/configuration')
            ->getDefaultTransformation()
            ->withFreeform(Freeform::fromString($freeTransform));
    }

    /**
     * @param Cloudinary_Cloudinary_Model_System_Config_Free $model
     * @param string $url
     * @throws Exception
     */
    private function validate(Cloudinary_Cloudinary_Model_System_Config_Free $model, $url)
    {
        if (!$model->hasAccountConfigured()) {
            throw new \Exception('Cloudinary credentials required');
        }

        $model->validateImageUrl($url);
    }
}
