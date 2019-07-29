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
 * Brands Brand View
 */
class BrandsViewBrand extends JViewLegacy
{
    protected $state;

    protected $item;

    protected $form;

    protected $script;

    /**
     * Display the Brands view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');


        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolBar()
    {
        // Hide Joomla Administrator Main menu:
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $userId     = $user->id;


        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
        $isNew = ($this->item->id == 0);

        // Build the actions for new and existing records.
        $canDo = JHelperContent::getActions('com_brands');


        JToolbarHelper::title(
            JText::_('COM_BRANDS_MANAGER_' . ($checkedOut ? 'RECORD_VIEW' : ($isNew ? 'RECORD_ADD' : 'RECORD_EDIT'))),
            'palette'
        );

        // For new records, check the create permission.
        if ($isNew && (count($user->getAuthorisedCategories('com_brands', 'core.create')) > 0)) {
            JToolbarHelper::apply('brand.apply');
            JToolbarHelper::save('brand.save');
            JToolbarHelper::save2new('brand.save2new');
            JToolbarHelper::cancel('brand.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                JToolbarHelper::apply('brand.apply');
                JToolbarHelper::save('brand.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    JToolbarHelper::save2new('brand.save2new');
                }
            }
            // If checked out, we can still save
            if ($canDo->get('core.create')) {
                JToolbarHelper::save2copy('brand.save2copy');
            }


            JToolbarHelper::cancel('brand.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $isNew = ($this->item->id < 1);
        $document = JFactory::getDocument();
        $document->setTitle($isNew ? JText::_('COM_BRANDS_RECORD_CREATING') :
                JText::_('COM_BRANDS_RECORD_EDITING'));

        if (!empty($this->script)) {
            $document->addScript(JURI::root() . $this->script);
        }

        $document->addScript(JURI::root() . "/administrator/components/com_brands"
                                          . "/views/brand/submitbutton.js");
        JText::script('COM_BRANDS_RECORD_ERROR_UNACCEPTABLE');
    }
}
