<?php

class Atlas_Form_ShippingSettings extends Zend_Form {

    protected $_products;

    public function __construct($products) {
        $this->_products = $products;

        parent::__construct();
    }

    public function init() {
        /* FORM META DATA ************************* */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements');
        $this->addDecorator('Form');

        foreach ($this->_products as $product) { // loop through all given items
            /* HIDDEN FORM ELEMENT ******************************************* */
            $item = new Zend_Form_Element_Hidden('code_' . $product['ProductId']);
            $item->setValue($product['Code']);
            // add product code to form
            $this->addElement($item);

            // add qty_case element
            $Qty_Case = $product['Qty_Case'];
            if (!isset($product['Qty_Case']) || $product['Qty_Case'] == "") {
                $Qty_Case = 0;
            }
            /* TEXT LINE FOR QTY PER CASE ********************************** */
            $item1 = new Zend_Form_Element_Text("qty_" . $product['ProductId']);
            $item1->setRequired(true)
                    ->setDecorators(array('ViewHelper'))
                    ->setFilters(array('StringTrim', 'StripTags'))
                    ->setValidators(array(
                        'Digits',
                        array('NotEmpty', true),
                        array('StringLength', false, array(1, 35))
                    ))
                    ->setAttribs(array(
                        'maxlength' => '35',
                        'class' => 'form-element',
                        'size' => '15'
                    ))
                    ->setValue($Qty_Case);
            // add qty per case to form
            $this->addElement($item1);

            // add UPC element
            $UPC = $product['UPC'];
            if (!isset($product['UPC']) || $product['UPC'] == "") {
                $UPC = "";
            }
            /* TEXT LINE FOR UPC CODE ************************************** */
            $item2 = new Zend_Form_Element_Text("upc_" . $product['ProductId']);
            $item2->setRequired(true)
                    ->setDecorators(array('ViewHelper'))
                    ->setFilters(array('StringTrim', 'StripTags'))
                    ->setValidators(array(
                        'Digits',
                        array('NotEmpty', true),
                        array('StringLength', false, array(1, 35))
                    ))
                    ->setAttribs(array(
                        'maxlength' => '35',
                        'class' => 'form-element',
                        'size' => '15'
                    ))
                    ->setValue($UPC);
            // add upc to form
            $this->addElement($item2);
        }

        /* SUBMIT BUTTON **************************************** */
        $submit = new Zend_Form_Element_Submit('submit', 'Update');
        $submit->setAttrib('class', 'form-element');
        $submit->setDecorators(array('ViewHelper'));

        // add submit button to form
        $this->addElement($submit);
    }

}

?>