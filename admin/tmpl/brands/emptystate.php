<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_BRANDS',
    'formURL'    => 'index.php?option=com_brands',
];

/*
$displayData = [
    'textPrefix' => 'COM_BRANDS',
    'formURL'    => 'index.php?option=com_brands',
    'helpURL'    => '',
    'icon'       => 'icon-globe brands',
];
*/

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_brands') || count($user->getAuthorisedCategories('com_brands', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_brands&task=brand.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);