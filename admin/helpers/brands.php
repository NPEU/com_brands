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
    
    /**
     * Tidy SVG.
     *
     * @param   string  The SVG.
     * @param   bool  Whether to minify output or not.
     */
    public static function tidySVG($str, $minify = true) {
        
        if ($minify) {
            $indent = false;
        } else {
            $indent = true;
        }
        
        ob_start();
        $tidy = new tidy;
        $config = array(
            'indent' => $indent,
            'wrap' => 0,
            'clean' => true,
            'show-body-only' => true,
            'input-xml' => true,
            'output-xml' => true,
            'newline' => 'LF'
        );
        $tidy->parseString($str, $config, 'utf8');
        $tidy->cleanRepair();
        $input = $tidy;
        
        if ($minify) {
            return preg_replace('#\n#', '', $input->value);
        } else {
            return $input->value;
        }
        
    }
}
