<?php

class Atlas_Form_Units extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM FIELDS *************************** */
        $unit_id = new Zend_Form_Element_Hidden("unit_id");
        $unit_id->setDecorators(array('ViewHelper'));

        /* TEXT FIELD FOR UNIT LABEL ************ */
        $unit = new Zend_Form_Element_Text("unit");
        $unit->setRequired(true)
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

        /* DROP DOWN TO DESIGNATE UNIT STATUS ********* */
        $active = new Zend_Form_Element_Select("active");
        $active->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(0, 'Inactive')
                ->addMultiOption(1, 'Active');

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Submit');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO FORM ********* */
        $this->addElements(array($unit_id, $unit, $active, $submit));
    }

}

?>