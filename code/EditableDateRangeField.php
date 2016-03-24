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
jQuery.validator.addMethod("datelaterthan", function(value, element, param) {
	if (this.optional(element)) {
		return true;
	}
	var label = $('label[for=' + param + ']').text();
	$(element).rules("remove", "datelaterthan");
    $(element).rules("add", {
        datelaterthan: param,
        messages: {
            datelaterthan: jQuery.validator.format("Date needs to be after '{0}'.", label)
        }
    });
	var dateThis = $(element).val();
	var dateOther = $('#' + param).val();
	if (dateThis != undefined && dateThis != '' && dateOther != undefined && dateOther != '') {
		dateThis = dateThis.split('/');
		dateThis = new Date(dateThis[2], dateThis[1]-1 , dateThis[0], 12, 0, 0, 0);
		dateOther = dateOther.split('/');
		dateOther = new Date(dateOther[2], dateOther[1]-1 , dateOther[0], 12, 0, 0, 0);
		return (dateThis.getTime() >= dateOther.getTime());
	}
	return false;
}, jQuery.validator.format("Date needs to be after '{0}'."));

jQuery.validator.addMethod("dateearlierthan", function(value, element, param) {
	if (this.optional(element)) {
		return true;
	}
	var label = $('label[for=' + param + ']').text();
	$(element).rules("remove", "dateearlierthan");
    $(element).rules("add", {
        dateearlierthan: param,
        messages: {
            dateearlierthan: jQuery.validator.format("Date needs to be before '{0}'.", label)
        }
    });
	var dateThis = $(element).val();
	var dateOther = $('#' + param).val();
	if (dateThis != undefined && dateThis != '' && dateOther != undefined && dateOther != '') {
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
    
    protected function updateFormField($field)
    {
    	parent::updateFormField($field);

    	if ($this->LaterThanID) {
    		$fieldid = "UserForm_Form_".$this->LaterThan()->getFormField()->ID();
			$field->setAttribute('data-rule-datelaterthan', $fieldid);
    	}
    	
    	if ($this->EarlierThanID) {
    		$fieldid = "UserForm_Form_".$this->EarlierThan()->getFormField()->ID();
			$field->setAttribute('data-rule-dateearlierthan', $fieldid);
    	}
    }
    
    public function getIcon()
    {
        return USERFORMS_DIR . '/images/' . strtolower(get_parent_class($this)) . '.png';
    }
}
