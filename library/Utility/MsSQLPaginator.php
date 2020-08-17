<?php

class Utility_MsSQLPaginator {

    protected $_mapper;
    protected $_original_query;
    protected $_count_query;
    protected $_items_per_page;
    protected $_current_page;
    protected $_pagination_type;
    protected $_partial_location;
    protected $_pages_panel;
    protected $_data;

    public function __construct($query, $mapper, $count, $page, $output_type, $partial) {
        if ((int) $page <= 0) {
            $page = 1;
        }

        $this->_original_query = $query;
        $this->_mapper = $mapper;
        $this->_items_per_page = $count;
        $this->_current_page = $page;
        $this->_pagination_type = $output_type;
        $this->_partial_location = $partial;

        $this->buildQueries();
        $this->buildPagePanel();
        $this->buildData();
    }

#end __construct function

    private function buildQueries() {
        // GET THE STARTING POSITIONS OF ALL CLAUSES
        $select_start = strpos($this->_original_query, "SELECT");
        $from_start = strpos($this->_original_query, "FROM");
        $where_start = strpos($this->_original_query, "WHERE");
        $group_start = strpos($this->_original_query, "GROUP");
        $having_start = strpos($this->_original_query, "HAVING");
        $order_start = strpos($this->_original_query, "ORDER");

        if (!isset($select_start) || !isset($from_start) || !isset($order_start)) {
            throw new Exception("This implementation of the paginator requires a SELECT, FROM and ORDER BY clause.");
        }

        // EXTRACT EACH OF THE CLAUSES
        // extract the select clause
        $select_clause = substr($this->_original_query, $select_start, $from_start - 1);
        // extract the from clause
        if (isset($where_start) && $where_start > 0) {
            $from_clause = substr($this->_original_query, $from_start, ($where_start - $from_start));
        } else if (isset($group_start) && $group_start > 0 && $group_start < $having_start) {
            $from_clause = substr($this->_original_query, $from_start, ($group_start - $from_start));
        } else if (isset($having_start) && $having_start > 0) {
            $from_clause = substr($this->_original_query, $from_start, ($having_start - $from_start));
        } else if (isset($order_start) && $order_start > 0) {
            $from_clause = substr($this->_original_query, $from_start, ($order_start - $from_start));
        } else {
            throw new Exception("This implementation of the paginator requires a SELECT, FROM and ORDER BY clause.");
        }
        // extract the where clause
        if (isset($where_start) && $where_start > 0) {
            $where_flag = true;
            if (isset($group_start) && $group_start > 0 && $group_start < $having_start) {
                $where_clause = substr($this->_original_query, $where_start, ($group_start - $where_start));
            } else if (isset($having_start) && $having_start > 0) {
                $where_clause = substr($this->_original_query, $where_start, ($having_start - $where_start));
            } else if (isset($order_start) && $order_start > 0) {
                $where_clause = substr($this->_original_query, $where_start, ($order_start - $where_start));
            } else {
                throw new Exception("This implementation of the paginator requires a SELECT, FROM and ORDER BY clause.");
            }
        } else {
            $where_flag = false;
        }
        // extract the group clause
        if (isset($group_start) && $group_start > 0) {
            $group_flag = true;
            if (isset($having_start) && $having_start > 0 && $group_start < $having_start) {
                $group_clause = substr($this->_original_query, $group_start, ($having_start - $group_start));
            } else if (isset($order_start) && $order_start > 0) {
                $group_clause = substr($this->_original_query, $group_start, ($order_start - $group_start));
            } else {
                throw new Exception("This implementation of the paginator requires a SELECT, FROM and ORDER BY clause.");
            }
        } else {
            $group_flag = false;
        }
        // extract the having clause
        if (isset($having_start) && $having_start > 0) {
            $having_flag = true;
            if (isset($group_start) && $group_start > 0 && $group_start > $having_start) {
                $having_clause = substr($this->_original_query, $having_start, ($group_start - $having_start));
            } else if (isset($order_start) && $order_start > 0) {
                $having_clause = substr($this->_original_query, $having_start, ($order_start - $having_start));
            } else {
                throw new Exception("This implementation of the paginator requires a SELECT, FROM and ORDER BY clause.");
            }
        } else {
            $having_flag = false;
        }
        // extract the order clause
        $order_clause = substr($this->_original_query, $order_start);

        // REBUILD THE QUERIES
        $this->_count_query = "SELECT COUNT(*) AS total " . $from_clause;
        $this->_original_query = str_replace("SELECT", "SELECT ROW_NUMBER() OVER (" . $order_clause . ") AS 'RowNumber', ", $select_clause) . " " . $from_clause;
        if ($where_flag) {
            $this->_count_query .= " " . $where_clause;
            $this->_original_query .= " " . $where_clause;
        }
        if ($group_flag && !$having_flag) {
            $this->_count_query .= " " . $group_clause;
            $this->_original_query .= " " . $group_clause;
        } else if ($group_flag && $having_flag && $having_start < $group_start) {
            $this->_count_query .= " " . $having_clause . " " . $group_clause;
            $this->_original_query .= " " . $having_clause . " " . $group_clause;
        } else if ($group_flag && $having_flag && $having_start > $group_start) {
            $this->_count_query .= " " . $group_clause . " " . $having_clause;
            $this->_original_query .= " " . $group_clause . " " . $having_clause;
        } else if ($having_flag) {
            $this->_count_query .= " " . $having_clause;
            $this->_original_query .= " " . $having_clause;
        }

        // wrap the query in the limit, offset implementation for MsSQL
        $this->_original_query = "WITH Results_BME AS (" . $this->_original_query . ")
            SELECT * FROM Results_BME
            WHERE RowNumber BETWEEN " . ($this->_items_per_page * ($this->_current_page - 1)) . " AND " . ($this->_items_per_page * $this->_current_page);
    }

#end buildQueries function

