<?php

class Atlas_Form_PrintingLabelsBuyer extends Zend_Form {

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** HIDDEN FIELD FOR ID ***********************/
        $field = new Zend_Form_Element_Hidden("id");
        $field->setDecorators(array("ViewHelper"));
        $this->addElement($field);

        // ** UPC TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("upc");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(12, 12))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** ASIN TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("asin");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** SKU TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("sku");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }

}

?>