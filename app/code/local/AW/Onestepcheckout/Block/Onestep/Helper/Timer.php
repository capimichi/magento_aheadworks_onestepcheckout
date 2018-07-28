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


/**
 * Class AW_Onestepcheckout_Block_Onestep_Helper_Timer
 *
 * config{
 *  'block_html_id', 'timer_clock_html_id', 'redirect_now_action_html_id', 'cancel_action_html_id',
 *  'title_text', 'description_text', 'redirect_now_action_text', 'cancel_action_text',
 * }
 */
class AW_Onestepcheckout_Block_Onestep_Helper_Timer extends Mage_Core_Block_Template
{
    const TEMPLATE = "aw_onestepcheckout/onestep/helper/timer.phtml";

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE);
    }

    public function getBlockId()
    {
        return !is_null($this->getData('block_html_id'))?$this->getData('block_html_id'):false;
    }

    public function getTimerClockElement()
    {
        $blockId = !is_null($this->getData('timer_clock_html_id'))?$this->getData('timer_clock_html_id'):false;
        $html = "<span ";
        if ($blockId) {
            $html .= "id=\"{$blockId}\"";
        }
        $html .= "></span>";
        return $html;
    }

    public function getRedirectNowActionHtmlId()
    {
        return !is_null($this->getData('redirect_now_action_html_id'))?$this->getData('redirect_now_action_html_id'):false;
    }

    public function getCancelActionHtmlId()
    {
        return !is_null($this->getData('cancel_action_html_id'))?$this->getData('cancel_action_html_id'):false;
    }

    public function getTitleText()
    {
        return !is_null($this->getData('title_text'))?$this->getData('title_text'):"";
    }

    public function getDescriptionText()
    {
        return !is_null($this->getData('description_text'))?$this->getData('description_text'):"";
    }

    public function getRedirectNowActionText()
    {
        return !is_null($this->getData('redirect_now_action_text'))?$this->getData('redirect_now_action_text'):"";
    }

    public function getCancelActionText()
    {
        return !is_null($this->getData('cancel_action_text'))?$this->getData('cancel_action_text'):"";
    }
}