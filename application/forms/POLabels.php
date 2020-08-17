<?php

class Atlas_Form_POLabels extends Zend_Form {

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        /* SKU NUMBER TEXT LINE ************************* */
        $sku_num = new Zend_Form_Element_Text("sku_number");
        $sku_num->setRequired(true)
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

        /* SO NUMBER TEXT LINE ************************* */
        $so_num = new Zend_Form_Element_Text("so_number");
        $so_num->setRequired(true)
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

        /* TOTAL NUMBER OF LABELS TEXT LINE ******************** */
        $total_label = new Zend_Form_Element_Text("total_labels");
        $total_label->setRequired(true)
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

        /* SUBMIT BUTTON ****************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Generate');
        $submit->setAttrib('class', 'form-element')
                ->setDecorators(array('ViewHelper'));

        /* ADD ELEMENTS TO FORM ***************************** */
        $this->addElements(array($sku_num, $so_num, $total_label, $submit));
    }

}

?>