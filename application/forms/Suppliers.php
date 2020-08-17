<?php

class Atlas_Form_Suppliers extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* HIDDEN FORM ELEMENTS ********************************* */
        $supplier_id = new Zend_Form_Element_Hidden("supplier_id");
        $supplier_id->setDecorators(array('ViewHelper'));

        /* TEXT LINE FOR SUPPLIER LABEL *************************** */
        $supplier_name = new Zend_Form_Element_Text("supplier_name");
        $supplier_name->setRequired(true)
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

        /* TEXT LINE FOR SUPPLIER CODE *************************** */
        $supplier_code = new Zend_Form_Element_Text("supplier_code");
        $supplier_code->setRequired(true)
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

        /* DROP DOWN TO DESIGNATE THE SUPPLIER STATUS ******************* */
        $supplier_active = new Zend_Form_Element_Select("supplier_active");
        $supplier_active->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(0, 'No')
                ->addMultiOption(1, 'Yes');

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Submit');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO THE FORM ************************** */
        $this->addElements(array($supplier_id, $supplier_name, $supplier_code, $supplier_active, $submit));
    }

}

?>