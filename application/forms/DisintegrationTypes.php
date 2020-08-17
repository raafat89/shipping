<?php

class Atlas_Form_DisintegrationTypes extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM FIELDS *************************** */
        $field = new Zend_Form_Element_Hidden("disintegration_type_id");
        $field->setDecorators(array('ViewHelper'));
        $this->addElement($field);

        /* TEXT FIELD FOR UNIT LABEL ************ */
        $field = new Zend_Form_Element_Text("disintegration_type");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim', 'StripTags'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 125))
                ))
                ->setAttribs(array(
                    'maxlength' => '125',
                    'class' => 'form-element',
                    'size' => '50'
        ));
        $this->addElement($field);

        /* SUBMIT BUTTON **************************************** */
        $field = new Zend_Form_Element_Submit('submit', 'Submit');
        $field->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));
        $this->addElement($field);
    }

}

?>