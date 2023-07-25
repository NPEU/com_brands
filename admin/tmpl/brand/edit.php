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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');

/* Following 'showon' taken from /layouts/joomla/content/options_default.php */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

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

$app = Factory::getApplication();
$input = $app->input;

#$this->ignore_fieldsets = array('details', 'images', 'item_associations', 'jmetadata');
$this->useCoreUI = true;

$fieldsets = $this->form->getFieldsets();
$field_types_full_width = [
    'Button',
    'Rules'
];
$field_types_no_label = [
    'Button'
];
?>

<form action="<?php echo Route::_('index.php?option=com_brands&layout=edit&id=' . $this->item->id); ?>"
    method="post"
    name="adminForm"
    id="brand-form"
    class="form-validate">

    <?php #echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'main')); ?>

        <?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
        <?php $form_fieldset = $this->form->getFieldset($fieldset->name); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_($fieldset->label)); ?>

        <div class="row">
            <?php if ($fieldset->name == 'main'): ?>
            <div class="col-xl-9"><?php else: ?><div class="col-12"><?php endif; ?>
                <?php $hidden_fields = array(); foreach($form_fieldset as $field): if(!in_array($field->fieldname, $global_edit_fields)): ?>
                <?php if($field->type == 'Hidden'){$hidden_fields[] = $field->input; continue;} ?>
                <?php if(!empty($field->getAttribute('hiddenLabel'))){ echo $field->input; continue; } ?>

                <?php /* Following 'showon' taken from /layouts/joomla/content/options_default.php */ ?>
                <?php $datashowon = ''; ?>
                <?php $groupClass = $field->type === 'Spacer' ? ' field-spacer' : ''; ?>
                <?php if ($field->showon) : ?>
                    <?php $wa->useScript('showon'); ?>
                    <?php $datashowon = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
                <?php endif; ?>

                <div class="control-group"<?php echo $datashowon; ?>>

                    <div class="control-label">
                        <?php if (!in_array($field->type, $field_types_no_label)): ?>
                        <?php echo Text::_($field->label); ?>
                        <?php endif; ?>
                        <?php if ($field->fieldname == 'logo_svg' && !empty($this->item->logo_svg_path)) : ?><br><br>
                        <img src="<?php echo $this->item->logo_svg_path; ?>" alt="Logo: <?php echo $this->item->name; ?>" height="30" style="height: 30px; margin-bottom: 1em;" onerror="this.src='<?php echo $this->item->logo_png_path; ?>'; this.onerror=null;">
                        <?php endif; ?>
                        <?php if ($field->fieldname == 'icon_svg' && !empty($this->item->icon_svg)) : ?><br><br>
                        <img src="data:image/svg+xml;base64,<?php echo base64_encode($this->item->icon_svg); ?>" alt="Logo: <?php echo $this->item->name; ?>" height="60" style="height: 60px; margin-bottom: 1em;">
                        <?php endif; ?>
                        <?php if ($field->fieldname == 'favicon_zip' && !empty($this->item->favicon_zip_path)) : ?><br><br>
                        <a href="<?php echo $this->item->favicon_zip_path; ?>">Download Favicon Zip</a>
                        <?php endif; ?>
                        </div>
                    <div class="controls"<?php if (in_array($field->type, $field_types_full_width)): ?> style="flex-basis:100%;"<?php endif; ?>>
                        <?php echo $field->input; ?>
                    </div>
                </div>

                <?php endif; endforeach; ?>

            <?php if ($fieldset->name == 'main'): ?>
            </div>
            <div class="col-xl-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
            <?php endif; ?>
            </div>

        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endforeach; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
    <?php echo implode("\n", $hidden_fields); ?>
    <input type="hidden" name="task" value="brand.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>