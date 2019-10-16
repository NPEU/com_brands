<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Brands Brand Model
 */
class BrandsModelBrand extends JModelAdmin
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($type = 'Brands', $prefix = 'BrandsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_brands.brand',
            'brand',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form))
        {
            return false;
        }

        // Determine correct permissions to check.
        if ($this->getState('brand.id'))
        {
            // Existing record. Can only edit in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit');
        }
        else
        {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data))
        {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_brands.edit.brand.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        $cat = JTable::getInstance('category');
        $cat->load($item->catid);
        $item->cat_alias = $cat->alias;
        return $item;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param   JTable  $table  A reference to a JTable object.
     *
     * @return  void
     */
    protected function prepareTable($table)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
        $table->alias = JApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias))
        {
            $table->alias = JApplicationHelper::stringURLSafe($table->name);
        }

        if (empty($table->id))
        {
            // Set the values
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }
    }

    /**
     * Method to prepare the saved data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     */
    public function save($data)
    {
        $is_new = empty($data['id']);
        $input  = JFactory::getApplication()->input;
        $app    = JFactory::getApplication();

        // Get parameters:
        $params = JComponentHelper::getParams(JRequest::getVar('option'));


        // By default we're only looking for and acting upon the 'email admins' setting.
        // If any other settings are related to this save method, add them here.
        /*$email_admins_string = $params->get('email_admins');
        if (!empty($email_admins_string) && $is_new) {
            $email_admins = explode(PHP_EOL, trim($email_admins_string));
            foreach ($email_admins as $email) {
                // Sending email as an array to make it easier to expand; it's quite likely that a
                // real app would need more info here.
                $email_data = array('email' => $email);
                $this->_sendEmail($email_data);
            }
        }*/

        // Alter the title for save as copy
        if ($app->input->get('task') == 'save2copy')
        {
            list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
            $data['name']  = $name;
            $data['alias'] = $alias;
            $data['state'] = 0;
        }

        // Automatic handling of alias for empty fields
        // Taken from com_content/models/article.php
        if (in_array($input->get('task'), array('apply', 'save', 'save2new'))) {
            if (empty($data['alias'])) {
                if (JFactory::getConfig()->get('unicodeslugs') == 1) {
                    $data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['name']);
                } else {
                    $data['alias'] = JFilterOutput::stringURLSafe($data['name']);
                }

                $table = JTable::getInstance('Brand', 'BrandsTable');

                if ($table->load(array('alias' => $data['alias']))) {
                    $msg = JText::_('COM_CONTENT_SAVE_WARNING');
                }

                #list($name, $alias) = $this->generateNewBrandsTitle($data['alias'], $data['name']);
                list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
                $data['alias'] = $alias;

                if (isset($msg)) {
                    JFactory::getApplication()->enqueueMessage($msg, 'warning');
                }
            }
        }

        return parent::save($data);
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $category_id  The id of the parent.
     * @param   string   $alias        The alias.
     * @param   string   $name         The title.
     *
     * @return  array  Contains the modified title and alias.
     */
    protected function generateNewTitle($category_id, $alias, $name)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
        {
            if ($name == $table->name)
            {
                $name = JString::increment($name);
            }

            $alias = JString::increment($alias, 'dash');
        }

        return array($name, $alias);
    }
}
