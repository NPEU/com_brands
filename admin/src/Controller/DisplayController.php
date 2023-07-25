<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Brands\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;


/**
 * Brands Component Controller
 */
class DisplayController extends BaseController {
    protected $default_view = 'brands';

    public function display($cachable = false, $urlparams = []) {
        return parent::display($cachable, $urlparams);
    }
}