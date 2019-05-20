Joomla.submitbutton = function(task, type)
{
    console.log(task);
    if (task == '')
    {
        return false;
    }
    /*else if (task == 'item.setType')
    {
        console.log(JSON.parse(atob(type)));
        var parsed_type = JSON.parse(atob(type));
        var request = parsed_type.request;
        var type_string = request.option + '.' + request.view;
        //jQuery('#item-form input[name="jform[type]"]').val(type);
        //jQuery('#fieldtype').val('type');
        //Joomla.submitform('item.setType', document.getElementById('item-form'));
        //jQuery('#jform_landing_menutype').val(type_string);
        jQuery('#jform_landing_menutype').val('Single Article');
        jQuery('[name="jform[landing_menutype]"]').val('component');
        jQuery('#jform_component_id').val(22);
        return false;
    }*/
    else
    {
        var isValid = true;
        var action = task.split('.');
        if (action[1] != 'cancel' && action[1] != 'close')
        {
            var forms = $$('form.form-validate');
            for (var i=0;i<forms.length;i++)
            {
                if (!document.formvalidator.isValid(forms[i]))
                {
                    isValid = false;
                    break;
                }
            }
        }
 
        if (isValid)
        {
            Joomla.submitform(task);
            return true;
        }
        else
        {
            alert(Joomla.JText._('COM_BRANDS_RECORD_ERROR_UNACCEPTABLE',
                                 'Some values are unacceptable'));
            return false;
        }
    }
}