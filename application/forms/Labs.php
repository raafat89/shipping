<?php

class Atlas_Form_Labs extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM ELEMENT ************************ */
        $lab_id = new Zend_Form_Element_Hidden("lab_id");
        $lab_id->setDecorators(array('ViewHelper'));

        /* LAB NAME TEXT FIELD ************************** */
        $lab_name = new Zend_Form_Element_Text("lab_name");
        $lab_name->setRequired(true)
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

        /* DROP DOWN FOR LAB STATUS *************************** */
        $lab_active = new Zend_Form_Element_Select("lab_active");
        $lab_active->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(0, 'Inactive')
                ->addMultiOption(1, 'Active');

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Submit');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO FORM ********************** */
        $this->addElements(array($lab_id, $lab_name, $lab_active, $submit));
    }

}

?>