    private function buildPagePanel() {
        $mapper_class = get_class($this->_mapper);
        unset($this->_mapper);
        $this->_mapper = new $mapper_class;

        $result = $this->_mapper->fetch($this->_count_query);
        if (is_array($result) && count($result) > 0) {
            $total = 0;
            foreach ($result as $row) {
                $total += $row['total'];
            }
            $this->_pages_panel = new Utility_PagePanel(
                    $this->_pagination_type, $total, $this->_current_page, $this->_items_per_page, 6
            );
        } else if (is_array($result) && count($result) > 0) {
            $this->_pages_panel = new Utility_PagePanel(
                    $this->_pagination_type, $result[0]['total'], $this->_current_page, $this->_items_per_page, 6
            );
        }
    }

#end buildPagePanel function

    private function buildData() {
        $mapper_class = get_class($this->_mapper);
        unset($this->_mapper);
        $this->_mapper = new $mapper_class;

        $this->_data = $this->_mapper->fetch($this->_original_query);
    }

#end buildData function

    public function getPagesPanel() {
        return $this->_pages_panel;
    }

#end getPagesPanel function

    public function getData() {
        return $this->_data;
    }

#end getData function

    public function toArray() {
        return $this->_data;
    }

#end toArray function

    public function __toString() {
        if (isset($this->_pages_panel)) {
            $tokens = explode("\\", $this->_partial_location);
            $path = "//";
            $script = "";
            for ($i = 0; $i < (count($tokens) - 1); ++$i) {
                $path .= $tokens[$i] . "//";
            }
            $script = $tokens[(count($tokens) - 1)];
            $view = new Zend_View();
            $view->setScriptPath(APPLICATION_PATH . '//views//scripts' . $path);
            $view->pageCount = $this->_pages_panel->pageCount;
            $view->pagesInRange = $this->_pages_panel->pagesInRange;
            $view->previous = $this->_pages_panel->previous;
            $view->current = $this->_pages_panel->current;
            $view->next = $this->_pages_panel->next;
            $view->firstItemNumber = $this->_pages_panel->firstItemNumber;
            $view->lastItemNumber = $this->_pages_panel->lastItemNumber;
            $view->totalItemCount = $this->_pages_panel->totalItemCount;
            $view->itemCountPerPage = $this->_pages_panel->itemCountPerPage;
            $view->currentItemCount = $this->_pages_panel->currentItemCount;
            $output = $view->render($script);

            return $output;
        } else {
            return "";
        }
    }

#end __toString function
}
