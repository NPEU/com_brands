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
 * Form field for a list of active staff members.
 */
class JFormFieldLandingMenuItem extends JFormField
{
    /**
     * The form field type.
     *
     * @var     string
     */
    protected $type = 'LandingMenuItem';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        $return = '';
        
        if (empty($this->value)) {
            
            $return .= '<div class="control-label" style="width:auto;"><i>Reserved when first saved</i>';
            
        } else {
            
            $return .= '<div><a href="/administrator/index.php?option=com_menus&view=item&client_id=0&layout=edit&id=' . $this->value . '" target="_blank" class="btn  btn-primary">Edit Menu Item <span class="icon-out-2" aria-hidden="true"></span></a>';
            
        }
        
        // @TODO when possible, add a method to the related plugin to listen for menu item saves
        // that checks if the menu item is assigned to a project and, if so, update the projects
        // table with the type and/or link so that HERE we can check for if the menu item has been
        // properly set (and not just a heading) and then change the wording accordingly.
        
        //$return .= '<pre>' . print_r($this->element, true) .'</pre>';
        $return .= '</div><input type="hidden" name="' . $this->name . '" value="' . $this->value . '">';
        return $return;
    }
}