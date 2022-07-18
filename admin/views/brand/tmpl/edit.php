<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));

$global_edit_fields = array(
    'id',
    'parent',
    'parent_id',
    'published',
    'state',
    'enabled',
    'category',
    'catid',
    'featured',
    'sticky',
    'access',
    'language',
    'tags',
    'note',
    'version_note'
);

$fieldsets = $this->form->getFieldsets();
?>
<?php JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true)); ?>
<form action="<?php echo JRoute::_('index.php?option=com_brands&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-validate"
    enctype="multipart/form-data">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <ul class="nav nav-tabs">
                <?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
                <li<?php echo $i == 1 ? ' class="active"' : ''; ?>><a href="#<?php echo $fieldset->name; ?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
            <?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
            <?php $form_fieldset = $this->form->getFieldset($fieldset->name); ?>
                <!-- Begin Tabs -->
                <div class="tab-pane<?php echo $i == 1 ? ' active' : ''; ?>" id="<?php echo $fieldset->name; ?>">
                    <div class="row-fluid">
                        <?php if ($fieldset->name == 'main'): ?>
                        <div class="span9"><?php else: ?><div class="span12">
                        <?php endif; ?>
                        <?php $hidden_fields = array(); foreach($form_fieldset as $field): if(!in_array($field->fieldname, $global_edit_fields)): ?>
                            <?php if($field->type == 'Hidden'){ $hidden_fields[] = $field->input; continue; } ?>
                            <?php if(!empty($field->getAttribute('hiddenLabel'))){ echo $field->input; continue; } ?>
                            <?php /* Following 'showon' taken from /layouts/joomla/content/options_default.php */ ?>
                            <?php $datashowon = ''; ?>
                            <?php $groupClass = $field->type === 'Spacer' ? ' field-spacer' : ''; ?>
                            <?php if ($field->showon) : ?>
                                <?php JHtml::_('jquery.framework'); ?>
                                <?php JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true)); ?>
                                <?php $datashowon = ' data-showon=\'' . json_encode(JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
                            <?php endif; ?>
                            <div class="control-group<?php echo $groupClass; ?>"<?php echo $datashowon; ?>>
                                <?php if ($field->type != 'Button'): ?>
                                <div class="control-label">
                                    <?php echo JText::_($field->label); ?>
                                    <?php if ($field->fieldname == 'logo_svg' && !empty($this->item->logo_svg_path)) : ?><br>
                                    <img src="<?php echo $this->item->logo_svg_path; ?>" alt="Logo: <?php echo $this->item->name; ?>" height="30" style="height: 30px; margin-bottom: 1em;" onerror="this.src='<?php echo $this->item->logo_png_path; ?>'; this.onerror=null;">
                                    <?php endif; ?>
                                    <?php if ($field->fieldname == 'icon_svg' && !empty($this->item->logo_svg_path)) : ?><br>
                                    <img src="data:image/svg+xml;base64,<?php echo base64_encode($this->item->icon_svg); ?>" alt="Logo: <?php echo $this->item->name; ?>" height="60" style="height: 60px; margin-bottom: 1em;">
                                    <?php endif; ?>
                                    <?php if ($field->fieldname == 'favicon_zip' && !empty($this->item->favicon_zip_path)) : ?><br>
                                    <a href="<?php echo $this->item->favicon_zip_path; ?>">Download Favicon Zip</a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div><!-- End control-group -->
                        <?php endif; endforeach; ?>

                        <?php if ($fieldset->name == 'main'): ?>
                        </div>
                        <div class="span3">
                        <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                        <?php endif; ?>

                        </div>
                        <?php echo implode("\n", $hidden_fields); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <input type="hidden" name="task" value="brand.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>
