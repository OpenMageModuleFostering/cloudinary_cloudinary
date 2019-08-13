<?php

class Cloudinary_Cloudinary_Block_Adminhtml_System_Config_Form_Foldered extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Select $element
     * @return mixed
     */
    protected function _getElementHtml($element)
    {
        if (Mage::getModel('cloudinary_cloudinary/autoUploadMapping_configuration')->isActive()) {
            $element->setDisabled('disabled');
        }
        return parent::_getElementHtml($element);
    }
}
