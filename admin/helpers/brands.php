<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * BrandsHelper component helper.
 */
class BrandsHelper
{
    /**
     * Add style
     */
    public static function addStyle()
    {
        // Set some global property
        $document = JFactory::getDocument();

        $document->addStyleDeclaration('.icon-record:before {content: "\e014";}');
    }

    /**
     * Get the actions
     */
    public static function getActions($itemId = 0, $model = null)
    {
        jimport('joomla.access.access');
        $user   = JFactory::getUser();
        $result = new JObject;

        if (empty($itemId)) {
            $assetName = 'com_brands';
        }
        else {
            $assetName = 'com_brands.record.'.(int) $itemId;
        }

        $actions = JAccess::getActions('com_brands', 'component');

        foreach ($actions as $action) {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }

        // Check if user belongs to assigned category and permit edit if so:
        if ($model) {
            $item  = $model->getItem($itemId);

            if (!!($user->authorise('core.edit', 'com_brands')
            || $user->authorise('core.edit', 'com_content.category.' . $item->catid))) {
                $result->set('core.edit', true);
            }
        }

        return $result;
    }
}