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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Associations;


$user    = Factory::getApplication()->getIdentity();
$user_id = $user->get('id');
#$this->document->getWebAssetManager()->useScript('com_brands.enable-tooltips');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_brands&view=brands'); ?>" method="post" id="adminForm" name="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                <div class="alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
                <?php else : ?>
                <table class="table" id="brandsList">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_BRANDS_TABLE_CAPTION'); ?>,
                        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                    </caption>
                    <thead>
                        <tr>
                            <td class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <th class="w-1 text-center">
                                <?php echo Text::_('COM_BRANDS_NUM'); ?>
                            </th>
                            <th scope="col" style="min-width:85px" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_BRANDS_RECORDS_TITLE', 'a.name', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo JText::_('COM_BRANDS_RECORDS_LOGO'); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_BRANDS_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                        <?php $item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_brandss&task=category.edit&id=' . $item->catid); ?>
                        <?php $canCreate      = $user->authorise('core.create',     'com_brands.category.' . $item->catid); ?>
                        <?php $canEdit        = $user->authorise('core.edit',       'com_brands.category.' . $item->catid); ?>
                        <?php $canCheckin     = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0; ?>
                        <?php $canEditOwn     = $user->authorise('core.edit.own',   'com_brands.category.' . $item->catid) && $item->created_by == $user->id; ?>
                        <?php $canChange      = $user->authorise('core.edit.state', 'com_brands.category.' . $item->catid) && $canCheckin; ?>

                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td>
                                <?php echo $this->pagination->getRowOffset($i); ?>
                            </td>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'brands.', $canChange, 'cb'); ?>
                            </td>
                            <th scope="row" class="has-context">
                                <div>
                                    <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'brands.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit || $canEditOwn) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_brands&task=brand.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                        <?php echo $this->escape($item->name); ?>
                                    </a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->name); ?>
                                    <?php endif; ?>
                                    <span class="small">
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    </span>
                                    <div class="small">
                                        <?php echo JText::_('JCATEGORY') . ': ' . (empty($item->category_title) ? 'none' : '<a href="' . $item->cat_link . '" target="_blank">' . $this->escape($item->category_title) . '</a>'); ?>
                                    </div>
                                </div>
                            </th>
                            <td class="text-center">
                                <?php if (!empty($item->logo_svg_path)) : ?>
                                <img src="<?php echo $item->logo_svg_path; ?>" alt="Logo: <?php echo $item->name; ?>" height="30" style="height: 30px" onerror="this.src='<?php echo $item->logo_png_path; ?>'; this.onerror=null;">
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $item->id; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif;?>

                <?php // Load the pagination. ?>
                <?php echo $this->pagination->getListFooter(); ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>