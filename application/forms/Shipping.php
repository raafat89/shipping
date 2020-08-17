<?php

class Atlas_Form_Shipping extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* TEXT LINE FOR SO NUMBER ********************* */
        $so_num = new Zend_Form_Element_Text("so_number");
        $so_num->setRequired(true)
                ->setFilters(array('StripTags', 'StringTrim'))
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(
                    array('NotEmpty', true),
                    array('StringLength', false, array(1, 35))
                ))
                ->setAttribs(array(
                    'maxlength' => '35',
                    'class' => 'form-element',
                    'size' => '50'
        ));

        /* SUBMIT BUTTON ****************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Generate');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO FORM ****************** */
        $this->addElements(array($so_num, $submit));
    }

}

?>