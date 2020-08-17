<?php

class Atlas_Form_PCStatusReasons extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM ELEMENTS ********************************* */
        $reason_id = new Zend_Form_Element_Hidden("reason_id");
        $reason_id->setDecorators(array('ViewHelper'));
        $this->addElement($reason_id);

        /* REASON *************************************** */
        $reason = new Zend_Form_Element_Text("reason");
        $reason->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setFilters(array('StringTrim', 'StripTags'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 170))
                ))
                ->setAttribs(array(
                    'maxlength' => '170',
                    'class' => 'form-element',
                    'size' => '50'
        ));
        $this->addElement($reason);

        /* DROP DOWN TO DESIGNATE STATUS ********* */
        $status = new Zend_Form_Element_Select("status");
        $status->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(0, "Inactive")
                ->addMultiOption(1, "Active");
        $this->addElement($status);

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Submit');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));
        $this->addElement($submit);
    }

}

?>