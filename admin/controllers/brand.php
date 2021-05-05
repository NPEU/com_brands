<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use SVG\SVG;

/**
 * Brands Brand Controller
 */
class BrandsControllerBrand extends JControllerForm
{

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     \JControllerLegacy
     * @throws  \Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->view_list = 'brands';
    }

    protected function styleToAttr($str) {
        preg_match_all('#style="([^"]*)"#', $str, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attribs = explode(';', trim($match[1], ';'));
            $attrib_str = '';
            foreach ($attribs as $attrib) {
                $attrib_str .= str_replace(': ', '="', $attrib) . '" ';
            }
            $str = str_replace($match[0], $attrib_str, $str);
        }

        return $str;
    }

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

        $upload_folder_permissions = octdec($params->get('upload_folder_permissions', false));
        $upload_file_permissions = octdec($params->get('upload_file_permissions', false));
        $upload_file_group       = $params->get('upload_file_group', false);
        $upload_file_owner       = $params->get('upload_file_owner', false);

        // We need to get the cat_alias if it's not set (it won't be for new saves and if it's not.
        // we get logo files generated in the wrong place).
        if (empty($data['cat_alias'])) {
            $cat = JTable::getInstance('category');
            $cat->load($data['catid']);
            $data['cat_alias'] = $cat->alias;
        }

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


        // Process SVG:
        if(!empty($data['logo_svg'])) {
            $svg = $data['logo_svg'];

            // Illustrator adds 'xml:space="preserve"'. It's easier to remove this as a string:
            $svg = str_replace('xml:space="preserve"', '', $svg);

            // Validate SVG:
            $svg_is_valid = true;

            function tmpErrorHandler($errno, $errstr, $errfile, $errline) {
                if (E_RECOVERABLE_ERROR === $errno) {
                    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
                }
                return false;
            }
            set_error_handler('tmpErrorHandler');

            try {
                $image = @SVG::fromString($svg);
            } catch(Exception $e) {
                $svg_is_valid = false;
            }

            restore_error_handler();
            ////

            if (!$svg_is_valid) {
                // Redirect and throw an error message:
                JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_SVG_INVALID')));
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;

            } else {
                $svg_errors = array();
                $svg_doc = $image->getDocument();


                // Tidy necessary attributes (e.g. from Illustrator):
                $svg_doc->removeAttribute('id');
                $svg_doc->removeAttribute('version');
                $svg_doc->removeAttribute('x');
                $svg_doc->removeAttribute('y');
                $svg_doc->removeStyle('enable-background');
                //$svg_doc->removeAttribute('xmlns:xml');


                $svg_xml_string = (string) $image;
                $svg_xml = new SimpleXMLElement($svg_xml_string);
                $svg_xml->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');

                $namespaces = $svg_xml->getNamespaces(true);

                $svg_id = empty($data['alias'])
                        ? $this->html_id($data['name'])
                        : $data['alias'];

                $doc_attributes = $svg_doc->getSerializableAttributes();
                $doc_title      = '';
                $doc_id         = '';
                $doc_title_id   = '';

                $doc_viewbox = $svg_doc->getViewBox();
                $doc_vwidth  = $doc_viewbox[2];
                $doc_vheight = $doc_viewbox[3];
                #$doc_ratio   = $doc_vwidth / $doc_vheight;

                // Note php-svg adds xmlns="http://www.w3.org/2000/svg" and
                // xmlns:xlink="http://www.w3.org/1999/xlink" if they're missing.

                // Rejection-level checks:
                // ----------------------


                // Does the SVG have a viewBox. We produce an error here because we can't infer it:
                if (!array_key_exists('viewBox', $doc_attributes)) {
                    $svg_errors[] = 'COM_BRANDS_ERROR_SVG_MISSING_VIEWBOX';
                }


                // Does the SVG have a title:
                //$title = $svg_xml->xpath("//svg:title");
                $title = $svg_doc->getElementsByTagName('title')[0];
                if (is_null($title)) {
                    $svg_errors[] = 'COM_BRANDS_ERROR_SVG_MISSING_TITLE';
                } elseif (($doc_title = $title->getValue()) == '') {
                    $svg_errors[] = 'COM_BRANDS_ERROR_SVG_EMPTY_TITLE';
                }

                // Does the SVG contain an image? We'll be handling this in code, so reject ones that already
                // have an image present.
                $result = $svg_xml->xpath("//svg:image");

                if (count($result) !== 0) {
                    $svg_errors[] = 'COM_BRANDS_ERROR_SVG_HAS_IMAGE';
                }

                // Inference-level checks:
                // ----------------------

                if (count($svg_errors) == 0 ) {
                    // Passed all rejection checks, so continue to process the SVG:

                    // Add a role of img to SVG if not present:
                    if (!array_key_exists('role', $doc_attributes) || $doc_attributes['role'] != 'img') {
                       $svg_doc->setAttribute('role', 'img');
                    }

                    // Add focusable false to SVG if not present:
                    if (!array_key_exists('focusable', $doc_attributes) || $doc_attributes['focusable'] != 'false') {
                       $svg_doc->setAttribute('focusable', 'false');
                    }

                    // Add an id to TITLE if not present:
                    if (($doc_id = $title->getAttribute('id')) == '') {
                        $doc_id = $svg_id;
                        $doc_title_id = $doc_id . '--title';
                        $title->setAttribute('id', $doc_title_id);
                    } else {
                        $doc_title_id = $doc_id;
                    }

                    // Set aria-labelledby attribute of SVG to TITLE id:
                    // (we might as well do this even if it already exists and is the same)
                    $svg_doc->setAttribute('aria-labelledby',  $doc_title_id);





                    // Generate a fallback PNG and add the IMAGE to the SVG:
                    // php-svg does a terrible job of rasterising at the moment, unfortunately, but
                    // this is how to do it: (note 4x image size helps improve aliasing)
                    #$raster = $image->toRasterImage($doc_viewbox[2] * 4, $doc_viewbox[3] * 4);
                    #imagepng($raster, $doc_id . '.png', 0);

                    $svg_doc->setAttribute('height', ($doc_vheight * 4));
                    $svg_doc->setAttribute('width', ($doc_vwidth * 4));

                    $logos_root_folder = trim($params->get('logo_folder'), '/');
                    #$logos_root_folder   = 'img';
                    $logos_public_folder = '/' . $logos_root_folder  . '/' . $data['cat_alias'] . '/';
                    $logos_server_folder = $_SERVER['DOCUMENT_ROOT'] .  $logos_public_folder;

                    $svg_filename = preg_replace('#' . $params->get('logo_file_suffix') . '$#', '', $svg_id) . $params->get('logo_file_suffix') . '.svg';
                    $png_filename = str_replace('.svg', '.png', $svg_filename);

                    $svg_path = $logos_server_folder . $svg_filename;
                    $png_path = $logos_server_folder . $png_filename;

                    if (!file_exists($logos_server_folder)) {
                        mkdir($logos_server_folder, 0775, true);

                        // Set the folder to our preferred permissions:
                        if ($upload_folder_permissions) {
                            chmod($logos_server_folder, $upload_folder_permissions);
                        }

                        // Set the folder to belong to our preferred group:
                        if ($upload_file_group) {
                            chgrp($logos_server_folder, $upload_file_group);
                        }

                        // Set the folder to belong to our preferred owner:
                        if ($upload_file_owner) {
                            chown($logos_server_folder, $upload_file_owner);
                        }
                    }

                    // Temporarily write the svg to a file:
                    file_put_contents($svg_path, $image->toXMLString());


                    $im = new Imagick();
                    $im->readImageBlob(file_get_contents($svg_path));
                    $im->setImageFormat("png24");
                    $im->writeImage($png_path);
                    $im->clear();
                    $im->destroy();

                    // Did the file get generated?
                    if (file_exists($png_path)) {
                        // Set the file to our preferred permissions:
                        if ($upload_file_permissions) {
                            chmod($png_path, $upload_file_permissions);
                        }

                        // Set the file to belong to our preferred group:
                        if ($upload_file_group) {
                            chgrp($png_path, $upload_file_group);
                        }

                        // Set the file to belong to our preferred owner:
                        if ($upload_file_owner) {
                            chown($png_path, $upload_file_owner);
                        }
                    }

                    // Finish off the SVG - note the template should use file_get_contents from the
                    // generated SVG, the data['logo_svg'] that's stored in the database should be
                    // pre-finalisation to avoid errors during subsequent saves.

                    // Reset attributes:
                    $svg_doc->removeAttribute('width');
                    $svg_doc->removeAttribute('height');

                    $data['logo_svg']      = BrandsHelper::tidySVG($this->styleToAttr($image->toXMLString(false)));
                    $data['logo_svg_path'] = $logos_public_folder . $svg_filename;
                    $data['logo_png_path'] = $logos_public_folder . $png_filename;


                    // Override generated SVG with final output, without fallback and height for
                    // img tag use:
                    file_put_contents($svg_path, $this->styleToAttr($image->toXMLString(false)));

                    // Did the file get generated?
                    if (file_exists($svg_path)) {

                        // Set the file to our preferred permissions:
                        if ($upload_file_permissions) {
                            chmod($svg_path, $upload_file_permissions);
                        }

                        // Set the file to belong to our preferred group:
                        if ($upload_file_group) {
                            chgrp($svg_path, $upload_file_group);
                        }

                        // Set the file to belong to our preferred owner:
                        if ($upload_file_owner) {
                            chown($svg_path, $upload_file_owner);
                        }
                    }

                    $svg_doc->setAttribute('height', $params->get('logo_image_height'));

                    // Add the fallback image tag for the generated SVG:
                    $svg_doc->addChild(new \SVG\Nodes\Embedded\SVGImage(''));
                    $img = $svg_doc->getElementsByTagName('image')[0];
                    $img->setAttribute('src', $logos_public_folder . $png_filename);
                    $img->setAttribute('alt', 'Logo: ' . $doc_title);
                    $img->setAttribute('height', $params->get('logo_image_height'));

                    // Add the logo with fallback to the data for direct HTML use:
                    $data['logo_svg_with_fallback'] = BrandsHelper::tidySVG($this->styleToAttr($image->toXMLString(false)));

                    #echo '<pre>'; var_dump($data); echo '</pre>'; exit;
                } else {
                    // Redirect and throw an error message:
                    foreach ($svg_errors as $svg_error) {
                        JError::raiseWarning(100, JText::_($svg_error));
                    }
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
        }

        // Process Favicon:
        $favicon_filename =  $files['favicon_zip']['name'];

        if(!empty($favicon_filename)) {
            $max = $this->return_bytes(ini_get('upload_max_filesize'));

            if ($files['favicon_zip']['size'] > $max) {
                // Redirect and throw an error message:
                JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_ZIP_TOO_LARGE'), $favicon_filename, ini_get('upload_max_filesize')));
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;
            }

            $accept_types = explode(',', str_replace(', ', ',', $form->getFieldAttribute('favicon_zip', 'accept')));

            if (in_array($files['favicon_zip']['type'], $accept_types)) {

                $favicon_zip_upload_root_folder = trim($params->get('favicon_zip_upload_folder'), '/');
                #$favicon_zip_upload_root_folder = 'templates/npeu6/favicon';
                $brand_favicon_folder = $favicon_zip_upload_root_folder . '/' . $svg_id . '/';
                $dest_folder = $_SERVER['DOCUMENT_ROOT'] . '/' . $brand_favicon_folder;

                if (!file_exists($dest_folder)) {
                    mkdir($dest_folder);

                    // Set the folder to our preferred permissions:
                    if ($upload_folder_permissions) {
                        chmod($dest_folder, $upload_folder_permissions);
                    }

                    // Set the folder to belong to our preferred group:
                    if ($upload_file_group) {
                        chgrp($dest_folder, $upload_file_group);
                    }

                    // Set the folder to belong to our preferred owner:
                    if ($upload_file_owner) {
                        chown($dest_folder, $upload_file_owner);
                    }
                }

                $src  = $files['favicon_zip']['tmp_name'];
                $dest = $dest_folder . $favicon_filename;


                if (JFile::upload($src, $dest)) {

                    // Set the file to our preferred permissions:
                    if ($upload_file_permissions) {
                        chmod($dest, $upload_file_permissions);
                    }

                    // Set the file to belong to our preferred group:
                    if ($upload_file_group) {
                        chgrp($dest, $upload_file_group);
                    }

                    // Set the file to belong to our preferred owner:
                    if ($upload_file_owner) {
                        chown($dest, $upload_file_owner);
                    }

                    // Unzip to folder:
                    $zip = new ZipArchive;
                    if ($zip->open($dest) === true) {
                        for($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            $fileinfo = pathinfo($filename);
                            #echo '<pre>'; var_dump($brand_favicon_folder . $fileinfo['basename']); echo '</pre>';
                            copy('zip://' . $dest . '#' . $filename, $dest_folder . $fileinfo['basename']);

                            // Set the file to our preferred permissions:
                            if ($upload_file_permissions) {
                                chmod($dest_folder . $fileinfo['basename'], $upload_file_permissions);
                            }

                            // Set the file to belong to our preferred group:
                            if ($upload_file_group) {
                                chgrp($dest_folder . $fileinfo['basename'], $upload_file_group);
                            }

                            // Set the file to belong to our preferred owner:
                            if ($upload_file_owner) {
                                chown($dest_folder . $fileinfo['basename'], $upload_file_owner);
                            }
                        }
                        $zip->close();
                        $data['favicon_zip_path'] = '/' . $brand_favicon_folder . $favicon_filename;
                    } else {
                        // Redirect and throw an error message:
                        JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_FAILED_UNZIP'), $favicon_filename, $brand_favicon_folder));
                        $app->setUserState($context . '.data', $data);
                        $this->setRedirect(
                            JRoute::_(
                                'index.php?option=' . $option . '&view=' . $view_item
                                . $this->getRedirectToItemAppend($recordId, $key), false
                            )
                        );
                        return false;
                    }

                    // Add binary to data:
                    // @TODO - still undecided about this


                    // Add success message:
                    $app->enqueueMessage(sprintf(JText::_('COM_BRANDS_MESSAGE_SUCCESS'), $favicon_filename, $brand_favicon_folder));
                } else {
                    // Redirect and throw an error message:
                    JError::raiseWarning(100, sprintf(JText::_('COM_BRANDS_ERROR_FAILED_UPLOAD'), $favicon_filename));
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
                //Redirect and notify user file is not right extension:
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
    protected function return_bytes($val)
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

    /**
     * Strips punctuation from a string.
     *
     * @param   string   $text.
     */
    protected function strip_punctuation($text) {
        if (!is_string($text)) {
            trigger_error('Function \'strip_punctuation\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $text = html_entity_decode($text, ENT_QUOTES);

        $urlbrackets = '\[\]\(\)';
        $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
        $urlspaceafter = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
        $urlall = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

        $specialquotes = '\'"\*<>';

        $fullstop = '\x{002E}\x{FE52}\x{FF0E}';
        $comma = '\x{002C}\x{FE50}\x{FF0C}';
        $arabsep = '\x{066B}\x{066C}';
        $numseparators = $fullstop . $comma . $arabsep;

        $numbersign = '\x{0023}\x{FE5F}\x{FF03}';
        $percent = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
        $prime = '\x{2032}\x{2033}\x{2034}\x{2057}';
        $nummodifiers = $numbersign . $percent . $prime;
        $return = preg_replace(
        array(
            // Remove separator, control, formatting, surrogate,
            // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
            // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
            $numseparators . $urlall . $nummodifiers . '])/u',
            // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
            // Remove special quotes, dashes, connectors, number
            // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
            '\p{Pd}\p{Pc}]+((?= )|$)/u',
            // Remove special quotes, connectors, and URL characters
            // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
            // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
            // Remove consecutive spaces
            '/ +/',
            ), ' ', $text);
        $return = str_replace('/', '_', $return);
        return str_replace("'", '', $return);
    }

    /**
     * Creates a suitable id/alias from a string.
     *
     * @param   string   $text.
     */
    protected function html_id($text) {
        if (!is_string($text)) {
            trigger_error('Function \'html_id\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }

        $return = trim(str_replace(' ', '-', strtolower(JFile::makeSafe($text))));
        #$return = strtolower(trim(preg_replace('/\s+/', '-', $this->strip_punctuation($text))));
        return $return;
    }

}
