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
 * Brands Record Controller
 */
class BrandsControllerRecord extends JControllerForm
{
    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // The parent save method handles everything expect the file upload, so handle that first:
        $app       = JFactory::getApplication();
        $model     = $this->getModel();
        $table     = $model->getTable();
        $option    = $this->option;
        $context   = "$option.edit.$this->context";
        $form      = $model->getForm();
        $control   = $form->getFormControl();
        $files     = $app->input->files->get($control);
        $params    = clone JComponentHelper::getParams($option);
        $data      = $app->input->post->get($control, array(), 'array');
        $view_item = $this->view_item;

        // SNIP: Taken from libraries/src/MVC/Controller/FormController.php save method, because we
        // can't call that first.

        // Determine the name of the primary key for the data.
		if (empty($key)) {
			$key = $table->getKeyName();
		}

        // To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar)) {
			$urlVar = $key;
		}

        $recordId = $this->input->getInt($urlVar);
        // End SNIP

        $favicon_filename =  $files['favion_zip']['name'];

        if(!empty($favicon_filename)) {
            $max = $this->return_bytes(ini_get('upload_max_filesize'));

            if ($files['favion_zip']['size'] > $max) {
                JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_TOO_LARGE'), $favicon_filename, ini_get('upload_max_filesize')));

                // Redirect back to the edit screen.
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;
            }

            $accept_types = explode(',', str_replace(', ', ',', $form->getFieldAttribute('favion_zip', 'accept')));

            if (in_array($files['favion_zip']['type'], $accept_types)) {

                #$favicon_zip_upload_root_folder = trim($params->get('favicon_zip_upload_folder'), '/');
                $favicon_zip_upload_root_folder = 'templates/npeu6/favicon';
                $brand_pathname = str_replace(' ', '-', strtolower(JFile::makeSafe($data['name'])));
                $brand_favicon_folder = $favicon_zip_upload_root_folder . '/' . $brand_pathname . '/';
                $dest_folder = $_SERVER['DOCUMENT_ROOT'] . '/' . $brand_favicon_folder;

                if (!file_exists($dest_folder)) {
                    mkdir($dest_folder);
                }

                $src  = $files['favion_zip']['tmp_name'];
                $dest = $dest_folder . $favicon_filename;

                $data['favicon_zip_path'] = '/' . $brand_favicon_folder . $favicon_filename;

                if (JFile::upload($src, $dest)) {

                    // Unzip to folder:
                    $zip = new ZipArchive;
                    if ($zip->open($dest) === true) {
                        for($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            $fileinfo = pathinfo($filename);
                            echo '<pre>'; var_dump($brand_favicon_folder . $fileinfo['basename']); echo '</pre>';
                            copy('zip://' . $dest . '#' . $filename, $dest_folder . $fileinfo['basename']);
                        }
                        $zip->close();
                    } else {
                        JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_FAILED_UNZIP'), $favicon_filename, $brand_favicon_folder));
                    }

                    // Add binary to data:
                    // @TODO - still undecided about this


                    // Add success message:
                    $app->enqueueMessage(sprintf(JText::_('COM_BRANDS_MESSAGE_SUCCESS'), $favicon_filename, $brand_favicon_folder));
                } else {
                    // Redirect and throw an error message
                    JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_FAILED_UPLOAD'), $favicon_filename));

                    // Redirect back to the edit screen.
                    $app->setUserState($context . '.data', $data);
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $option . '&view=' . $view_item
                            . $this->getRedirectToItemAppend($recordId, $key), false
                        )
                    );

                    return false;
                }
            } else {
                //Redirect and notify user file is not right extension
                JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_WRONG_TYPE'), $favicon_filename));
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;
            }
        }

        // Update the post data so that it includes new values:
        $app->input->post->set($control, $data);

        // Call the parent:
        return parent::save($key, $urlVar);
    }


    /**
     * Converts filesize string to real bytes.
     *
     * @param   string   $val  Filesize string.
     */
    public function return_bytes($val)
    {
        if (empty($val)) {
            return 0;
        }

        $val = trim($val);

        preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);

        $last = '';
        if (isset($matches[2])) {
            $last = $matches[2];
        }

        if (isset($matches[1])) {
            $val = (int) $matches[1];
        }

        switch (strtolower($last)) {
            case 'g':
            case 'gb':
                $val *= 1024;
            case 'm':
            case 'mb':
                $val *= 1024;
            case 'k':
            case 'kb':
                $val *= 1024;
        }

        return (int) $val;
    }
}
