<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onestepcheckout
 * @version    1.3.11
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onestepcheckout_Helper_Awgiftcard extends Mage_Core_Helper_Data
{
    const AW_GIFTCARD_V1 = 1;
    const AW_GIFTCARD_V2 = 2;

    /**
     * Check is AW_GiftCard enabled
     * @return bool
     */
    public function isGiftcardEnabled()
    {
        if ($this->_getGiftcardVersion() !== null) {
            return true;
        }
        return false;
    }

    public function applyGiftcard($giftCardCode)
    {
        $giftCardModel = $this->_getGiftcardByCode($giftCardCode);
        if ($giftCardModel->getId()) {
            switch($giftCardModel->getAWGiftCardVersion()) {
                case self::AW_GIFTCARD_V1:
                    Mage::helper('aw_giftcard/totals')->addCardToQuote($giftCardModel);
                    break;
                case self::AW_GIFTCARD_V2:
                    Mage::helper('aw_giftcard2/giftcard')->addToQuote($giftCardModel);
                    break;
                default:
            }

        }
    }

    public function checkGiftcard($giftCardCode)
    {
        $giftCardModel = $this->_getGiftcardByCode($giftCardCode);
        $result = array();
        if ($giftCardModel->getId()) {
            switch($giftCardModel->getAWGiftCardVersion()) {
                case self::AW_GIFTCARD_V1:
                    $result['code'] = $giftCardModel->getCode();
                    $result['status'] = array (
                        'label'     => 'Active: %s',
                        'value'     =>  Mage::getModel('aw_giftcard/source_product_attribute_option_yesno')
                                            ->getOptionText($giftCardModel->getStatus())
                    );
                    $result['state'] = array (
                        'label'     => 'Status: %s',
                        'value'     =>  Mage::getModel('aw_giftcard/source_giftcard_status')
                                            ->getOptionByValue($giftCardModel->getState())
                    );
                    $result['balance'] = Mage::helper('core')->currency($giftCardModel->getBalance());
                    $result['expires'] = $giftCardModel->getExpireAt();
                    break;
                case self::AW_GIFTCARD_V2:
                    $result['code'] = $giftCardModel->getCode();
                    $result['status'] = array (
                        'label'     => 'Availability: %s',
                        'value'     =>  Mage::getModel('aw_giftcard2/source_giftcard_availability')
                            ->getOptionText($giftCardModel->getAvailability())
                    );
                    $result['state'] = array (
                        'label'     => 'Is Used: %s',
                        'value'     =>  Mage::getModel('aw_giftcard2/source_giftcard_used')
                            ->getOptionText($giftCardModel->getIsUsed())
                    );
                    $result['balance'] = Mage::helper('core')->currency($giftCardModel->getBalance());
                    $result['expires'] = $giftCardModel->getExpireAt();
                    break;
                default:
            }
        }
        return $result;
    }

    public function removeGiftcard($giftCardCode)
    {
        $giftCardModel = $this->_getGiftcardByCode($giftCardCode);
        if ($giftCardModel->getId()) {
            switch($giftCardModel->getAWGiftCardVersion()) {
                case self::AW_GIFTCARD_V1:
                    Mage::helper('aw_giftcard/totals')->removeCardFromQuote(trim($giftCardModel->getCode()));
                    break;
                case self::AW_GIFTCARD_V2:
                    Mage::helper('aw_giftcard2/giftcard')->removeFromQuote($giftCardModel->getId());
                    break;
                default:
            }
        }
    }

    protected function _getGiftcardByCode($giftCardCode)
    {
        $gcVersion = $this->_getGiftcardVersion();
        switch ($gcVersion) {
            case self::AW_GIFTCARD_V1:
                $giftCardModel = Mage::getModel('aw_giftcard/giftcard')->loadByCode(trim($giftCardCode));
                break;
            case self::AW_GIFTCARD_V2:
                $giftCardModel = Mage::getModel('aw_giftcard2/giftcard')->loadByCode(trim($giftCardCode));
                break;
            default:
                $giftCardModel = null;
        }

        if ($giftCardModel === null || !$giftCardModel->getId()) {
            throw new Exception($this->__('Gift Card is not valid.'));
        }
        $giftCardModel->setAWGiftCardVersion($gcVersion);
        return $giftCardModel;
    }

    protected function _getGiftcardVersion()
    {
        if ($this->isModuleOutputEnabled('AW_Giftcard2') && Mage::helper('aw_giftcard2/config')->isEnabled()) {
            $version = self::AW_GIFTCARD_V2;
        } elseif ($this->isModuleOutputEnabled('AW_Giftcard') && Mage::helper('aw_giftcard/config')->isEnabled()) {
            $version = self::AW_GIFTCARD_V1;
        } else {
            $version = null;
        }
        return $version;
    }
}
