<?php
/**
 * EditableDateRangeField
 *
 * Allows a user to add a date field.
 *
 * @package userforms
 */

class EditableDateRangeField extends EditableDateField
{
    
    private static $has_one = array(
        "LaterThan" => "EditableDateField",
        "EarlierThan" => "EditableDateField",
    );
    
    private static $singular_name = 'Date Range Field';
    
    private static $plural_name = 'Date Range Fields';
    
    public function getFieldValidationOptions()
    {
        $fields = parent::getFieldValidationOptions();
        
        $validEmailFields =  EditableDateField::get()->filter(array(
            'ParentID' => (int)$this->ParentID,
        ))->exclude(array(
            'ID' => (int)$this->ID,
        ));
        
        $fields->add(
            DropdownField::create(
                $this->getFieldName('LaterThan'),
                _t('EditableDateRangeField.MUSTBELATERTHAN', 'Must be later than'),
                $validEmailFields->map('ID', 'Title'),
                $this->LaterThanID
            )->setEmptyString('- select -')
        );
        
        $fields->add(
            DropdownField::create(
                $this->getFieldName('EarlierThan'),
                _t('EditableDateRangeField.MUSTBEEARLIERTHAN', 'Must be earlier than'),
                $validEmailFields->map('ID', 'Title'),
                $this->EarlierThanID
            )->setEmptyString('- select -')
        );
        
        return $fields;
    }
    
    public function populateFromPostData($data)
    {
        $this->EarlierThanID    = (isset($data['EarlierThan'])) ? $data['EarlierThan']: 0;
        $this->LaterThanID        = (isset($data['LaterThan'])) ? $data['LaterThan'] : 0;
        
        parent::populateFromPostData($data);
    }
    
    public function getFormField()
    {
        Requirements::customScript(<<<JS
jQuery.validator.addMethod("DateLaterThan", function(value, element, param) {
	if (this.optional(element)) {
		return true;
	}
	var label = $('label[for=' + param + ']').text();
	$(element).rules("remove", "DateLaterThan");
    $(element).rules("add", {
        DateLaterThan: param,
        messages: {
            DateLaterThan: jQuery.validator.format("Date needs to be after '{0}'.", label)
        }
    });
	var dateThis = $(element).val(); 
	var dateOther = $('#' + param).val();
	if (dateThis != '' && dateOther != '') { 
		dateThis = dateThis.split('/'); 
		dateThis = new Date(dateThis[2], dateThis[1]-1 , dateThis[0], 12, 0, 0, 0); 
		dateOther = dateOther.split('/'); 
		dateOther = new Date(dateOther[2], dateOther[1]-1 , dateOther[0], 12, 0, 0, 0); 
		return (dateThis.getTime() >= dateOther.getTime()); 
	}
	return false;
}, jQuery.validator.format("Date needs to be after '{0}'."));

jQuery.validator.addMethod("DateEarlierThan", function(value, element, param) {
	if (this.optional(element)) {
		return true;
	}
	var label = $('label[for=' + param + ']').text();
	$(element).rules("remove", "DateEarlierThan");
    $(element).rules("add", {
        DateEarlierThan: param,
        messages: {
            DateEarlierThan: jQuery.validator.format("Date needs to be before '{0}'.", label)
        }
    });
	var dateThis = $(element).val(); 
	var dateOther = $('#' + param).val();
	if (dateThis != '' && dateOther != '') { 
		dateThis = dateThis.split('/'); 
		dateThis = new Date(dateThis[2], dateThis[1]-1 , dateThis[0], 12, 0, 0, 0); 
		dateOther = dateOther.split('/'); 
		dateOther = new Date(dateOther[2], dateOther[1]-1 , dateOther[0], 12, 0, 0, 0); 
		return (dateThis.getTime() < dateOther.getTime()); 
	}
	return false;
}, jQuery.validator.format("Date needs to be before '{0}'."));
JS
, 'editabledaterangefield');
        return parent::getFormField();
    }
        
    /**
     * Return the validation information related to this field. This is 
     * interrupted as a JSON object for validate plugin and used in the 
     * PHP. 
     *
     * @see http://docs.jquery.com/Plugins/Validation/Methods
     * @return Array
     */
    public function getValidation()
    {
        $options = parent::getValidation();
        
        if ($this->LaterThanID) {
            $fieldid = "Form_Form_".$this->LaterThan()->getFormField()->ID();
            $options['DateLaterThan'] = $fieldid;
        }
            
        if ($this->EarlierThanID) {
            $fieldid = "Form_Form_".$this->EarlierThan()->getFormField()->ID();
            $options['DateEarlierThan'] = $fieldid;
        }
        
        return $options;
    }
    
    public function getIcon()
    {
        return USERFORMS_DIR . '/images/' . strtolower(get_parent_class($this)) . '.png';
    }
}
