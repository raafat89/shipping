<?php

class Utility_PagePanel {

    public $pageCount;
    public $pagesInRange;
    public $previous;
    public $current;
    public $next;
    public $firstItemNumber;
    public $lastItemNumber;
    public $totalItemCount;
    public $itemCountPerPage;
    public $currentItemCount;

    public function __construct($type, $total_count, $cur_page, $items_per, $boundry) {
        $this->pageCount = ceil($total_count / $items_per);
        $this->totalItemCount = $total_count;
        $this->itemCountPerPage = $items_per;
        $this->firstItemNumber = 1;
        $this->lastItemNumber = $this->pageCount;
        $this->current = $cur_page;
        if ($cur_page < $this->pageCount) {
            $this->currentItemCount = $this->itemCountPerPage;
        } else {
            $this->currentItemCount = $total_count % $this->itemCountPerPage;
        }
        if ($cur_page > 1) {
            $this->previous = $cur_page - 1;
        }
        if ($cur_page < $this->pageCount) {
            $this->next = $cur_page + 1;
        }

        $this->pagesInRange = array();
        $lower_limit = $this->current;
        $upper_limit = $this->current;
        if (strtolower($type) == "sliding") {
            if ($this->currrent - ($boundry / 2) < 1) {
                $lower_limit = 1;
                $upper_limit += ($boundry / 2) - $this->current;
            } else {
                $lower_limit -= ($boundry / 2);
            }
            if ($this->current + ($boundry / 2) > $this->pageCount) {
                $upper_limit = $this->pageCount;
                $lower_limit -= (($boundry / 2) + $this->current) - $this->pageCount;
            } else {
                $uppper_limit += ($boundry / 2);
            }
        } else if (strtolower($type) == "elastic") {
            $lower_limit = $this->current - $boundry;
            $upper_limit = $this->current + $boundry;
        }
        for ($i = $lower_limit; $i <= $upper_limit; ++$i) {
            if ($i >= 1 && $i <= $this->pageCount) {
                $this->pagesInRange[] = $i;
            }
        }
    }

#end __construct function
}
