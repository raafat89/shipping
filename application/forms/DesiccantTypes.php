<?php

class Atlas_Form_DesiccantTypes extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM FIELDS *************************** */
        $field = new Zend_Form_Element_Hidden("desiccant_type_id");
        $field->setDecorators(array('ViewHelper'));
        $this->addElement($field);

        /* TEXT FIELD FOR UNIT LABEL ************ */
        $field = new Zend_Form_Element_Text("desiccant_type");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim', 'StripTags'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 35))
                ))
                ->setAttribs(array(
                    'maxlength' => '35',
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