<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brandprojects
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

//jimport('joomla.filesystem.path');
//JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * BrandProjects Record Model
 */
class BrandProjectsModelRecord extends JModelAdmin
{
    /**
	 * Copied from libraries/src/MVC/Model/AdminModel.php because it uses a hard-coded field name:
     * catid.
     * I've used pr_catid to help distiguish from generated/owned content category, but I may
     * rethink this.
     * 
     * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	1.7
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'pr_catid' => $category_id)))
		{
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}
    
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($type = 'BrandProjects', $prefix = 'BrandProjectsTable', $config = array())
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
            'com_brandprojects.record',
            'record',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form))
        {
            return false;
        }
        return $form;
    }
    
    /**
     * Method to get the script that have to be included on the form
     *
     * @return string   Script files
     */
    public function getScript() 
    {
        #return 'administrator/components/com_helloworld/models/forms/helloworld.js';
        return '';
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
        
        // The following is generally useful for any app, but you'll need to make sure the database
        // schema includes these fields:
        $user        = JFactory::getUser();
        $user_id     = $user->get('id');
        $date_format = 'Y-m-d H:i:s A';
        
        $prefix      = $is_new ? 'created' : 'modified';
        
        $data[$prefix]         = date($date_format, time()); // created/modified
        $data[$prefix . '_by'] = $user_id; // created_by/modified_by
        
        
        
        
        
        
        
        
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
        
        // Alter the name for save as copy
		if ($input->get('task') == 'save2copy') {
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));
           
			if ($data['name'] == $origTable->name) {
				list($title, $alias) = $this->generateNewTitle($data['pr_catid'], $data['alias'], $data['name']);
				$data['name'] = $title;
				$data['alias'] = $alias;
                
                
			} else {
				if ($data['alias'] == $origTable->alias) {
					$data['alias'] = '';
				}
			}

			$data['state'] = 0;
		}

        // Automatic handling of alias for empty fields
        // Taken from com_content/models/article.php
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
			if (empty($data['alias'])) {
				if (JFactory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['name']);
				} else {
					$data['alias'] = JFilterOutput::stringURLSafe($data['name']);
				}

				$table = JTable::getInstance('BrandProjects', 'BrandProjectsTable');

				if ($table->load(array('alias' => $data['alias'], 'pr_catid' => $data['pr_catid']))) {
					$msg = JText::_('COM_CONTENT_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewTitle($data['pr_catid'], $data['alias'], $data['name']);
				$data['alias'] = $alias;

				if (isset($msg)) {
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}
        
        
        // Need to create a menu item and add the new ID to the data if one doesn't exist:
        /* https://stackoverflow.com/questions/12651075/programmatically-create-menu-item-in-joomla
        
        // Need to act upon the selected menu type in order to generatate the correct link.
        // This would seem impossible to do to try and support every menu type so may have to
        // abandon the option to choose the link type here - I think it's too complicated.
        // Maybe just fall back to creating an unpublished heading, and provide a link to the 
        // menu item so it can chosen manually.
        
        
        //index.php?option=com_bespoke&view=bespoke
        //index.php?option=com_content&view=article&id=1668
        
        type = 'heading' (link can be empty)
        id	menutype	title	alias	note	path	link	type	published	parent_id	level	component_id	checked_out	checked_out_time	browserNav	access	img	template_style_id	params	lft	rgt	home	language	client_id
        962	mainmenu	Test Placeholder	test-placeholder	""	test-placeholder	""	heading	0	1	1	0	0	29/12/1899	0	1	""	0	"{""menu-anchor_title"":"""",""menu-anchor_css"":"""",""menu_image"":"""",""menu_image_css"":"""",""menu_text"":1,""menu_show"":1}"	1359	1360	0	*	0
        if () {
            
        }
        
        $link = 'index.php?option=com_content&view=article&id='.$resultID,
        
        $menuItem = array(
            'menutype' => 'mainmenu',
            'title'    => $data['name'],
            'alias'    => $data['alias'],
            'path'     =, $data['alias'],
            'link'     => $link,
            'type'     => 'component',
            
            'component_id' => 22,                  
            
            'language' => '*',
            'published' => 1,
            'parent_id' => $parent_id,
            'level' => 1,
        );

        $menuTable = JTable::getInstance('Menu', 'JTable', array());

        $menuTable->setLocation($parent_id, 'last-child');

        if (!$menuTable->save($menuItem)) {
            throw new Exception($menuTable->getError());
            return false;
        }
        
        */
        
        
        if (parent::save($data)) {
			/*if (isset($data['featured'])) {
				$this->featured($this->getState($this->getName() . '.id'), $data['featured']);
			}*/

			return true;
		}

		return false;
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
            'com_brandprojects.edit.records.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool  Email success/failed to send.
     */
    private function _sendEmail($email_data)
    {
            $app        = JFactory::getApplication();
            $mailfrom   = $app->getCfg('mailfrom');
            $fromname   = $app->getCfg('fromname');
            $sitename   = $app->getCfg('sitename');
            $email      = JStringPunycode::emailToPunycode($email_data['email']);           
            
            // Ref: JText::sprintf('LANG_STR', $var, ...);
                
            $mail = JFactory::getMailer();
            $mail->addRecipient($email);
            $mail->addReplyTo($mailfrom);
            $mail->setSender(array($mailfrom, $fromname));
            $mail->setSubject(JText::_('COM_BRANDPROJECTS_EMAIL_ADMINS_SUBJECT'));
            $mail->setBody(JText::_('COM_BRANDPROJECTS_EMAIL_ADMINS_BODY'));
            $sent = $mail->Send();

            return $sent;
    }
}
