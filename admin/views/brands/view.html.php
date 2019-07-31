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
 * Brands Brands View
 */
class BrandsViewBrands extends JViewLegacy
{
    protected $items;

    protected $pagination;

    protected $state;

    /**
     * Display the Brands view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null)
    {
        $this->state         = $this->get('State');
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        BrandsHelper::addSubmenu('brands');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolBar()
    {
        //$canDo = BrandsHelper::getActions();
        $canDo = JHelperContent::getActions('com_brands');
        $user  = JFactory::getUser();

        $title = JText::_('COM_BRANDS_MANAGER_RECORDS');

        if ($this->pagination->total) {
            $title .= "<span style='font-size: 0.5em; vertical-align: middle;'> (" . $this->pagination->total . ")</span>";
        }

        JToolBarHelper::title($title, 'brand');
        /*
        JToolBarHelper::addNew('brand.add');
        if (!empty($this->items)) {
            JToolBarHelper::editList('brand.edit');
            JToolBarHelper::deleteList('', 'brands.delete');
        }
        */
        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_brands', 'core.create')) > 0) {
            JToolbarHelper::addNew('brand.add');
        }

        if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
        {
            JToolbarHelper::editList('brand.edit');
        }

        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('brands.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('brands.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            //JToolbarHelper::custom('brand.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
            //JToolbarHelper::custom('brand.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
            //JToolbarHelper::archiveList('brand.archive');
            //JToolbarHelper::checkin('brand.checkin');
        }


        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'brands.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('brands.trash');
        }

        if ($user->authorise('core.admin', 'com_brands') || $user->authorise('core.options', 'com_brands'))
        {
            JToolbarHelper::preferences('com_brands');
        }

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
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_BRANDS_ADMINISTRATION'));
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'a.name'  => JText::_('COM_BRANDS_RECORDS_NAME'),
            'a.state' => JText::_('COM_BRANDS_PUBLISHED'),
            'a.id'    => JText::_('COM_BRANDS_ID')
        );
    }
}
