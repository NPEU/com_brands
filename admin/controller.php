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
 * Brands Component Controller
 */
class BrandsController extends JControllerLegacy
{
    /**
     * The default view for the display method.
     *
     * @var string
     */
    #protected $default_view = 'records';

    /**
     * Constructor
     *
     * @param   array  $config  Optional configuration array
     *
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        //JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
        //$this->addModelPath(JPATH_ADMINISTRATOR . '/components/com_menus/models');
    }

    /**
     * display task
     *
     * @return void
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Get the document object.
        $document = JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', 'records');
        $vFormat = $document->getType();
        $lName   = $this->input->get('layout', 'default', 'string');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat))
        {
            // Get the model for the view.
            $model = $this->getModel($vName);

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;

            $view->display();
        }

        return $this;

        /*
        // Set default view if not set
        JFactory::getApplication()->input->set('view', JFactory::getApplication()->input->get('view', 'records'));

        $session = JFactory::getSession();
        $registry = $session->get('registry');

        // call parent behavior
        parent::display($cachable, $urlparams);

        // Add style
        BrandsHelper::addStyle();

        // Set the submenu
        BrandsHelper::addSubmenu(JFactory::getApplication()->input->get('view'));
        */
    }
}
