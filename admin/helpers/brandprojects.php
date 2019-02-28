<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brandprojects
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * BrandProjectsHelper component helper.
 */
class BrandProjectsHelper
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
     * Configure the Linkbar.
     *
     * @param   string  The name of the active view.
     */
    public static function addSubmenu($vName = 'records')
    {
        #echo $vName;
        JHtmlSidebar::addEntry(
            JText::_('COM_BRANDPROJECTS_MANAGER_SUBMENU_PROJECTS'),
            'index.php?option=com_brandprojects&view=records',
            $vName == 'records'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_BRANDPROJECTS_MANAGER_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&view=categories&extension=com_brandprojects',
            $vName == 'categories'
        );
        
        /* This seems to get overridden:
        if ($vName == 'categories') {
            JFactory::getDocument()->setTitle(JText::_('COM_BRANDPROJECTS_MANAGER_SUBMENU_CATEGORIES'));
        }
        */
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
            $assetName = 'com_brandprojects';
        }
        else {
            $assetName = 'com_brandprojects.csvupload.'.(int) $itemId;
        }

        $actions = JAccess::getActions('com_brandprojects', 'component');

        foreach ($actions as $action) {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }

        // Check if user belongs to assigned category and permit edit if so:
        if ($model) {
            $item  = $model->getItem($itemId);

            if (!!($user->authorise('core.edit', 'com_brandprojects')
            || $user->authorise('core.edit', 'com_content.category.' . $item->catid))) {
                $result->set('core.edit', true);
            }
        }

        return $result;
    }
}