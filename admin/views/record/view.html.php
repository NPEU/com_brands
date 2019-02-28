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
 * BrandProjects Record View
 */
class BrandProjectsViewRecord extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    /**
     * Display the BrandProjects view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');

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
        $input = JFactory::getApplication()->input;

        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        if ($isNew) {
            $title = JText::_('COM_BRANDPROJECTS_MANAGER_RECORD_NEW');
        } else {
            $title = JText::_('COM_BRANDPROJECTS_MANAGER_RECORD_EDIT');
        }

        JToolBarHelper::title($title, 'record');
        JToolBarHelper::save('record.save');
        JToolBarHelper::cancel(
            'record.cancel',
            $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
        );
        
        // Render side bar.
		$this->sidebar = JHtmlSidebar::render();
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
        $document->setTitle($isNew ? JText::_('COM_BRANDPROJECTS_RECORD_CREATING') :
                JText::_('COM_BRANDPROJECTS_RECORD_EDITING'));
        $document->addScript(JURI::root() . $this->script);
        $document->addScript(JURI::root() . "/administrator/components/com_brandprojects"
                                          . "/views/record/submitbutton.js");
        JText::script('COM_BRANDPROJECTS_RECORD_ERROR_UNACCEPTABLE');
    }
}
