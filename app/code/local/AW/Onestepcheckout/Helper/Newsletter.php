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

class AW_Onestepcheckout_Helper_Newsletter extends Mage_Core_Helper_Data
{
    /**
     * Check is Advanced Newsletter enabled
     */
    public function isAdvancedNewsletterEnabled()
    {
        return $this->isModuleOutputEnabled('AW_Advancednewsletter');
    }

    public function isMageNewsletterEnabled()
    {
        return $this->isModuleOutputEnabled('Mage_Newsletter');
    }

    public function subscribeCustomer($data = array())
    {
        if ($this->isAdvancedNewsletterEnabled()) {
            $subscriber = Mage::getModel('advancednewsletter/subscriber');
            $subscriber->loadByEmail($data['email']);
            $data['status'] = AW_Advancednewsletter_Model_Subscriber::STATUS_SUBSCRIBED;
            $subscribedSegments = $subscriber->getSegmentsCodes();
            if ($subscribedSegments) {
                $data['segments_codes'] = array_merge($subscribedSegments, $data['segments_codes']);
            }
            if (!$subscriber->getId()) {
                $subscriber->setIsNew(true);
            }
            $subscriber->forceWrite($data);
            Mage::dispatchEvent('an_subscriber_subscribe', array('subscriber' => $subscriber));
        } else {
            Mage::getModel('newsletter/subscriber')->subscribe($data['email']);
        }
    }
}