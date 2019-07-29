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
class BrandsHelper extends JHelperContent
{
    /**
     * Add style
     */
    public static function addStyle()
    {
        // Set some global property
        $document = JFactory::getDocument();
        // Update this with icon of choice from:
        // /administrator/templates/isis/css/template.css
        $document->addStyleDeclaration('.icon-brand:before {content: "\e014";}');
    }

    /**
     * Configure the Submenu. Delete if component has only one view.
     *
     * @param   string  The name of the active view.
     */
    public static function addSubmenu($vName = 'brands')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_BRANDS_MANAGER_SUBMENU_RECORDS'),
            'index.php?option=com_brands&view=brands',
            $vName == 'brands'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_BRANDS_MANAGER_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&view=categories&extension=com_brands',
            $vName == 'categories'
        );
    }
}
