<?php

class Utility_Decorator_RowWrap extends Zend_Form_Decorator_Abstract {

    public function render($content) {
        return "\n<tr>" . $content . "\n</tr>";
    }

}

?>