<?php
class ScriptsController extends Zend_Controller_Action {
    public function init() {
        //This Controller has all the automated scripts that run Hourly\Daily\Weekly\Monthly
        // only allow command line interaction with this controller
        if (PHP_SAPI != "cli") {
            return $this->_redirect(Zend_Registry::get("full_url")."/login");
        }

        // Extend memory Limits for longer scripts
        ini_set("memory_limit", "5000M");
        set_time_limit(4 * 60 * 60);

    }

    //Fix Item Codes For Scanned orders Using UPC code
    public function fixordercheckerAction() {
        $altmapper = new Atlas_Model_ProductExtraInfoMapper();
        $mapper = new Atlas_Model_OrderCheckerShortMapper();
        $omapper = new Atlas_Model_OrderCheckerHeaderMapper();
        $items = $mapper->selectAll()->query()->fetchAll();
        foreach ($items as $row) {
            $order_details = $omapper->buildMergedOrderData($row['so_number']);
            if (trim($row['upc']) != "") {
                $row['qty_required'] = $order_details['lines'][$row['upc']]['qty_ordered'];
                $product = $altmapper->buildByUPC($row['upc']);
                $row['item_key'] = $product['product_code'];
                $mapper->save(new Atlas_Model_OrderCheckerShort($row));
            }
        }
        die();
    }

    //Migrate test results data to multi line table
    public function extractitemlinesAction() {
        $sd_mapper = new Atlas_Model_SupplementItemDataMapper();

        $heavy_metals = array(
            array("name" => "arsenic", "label" => "Arsenic (As)"),
            array("name" => "cadmium", "label" => "Cadmium (Cd)"),
            array("name" => "lead", "label" => "Lead (Pb)"),
            array("name" => "mercury", "label" => "Mercury (Hg)")
        );

        $rancidity = array(
            array("name" => "peroxide_value", "label" => "Peroxide Value"),
            array("name" => "tba", "label" => "TBA"),
            array("name" => "saponification", "label" => "Saponification")
        );

        $micro = array(
            array("name" => "total_plate_count", "label" => "Total Plate Count"),
            array("name" => "yeast_and_molds", "label" => "Yeast & Molds"),
            array("name" => "saureus", "label" => "S. Aureus"),
            array("name" => "ecoli", "label" => "E. Coli"),
            array("name" => "salmonella", "label" => "Salmonella"),
            array("name" => "coliforms", "label" => "Coliforms"),
            array("name" => "yeast", "label" => "Yeast"),
            array("name" => "mold", "label" => "Mold")
        );

        echo "parsing finished goods\n";
        $fg_mapper = new Atlas_Model_FinishedGoodsMapper();
        $items = $fg_mapper->selectAll()->query()->fetchAll();
        $counter = 0;
        foreach ($items as $row) {
            ++$counter;
            if ($counter % 10 == 0) {
                echo ".";
            }

            echo "-------------------------------\n";
            print_r($row);
            echo "\n";

            // extract heavy metals
            foreach ($heavy_metals as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 7, // spec heavy metal
                        "record_id" => $row['fg_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                        "data_extra" => ""
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract rancidity
            foreach ($rancidity as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 8, // spec ranc
                        "record_id" => $row['fg_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract micro
            foreach ($micro as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 9, // spec micro
                        "record_id" => $row['fg_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }
        }
        echo "\n";

        echo "parsing bulk materials\n";
        $bu_mapper = new Atlas_Model_BulkMaterialsMapper();
        $items = $bu_mapper->selectAll()->query()->fetchAll();
        $counter = 0;
        foreach ($items as $row) {
            ++$counter;
            if ($counter % 10 == 0) {
                echo ".";
            }

            echo "-------------------------------\n";
            print_r($row);
            echo "\n";

            // extract heavy metals
            foreach ($heavy_metals as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 7, // spec heavy metal
                        "record_id" => $row['bulk_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                        "data_extra" => ""
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract rancidity
            foreach ($rancidity as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 8, // spec ranc
                        "record_id" => $row['bulk_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract micro
            foreach ($micro as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 9, // spec micro
                        "record_id" => $row['bulk_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }
        }
        echo "\n";

        echo "parsing blend materials\n";
        $bl_mapper = new Atlas_Model_BlendMaterialsMapper();
        $items = $bl_mapper->selectAll()->query()->fetchAll();
        $counter = 0;
        foreach ($items as $row) {
            ++$counter;
            if ($counter % 10 == 0) {
                echo ".";
            }

            echo "-------------------------------\n";
            print_r($row);
            echo "\n";

            // extract heavy metals
            foreach ($heavy_metals as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 7, // spec heavy metal
                        "record_id" => $row['blend_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                        "data_extra" => ""
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract rancidity
            foreach ($rancidity as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 8, // spec ranc
                        "record_id" => $row['blend_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract micro
            foreach ($micro as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 9, // spec micro
                        "record_id" => $row['blend_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }
        }
        echo "\n";

        echo "parsing raw materials\n";
        $rw_mapper = new Atlas_Model_RawMaterialsMapper();
        $items = $rw_mapper->selectAll()->query()->fetchAll();
        $counter = 0;
        foreach ($items as $row) {
            ++$counter;
            if ($counter % 10 == 0) {
                echo ".";
            }

            echo "-------------------------------\n";
            print_r($row);
            echo "\n";

            // extract heavy metals
            foreach ($heavy_metals as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 7, // spec heavy metal
                        "record_id" => $row['raw_material_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                        "data_extra" => ""
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract rancidity
            foreach ($rancidity as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 8, // spec ranc
                        "record_id" => $row['raw_material_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }

            // extract micro
            foreach ($micro as $field) {
                print_r($field);
                echo "\n";

                if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                    $data = array(
                        "data_name" => $field['label'],
                        "data_tolerance" => $row[$field['name']],
                        "data_test_method" => (int) $row[$field['name'] . '_method'],
                        "data_type_id" => 9, // spec micro
                        "record_id" => $row['raw_material_id'],
                        "item_key" => $row['item_key'],
                        "data_unit" => (int) $row[$field['name'] . '_unit'],
                        "data_negative" => 0,
                        "data_inequality" => 0,
                        "data_extra" => $row[$field['name'] . '_prefix']
                    );
                    print_r($data);
                    echo "\n";
                    $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                }
            }
        }
        echo "\n";

        echo "correcting test assay points\n";
        try {
            $assay_points = $sd_mapper->getDbTable()->select()
                            ->from(array("t" => "supplement_item_data"), array("t.data_id", "t.record_id"))
                            ->where("t.data_type_id = ?", 4)// Lot Test Result Assay
                            ->query()->fetchAll();
            $tr_mapper = new Atlas_Model_TestResultsMapper();
            foreach ($assay_points as $assay) {
                $entry = $sd_mapper->find($assay['data_id']);
                $test = $tr_mapper->buildMinimalByStaticId($assay['record_id']);
                if ((int) $test['test_result_id'] > 0) {
                    $entry->setRecord_id($test['test_result_id']);
                    $sd_mapper->save($entry);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            echo print_r($assay);
            die();
        }
        echo "\n";

        echo "parsing test results\n";
        $tr_mapper = new Atlas_Model_TestResultsMapper();
        $tests = $tr_mapper->selectAll()->query()->fetchAll();
        $counter = 0;
        foreach ($tests as $row) {
            ++$counter;
            if ($counter % 10 == 0) {
                echo ".";
            }

            echo "-------------------------------\n";
            print_r($row);
            echo "\n";

            if ($row['test_result_type'] == 1) { // assay
                continue;
            } else if ($row['test_result_type'] == 2) { // heavy metal
                // extract heavy metals
                foreach ($heavy_metals as $field) {
                    print_r($field);
                    echo "\n";

                    if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                        $data = array(
                            "data_name" => $field['label'],
                            "data_tolerance" => $row[$field['name']],
                            "data_test_method" => (int) $row[$field['name'] . '_method'],
                            "data_type_id" => 10, // test result heavy metal
                            "record_id" => $row['test_result_id'],
                            "item_key" => $row['item_key'],
                            "data_unit" => (int) $row[$field['name'] . '_unit'],
                            "data_negative" => 0,
                            "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                            "data_extra" => ""
                        );
                        print_r($data);
                        echo "\n";
                        $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                    }
                }
            } else if ($row['test_result_type'] == 3) { // micro
                foreach ($micro as $field) {
                    print_r($field);
                    echo "\n";

                    if ($field['name'] == "total_plate_count") {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 12, // test result micro
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row['tpc_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row['tpc_inequality'],
                                "data_extra" => ""
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    } else {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 12, // test result micro
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row[$field['name'] . '_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                                "data_extra" => ""
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    }
                }
            } else if ($row['test_result_type'] == 4) { // ranc
                foreach ($rancidity as $field) {
                    print_r($field);
                    echo "\n";

                    if ($field['name'] == "peroxide_value") {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 11, // test result ranc
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row[$field['name'] . '_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                                "data_extra" => $row["peroxide_prefix"]
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    } else {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 11, // test result ranc
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row[$field['name'] . '_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                                "data_extra" => $row[$field['name'] . '_prefix']
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    }
                }
            }
        }
        echo "\n";

        echo "parsing stability test results\n";
        $tr_mapper = new Atlas_Model_STTestResultsMapper();
        $tests = $tr_mapper->selectAll()->query()->fetchAll();
        $counter = 0;
        foreach ($tests as $row) {
            ++$counter;
            if ($counter % 10 == 0) {
                echo ".";
            }

            echo "-------------------------------\n";
            print_r($row);
            echo "\n";

            if ($row['test_result_type'] == 1) { // assay
                continue;
            } else if ($row['test_result_type'] == 2) { // heavy metal
                // extract heavy metals
                foreach ($heavy_metals as $field) {
                    print_r($field);
                    echo "\n";

                    if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                        $data = array(
                            "data_name" => $field['label'],
                            "data_tolerance" => $row[$field['name']],
                            "data_test_method" => (int) $row[$field['name'] . '_method'],
                            "data_type_id" => 13, // stability test heavy metal
                            "record_id" => $row['test_result_id'],
                            "item_key" => $row['item_key'],
                            "data_unit" => (int) $row[$field['name'] . '_unit'],
                            "data_negative" => 0,
                            "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                            "data_extra" => ""
                        );
                        print_r($data);
                        echo "\n";
                        $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                    }
                }
            } else if ($row['test_result_type'] == 3) { // micro
                foreach ($micro as $field) {
                    print_r($field);
                    echo "\n";

                    if ($field['name'] == "total_plate_count") {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 15, // stability test micro
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row['tpc_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row['tpc_inequality'],
                                "data_extra" => ""
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    } else {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 15, // stability test micro
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row[$field['name'] . '_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                                "data_extra" => ""
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    }
                }
            } else if ($row['test_result_type'] == 4) { // ranc
                foreach ($rancidity as $field) {
                    print_r($field);
                    echo "\n";

                    if ($field['name'] == "peroxide_value") {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 14, // stability test ranc
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row[$field['name'] . '_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                                "data_extra" => "peroxide_prefix",
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    } else {
                        if (isset($row[$field['name']]) && trim($row[$field['name']]) != "") {
                            $data = array(
                                "data_name" => $field['label'],
                                "data_tolerance" => $row[$field['name']],
                                "data_test_method" => (int) $row[$field['name'] . '_method'],
                                "data_type_id" => 14, // stability test ranc
                                "record_id" => $row['test_result_id'],
                                "item_key" => $row['item_key'],
                                "data_unit" => (int) $row[$field['name'] . '_unit'],
                                "data_negative" => 0,
                                "data_inequality" => (int) $row[$field['name'] . '_inequality'],
                                "data_extra" => $row[$field['name'] . '_prefix'],
                            );
                            print_r($data);
                            echo "\n";
                            $sd_mapper->save(new Atlas_Model_SupplementItemData($data));
                        }
                    }
                }
            }
        }

        try {
            // extracting stability protocols
            echo "extracting stability lot protocols\n";
            $mapper = new Atlas_Model_STLotsMapper();
            $p_mapper = new Atlas_Model_LotProtocolMapper();
            $stability_lots = $mapper->selectAll()->query()->fetchAll();
            foreach ($stability_lots as $lot) {
                echo "\nLot ID: " . $lot['lot_id'] . "\n";
                print_r($lot);
                echo "\n\n";
                if ($lot['tpc_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Total Plate Count");
                        $data['protocol'] = $lot['tpc_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Total Plate Count",
                            "protocol" => $lot['tpc_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['yeast_and_molds_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Yeast & Molds");
                        $data['protocol'] = $lot['yeast_and_molds_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Yeast & Molds",
                            "protocol" => $lot['yeast_and_molds_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['ecoli_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "E. Coli");
                        $data['protocol'] = $lot['ecoli_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "E. Coli",
                            "protocol" => $lot['ecoli_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['salmonella_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Salmonella");
                        $data['protocol'] = $lot['salmonella_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Salmonella",
                            "protocol" => $lot['salmonella_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['saureus_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "S. Aureus");
                        $data['protocol'] = $lot['saureus_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "S. Aureus",
                            "protocol" => $lot['saureus_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['coliforms_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Coliforms");
                        $data['protocol'] = $lot['coliforms_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Coliforms",
                            "protocol" => $lot['coliforms_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['peroxide_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Peroxide");
                        $data['protocol'] = $lot['peroxide_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Peroxide",
                            "protocol" => $lot['peroxide_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['arsenic_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Arsenic (As)");
                        $data['protocol'] = $lot['arsenic_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Arsenic (As)",
                            "protocol" => $lot['arsenic_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['cadmium_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Cadmium (Cd)");
                        $data['protocol'] = $lot['cadmium_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Cadmium (Cd)",
                            "protocol" => $lot['cadmium_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['lead_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Lead (Pb)");
                        $data['protocol'] = $lot['lead_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Lead (Pb)",
                            "protocol" => $lot['lead_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
                if ($lot['mercury_chk'] == 1) {
                    try {
                        $data = $p_mapper->buildProtocol($lot['lot_id'], "Mercury (Hg)");
                        $data['protocol'] = $lot['mercury_note'];
                        $data['status'] = 1;
                    } catch (Exception $e) {
                        $data = array(
                            "lot_id" => $lot['lot_id'],
                            "title" => "Mercury (Hg)",
                            "protocol" => $lot['mercury_note'],
                            "status" => 1
                        );
                    }
                    print_r($data);
                    echo "\n";
                    $p_mapper->save(new Atlas_Model_LotProtocol($data));
                }
            }
        } catch (Exception $e) {
            print_r($lot);
            print_r($data);
            print_r($e);
            die();
        }

        echo "finished\n";
        die();
    }

    //Adjust Status for new Categorizations
    public function adjustStatusReasonAction() {
        $pc_mapper = new Atlas_Model_ProductCategorizationMapper();
        $dict = $pc_mapper->buildStatusReasonDict();
        $mapper = new Atlas_Model_PCStatusReasonsMapper();
        foreach ($dict as $row) {
            if (trim($row['status_reason']) == "" || $row['status_reason'] == NULL) {
                continue;
            }
            $data = array(
                "reason" => $row['status_reason'],
                "status" => 1
            );
            $id = $mapper->save(new Atlas_Model_PCStatusReasons($data));
            $update = $pc_mapper->getDbTable();
            $update->update(
                    array("status_reason" => $id), array("status_reason = ?" => $row['status_reason'])
            );
        }

        die();
    }
    
    //Migrate Suppliers to new table structure
    public function collapseSupplierListsAction() {
        $su_mapper = new Atlas_Model_SuppliersMapper();
        $fs_mapper = new Atlas_Model_FormulaSupplierMapper();

        $suppliers = $su_mapper->selectAll()->query()->fetchAll();
        echo count($suppliers) . "\n";
        foreach ($suppliers as $su_supplier) {
            echo ".";
            try {
                $su_supplier_id = $su_supplier['supplier_id'];
                $fs_supplier = $fs_mapper->buildSupplierByString($su_supplier['supplier_code']);
                if (is_array($fs_supplier) && count($fs_supplier) > 0) {
                    $fs_supplier_id = $fs_supplier['supplier_id'];
                } else {
                    $fs_supplier_id = $fs_mapper->save(new Atlas_Model_FormulaSupplier(array(
                                "supplier_name" => $su_supplier['supplier_name'],
                                "supplier_code" => $su_supplier['supplier_code'],
                                "status" => $su_supplier['supplier_active']
                            )));
                }

                $db = Zend_Db::factory('Pdo_Mysql', array(
                            'host' => '127.0.0.1',
                            'username' => 'root',
                            'password' => 'buddhaba',
                            'dbname' => 'atlas'
                        ));
                new Zend_Db_Statement_Mysqli($db, "UPDATE lots SET supplier_id='" . $fs_supplier_id . "' WHERE supplier_id='" . $su_supplier_id . "'");
                new Zend_Db_Statement_Mysqli($db, "UPDATE skiplot_data SET supplier_id='" . $fs_supplier_id . "' WHERE supplier_id='" . $su_supplier_id . "'");
            } catch (Exception $e) {
                echo "\n";
                echo $e->getMessage() . "\n";
                echo "formula supplier id: " . $fs_supplier_id . "\n";
                echo "qc supplier id: " . $su_supplier_id . "\n";
                die();
            }
        }
        echo "\n";

        die();
    }

    //Validate Suppliers for each ingredient
    public function parseIngredientSupplierStreamAction() {
        $request = $this->getRequest();
        $file_location = $request->getParam("url", "");

        if ($file_location == "" || !file_exists("/root/" . $file_location)) {
            echo "No file provided...\n";
            die();
        }

        $cat_mapper = new Atlas_Model_CategorizationsMapper();
        $pc_mapper = new Atlas_Model_ProductCategorizationMapper();
        $pcl_mapper = new Atlas_Model_ProductCategorizationLinkMapper();
        $ing_mapper = new Atlas_Model_FormulaIngredientMapper();
        $sup_mapper = new Atlas_Model_FormulaSupplierMapper();

        $file = fopen("/root/" . $file_location, "r");
        echo "parsing file\n";

        $counter = 0;
        $categories = array();
        while (!feof($file)) {
            ++$counter;
            $line = fgetcsv($file);
            if (trim($line[0]) == "") {
                continue;
            }

            if ($counter <= 1) {
                for ($i = 6; $i < count($line); ++$i) {
                    if (trim($line[$i]) == "") {
                        continue;
                    }
                    try {
                        $categories[] = array(
                            "pos" => $i,
                            "name" => trim($line[$i]),
                            "id" => $cat_mapper->buildIDFromName(trim(Utility_Filter_DBSafe::clean($line[$i])))
                        );
                    } catch (Exception $e) {
                        echo "0 " . $e->getMessage() . "\n";
                    }
                }
                continue;
            }

            try {
                $ingr_code = trim($line[0]);
                $ingr_name = trim($line[1]);
                $ingredient = $ing_mapper->buildIngredientByString($ingr_code);
            } catch (Exception $e) {
                echo $ingr_code . ": " . $e->getMessage();
                $ingredient = NULL;
            }
            try {
                $supp_code = trim($line[2]);
                $supp_name = trim($line[3]);
                $supplier = $sup_mapper->buildSupplierByString($supp_code);
            } catch (Exception $e) {
                echo $supp_code . ": " . $e->getMessage();
                $supplier = NULL;
            }

            if ($ingredient === NULL) {
                $data = array(
                    "ingredient_name" => $ingr_name,
                    "ingredient_code" => $ingr_code,
                    "ingredient_type" => "RAW",
                    "status" => 1
                );
                $ingredient_id = $ing_mapper->save(new Atlas_Model_FormulaIngredient($data));
            } else {
                $ingredient_id = $ingredient['ingredient_id'];
            }
            if ($supplier === NULL) {
                $data = array(
                    "supplier_name" => $supp_name,
                    "supplier_code" => $supp_code,
                    "status" => 1
                );
                $supplier_id = $sup_mapper->save(new Atlas_Model_FormulaSupplier($data));
            } else {
                $supplier_id = $supplier['supplier_id'];
            }

            $status_flag = (trim($line[4]) == "Active") ? 1 : 0;
            $status_reason = trim($line[5]);

            try {
                try {
                    $coupling = $pc_mapper->find($pc_mapper->buildId($ingredient_id, $supplier_id));
                    $coupling->setStatus_flag($status_flag)
                            ->setStatus_reason($status_reason);
                    try {
                        $coupling_id = $pc_mapper->save($coupling);
                    } catch (Exception $e) {
                        echo "1 " . $e->getMessage();
                        die();
                    }
                } catch (Exception $e) {
                    $data = array(
                        "ingredient_id" => $ingredient_id,
                        "item_key" => $ingredient['ingredient_code'],
                        "item_name" => $ingredient['ingredient_name'],
                        "supplier_id" => $supplier['supplier_id'],
                        "supplier_name" => $supplier['supplier_name'],
                        "status" => "UNINITIALIZED",
                        "user_id" => 41,
                        "posted_date" => date("Y-m-d H:i:s", time()),
                        "status_flag" => $status_flag,
                        "status_reason" => $status_reason
                    );
                    try {
                        $coupling_id = $pc_mapper->save(new Atlas_Model_ProductCategorization($data));
                    } catch (Exception $e) {
                        echo "2 " . $e->getMessage();
                        die();
                    }
                }

                foreach ($categories as $row) {
                    if (strtolower(trim($line[$row['pos']])) == "x" && !$pcl_mapper->testConnection($coupling_id, $row['id'])) {
                        $data = array(
                            "pc_id" => $coupling_id,
                            "c_id" => $row['id'],
                            "user_id" => 41,
                            "posted_date" => date("Y-m-d H:i:s", time()),
                            "notes" => "auto insertion"
                        );
                        $pcl_mapper->save(new Atlas_Model_ProductCategorizationLink($data));
                    }
                }
            } catch (Exception $e) {
                echo "3 " . $e->getMessage() . "\n";
                die();
            }
        }

        fclose($file);
        echo "finished\n";
        die();
    }

    //Send Blast Email to announce changes
    public function sendBlastAction() {
        $emails = "";
        $email_file = fopen("/root/email_blast.csv", "r");
        while (!feof($email_file)) {
            $line = fgets($email_file);
            $tokens = explode(",", $line);
            if (!isset($tokens[1]) || trim($tokens[1]) == "") {
                continue;
            }
            $extra = explode(";", $tokens[1]);
            if (count($extra) > 1) {
                foreach ($extra as $idiot) {
                    $emails .= trim($idiot) . ";";
                }
            } else {
                $emails .= trim($tokens[1]) . ";";
            }
        }
        fclose($email_file);
        $emails = substr($emails, 0, -1);

        $data = array();
        $data['email'] = $emails;
        $data['subject'] = "Upcoming changes to your Jarrow Formulas invoice";
        $data['content'] = file_get_contents("/root/content.txt");

        $mapper = new Atlas_Model_EmailBlastMapper();
        $mapper->processForm($data);

        die();
    }

    //Update ARMS status after checking alerts
    public function fixPendingReviewAction() {
        $arms_mapper = new Atlas_Model_ArmsTaskMapper();
        $alert_mapper = new Atlas_Model_AlertQueueMapper();

        $in_progress = $arms_mapper->buildTasksByStatus("IN PROGRESS");
        $counter = 0;
        echo "total in progress: " . count($in_progress) . "\n";
        if (is_array($in_progress) && count($in_progress) > 0) {
            foreach ($in_progress as $document) {
                if ($alert_mapper->checkAlert("ARMS", "RnD Request", $document['task_id'])) {
                    $entry = $arms_mapper->find($document['task_id']);
                    $entry->setStatus("PENDING APPROVAL");
                    $arms_mapper->save($entry);
                    ++$counter;
                }
            }
        }
        echo "total updated: " . $counter . "\n";

        die();
    }

    public function removeDuplicateFormulaSuppliersAction() {
        /*  FIRST MANUALLY CHECK PRODUCT CATEGORIZATIONS AND ARMs TASKS
         *  Product Categorization (pc_id: ing/sup) -> Link (pcl_id: cat) -> Link Files (pclf_id: file)
         *  ARMs (supplier_id/ingredient_id)
         */

        /* THIS WAS CREATED BEFORE THE QC SUPPLIER LIST WAS REMOVED. THE LOTS AND SKIPLOT_DATA TABLES NEED
         * TO BE TAKEN INTO ACCOUNT IN A REFACTORING OF THE CODE BELOW */

        $fs_mapper = new Atlas_Model_FormulaSupplierMapper();
        $fl_mapper = new Atlas_Model_FormulaLineMapper();
        $nd_mapper = new Atlas_Model_NutritionalDataMapper();
        $at_mapper = new Atlas_Model_ArmsTaskMapper();
        $pc_mapper = new Atlas_Model_ProductCategorizationMapper();

        $duplicates = $fs_mapper->getDuplicateSuppliers()->query()->fetchAll();
        echo count($duplicates) . " supplier(s) found\n";
        $total = 0;
        $prev = "START_VAL";
        $cur_id = 0;
        foreach ($duplicates as $supplier) {
            if ($prev != $supplier['supplier_code']) {
                $prev = $supplier['supplier_code'];
                $cur_id = $supplier['supplier_id'];
                continue;
            } else if ($cur_id == $supplier['supplier_id']) {
                continue;
            }

            // fix formula lines
            $fl_mapper->switchSupplier($supplier['supplier_id'], $cur_id);
            // fix nutritional lines
            $nd_mapper->switchSupplier($supplier['supplier_id'], $cur_id);
            // fix arms documents
            $at_mapper->switchSupplier($supplier['supplier_id'], $cur_id);
            // fix product categorizations
            $pc_mapper->switchSupplier($supplier['supplier_id'], $cur_id);

            $fs_mapper->remove($supplier['supplier_id']);

            ++$total;
        }
        echo $total . " supplier(s) dropped\n";

        die();
    }

    public function removeDuplicateFormulaIngredientsAction() {
        /*
         *  FIRST MANUALLY CHECK PRODUCT CATEGORIZATIONS AND ARMs TASKS
         *  Product Categorization (pc_id: ing/sup) -> Link (pcl_id: cat) -> Link Files (pclf_id: file)
         *  ARMs (supplier_id/ingredient_id)
         */

        $fi_mapper = new Atlas_Model_FormulaIngredientMapper();
        $fl_mapper = new Atlas_Model_FormulaLineMapper();
        $nd_mapper = new Atlas_Model_NutritionalDataMapper();
        $at_mapper = new Atlas_Model_ArmsTaskMapper();
        $pc_mapper = new Atlas_Model_ProductCategorizationMapper();

        $duplicates = $fi_mapper->getDuplicateIngredients()->query()->fetchAll();
        echo count($duplicates) . " ingredient(s) found\n";
        $total = 0;
        $prev = "START_VAL";
        $cur_id = 0;
        foreach ($duplicates as $ingredient) {
            if ($prev != $ingredient['ingredient_code']) {
                $prev = $ingredient['ingredient_code'];
                $cur_id = $ingredient['ingredient_id'];
                continue;
            } else if ($cur_id == $ingredient['ingredient_id']) {
                continue;
            }

            // fix formula lines
            $fl_mapper->switchIngredient($ingredient['ingredient_id'], $cur_id);
            // fix nutritional lines
            $nd_mapper->switchIngredient($ingredient['ingredient_id'], $cur_id);
            // fix arms documents
            $at_mapper->switchIngredient($ingredient['ingredient_id'], $cur_id);
            // fix product categorizations
            $pc_mapper->switchIngredient($ingredient['ingredient_id'], $cur_id);

            $fi_mapper->remove($ingredient['ingredient_id']);

            ++$total;
        }
        echo $total . " ingredient(s) dropped\n";

        die();
    }
    
    //Sending Labeling alerts for requests in Queue
    public function labelingRequestReminderAction() {
        $priority = array("NULL", "Low", "Medium", "High", "URGENT");
        $stage = array("Graphics", "Complete", "RnD", "Production", "Voided", "Compliance");
        $admin = Zend_Registry::get("admin");
        $lr_mapper = new Atlas_Model_LabelingRequestMapper();
        $graphics = $lr_mapper->selectAll("", "", "0", 0)->query()->fetchAll();
        echo count($graphics) . " Graphics request(s) found\n";
        if (is_array($graphics) && count($graphics) > 0) {
            // build mail subject and body
            $subject = "Labeling Alert: Graphics Daily Reminder";
            $mail_body = "Greetings,<br />This is an automated reminder, meant to inform you of pending Label Requests.<br /><br />";
            $mail_body .= "The following requests are pending:<br />";
            $mail_body .= "<table>";
            $mail_body .= "<tr><td colspan='7' style='background-color:#900;color:#FFF;'>Labeling Manager</td></tr>";
            $mail_body .= "<tr>
                <td width='100' style='background-color:#CCC;color:#000;'>FG Code</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Stage</td>
                <td width='150' style='background-color:#CCC;color:#000;'>Priority</td>
                <td width='100' style='background-color:#CCC;color:#000;'>Type</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Target Date</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Qty Needed</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Creation Date</td>
            </tr>";

            foreach ($graphics as $request) {
                $mail_body .= "<tr>
                    <td>" . $request['fg_code'] . "</td>
                    <td>" . $stage[$request['completed']] . "</td>
                    <td>" . $priority[$request['priority']] . "</td>
                    <td>" . $request['type'] . "</td>
                    <td>" . $request['target_date'] . "</td>
                    <td>" . $request['qty_required'] . "</td>
                    <td>" . $request['date_created'] . "</td>
                </tr>";
            }

            $mail_body .= "</table><br /><br />";

            // build recipient list
            $mapper = new Atlas_Model_PermissionGroupsMapper();
            $task_users = $mapper->buildUsersInGroups("84");
            $recipients = $admin['email'];
            if (count($task_users) > 0) {
                foreach ($task_users as $user) {
                    $recipients .= $user['email'] . ";";
                }
            }
            $recipients = substr($recipients, 0, -1);
            echo "recipients " . $recipients . "\n";

            // push alert to queue
            $alert_mapper = new Atlas_Model_AlertQueueMapper();
            $alert_mapper->push(
                    "Scripts", "Reminder", 0, array(
                "url" => "/labeling/requests/stage/0",
                "recipients" => $recipients,
                "subject" => $subject,
                "message" => $mail_body
                    ), NULL, "Utility_Emails_AlertQueue"
            );
        }

        $rnd = $lr_mapper->selectAll("", "", "2", 0)->query()->fetchAll();
        echo count($rnd) . " RnD request(s) found\n";
        if (is_array($rnd) && count($rnd) > 0) {
            // build mail subject and body
            $subject = "Labeling Alert: RnD Daily Reminder";
            $mail_body = "Greetings,<br />This is an automated reminder, meant to inform you of pending Label Requests.<br /><br />";
            $mail_body .= "The following requests are pending:<br />";
            $mail_body .= "<table>";
            $mail_body .= "<tr><td colspan='7' style='background-color:#900;color:#FFF;'>Labeling Manager</td></tr>";
            $mail_body .= "<tr>
                <td width='100' style='background-color:#CCC;color:#000;'>FG Code</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Stage</td>
                <td width='150' style='background-color:#CCC;color:#000;'>Priority</td>
                <td width='100' style='background-color:#CCC;color:#000;'>Type</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Target Date</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Qty Needed</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Creation Date</td>
            </tr>";

            foreach ($rnd as $request) {
                $mail_body .= "<tr>
                    <td>" . $request['fg_code'] . "</td>
                    <td>" . $stage[$request['completed']] . "</td>
                    <td>" . $priority[$request['priority']] . "</td>
                    <td>" . $request['type'] . "</td>
                    <td>" . $request['target_date'] . "</td>
                    <td>" . $request['qty_required'] . "</td>
                    <td>" . $request['date_created'] . "</td>
                </tr>";
            }

            $mail_body .= "</table><br /><br />";

            // build recipient list
            $mapper = new Atlas_Model_PermissionGroupsMapper();
            $task_users = $mapper->buildUsersInGroups("85");
            $recipients = $admin['email'];
            if (count($task_users) > 0) {
                foreach ($task_users as $user) {
                    $recipients .= $user['email'] . ";";
                }
            }
            $recipients = substr($recipients, 0, -1);
            echo "recipients " . $recipients . "\n";

            // push alert to queue
            $alert_mapper = new Atlas_Model_AlertQueueMapper();
            $alert_mapper->push(
                    "Scripts", "Reminder", 2, array(
                "url" => "/labeling/requests/stage/2",
                "recipients" => $recipients,
                "subject" => $subject,
                "message" => $mail_body
                    ), NULL, "Utility_Emails_AlertQueue"
            );
        }

        $production = $lr_mapper->selectAll("", "", "3", 0)->query()->fetchAll();
        echo count($production) . " Production request(s) found\n";
        if (is_array($production) && count($production) > 0) {
            // build mail subject and body
            $subject = "Labeling Alert: Production Daily Reminder";
            $mail_body = "Greetings,<br />This is an automated reminder, meant to inform you of pending Label Requests.<br /><br />";
            $mail_body .= "The following requests are pending:<br />";
            $mail_body .= "<table>";
            $mail_body .= "<tr><td colspan='7' style='background-color:#900;color:#FFF;'>Labeling Manager</td></tr>";
            $mail_body .= "<tr>
                <td width='100' style='background-color:#CCC;color:#000;'>FG Code</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Stage</td>
                <td width='150' style='background-color:#CCC;color:#000;'>Priority</td>
                <td width='100' style='background-color:#CCC;color:#000;'>Type</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Target Date</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Qty Needed</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Creation Date</td>
            </tr>";

            foreach ($production as $request) {
                $mail_body .= "<tr>
                    <td>" . $request['fg_code'] . "</td>
                    <td>" . $stage[$request['completed']] . "</td>
                    <td>" . $priority[$request['priority']] . "</td>
                    <td>" . $request['type'] . "</td>
                    <td>" . $request['target_date'] . "</td>
                    <td>" . $request['qty_required'] . "</td>
                    <td>" . $request['date_created'] . "</td>
                </tr>";
            }

            $mail_body .= "</table><br /><br />";

            // build recipient list
            $mapper = new Atlas_Model_PermissionGroupsMapper();
            $task_users = $mapper->buildUsersInGroups("86");
            $recipients = $admin['email'];
            if (count($task_users) > 0) {
                foreach ($task_users as $user) {
                    $recipients .= $user['email'] . ";";
                }
            }
            $recipients = substr($recipients, 0, -1);
            echo "recipients " . $recipients . "\n";

            // push alert to queue
            $alert_mapper = new Atlas_Model_AlertQueueMapper();
            $alert_mapper->push(
                    "Scripts", "Reminder", 3, array(
                "url" => "/labeling/requests/stage/3",
                "recipients" => $recipients,
                "subject" => $subject,
                "message" => $mail_body
                    ), NULL, "Utility_Emails_AlertQueue"
            );
        }

        $compliance = $lr_mapper->selectAll("", "", "5", 0)->query()->fetchAll();
        echo count($compliance) . " Compliance request(s) found\n";
        if (is_array($compliance) && count($compliance) > 0) {
            // build mail subject and body
            $subject = "Labeling Alert: Compliance Daily Reminder";
            $mail_body = "Greetings,<br />This is an automated reminder, meant to inform you of pending Label Requests.<br /><br />";
            $mail_body .= "The following requests are pending:<br />";
            $mail_body .= "<table>";
            $mail_body .= "<tr><td colspan='7' style='background-color:#900;color:#FFF;'>Labeling Manager</td></tr>";
            $mail_body .= "<tr>
                <td width='100' style='background-color:#CCC;color:#000;'>FG Code</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Stage</td>
                <td width='150' style='background-color:#CCC;color:#000;'>Priority</td>
                <td width='100' style='background-color:#CCC;color:#000;'>Type</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Target Date</td>
                <td width='75' style='background-color:#CCC;color:#000;'>Qty Needed</td>
                <td width='125' style='background-color:#CCC;color:#000;'>Creation Date</td>
            </tr>";

            foreach ($compliance as $request) {
                $mail_body .= "<tr>
                    <td>" . $request['fg_code'] . "</td>
                    <td>" . $stage[$request['completed']] . "</td>
                    <td>" . $priority[$request['priority']] . "</td>
                    <td>" . $request['type'] . "</td>
                    <td>" . $request['target_date'] . "</td>
                    <td>" . $request['qty_required'] . "</td>
                    <td>" . $request['date_created'] . "</td>
                </tr>";
            }

            $mail_body .= "</table><br /><br />";

            // build recipient list
            $mapper = new Atlas_Model_PermissionGroupsMapper();
            $task_users = $mapper->buildUsersInGroups("87");
            $recipients = $admin['email'];
            if (count($task_users) > 0) {
                foreach ($task_users as $user) {
                    $recipients .= $user['email'] . ";";
                }
            }
            $recipients = substr($recipients, 0, -1);
            echo "recipients " . $recipients . "\n";

            // push alert to queue
            $alert_mapper = new Atlas_Model_AlertQueueMapper();
            $alert_mapper->push(
                    "Scripts", "Reminder", 5, array(
                "url" => "/labeling/requests/stage/5",
                "recipients" => $recipients,
                "subject" => $subject,
                "message" => $mail_body
                    ), NULL, "Utility_Emails_AlertQueue"
            );
        }

        die();
    }

    //Fully Qualify Suppliers of three or more successfully passed lots
    public function checkArmsSuppliersAction() {
        // build list of pre-qualified suppliers
        $pc_mapper = new Atlas_Model_ProductCategorizationMapper();
        $data = $pc_mapper->buildPrequalifiedSuppliers();

        // iterate over the suppliers and check how many lots have come in
        // and how many tests have been passed, in order to determine status
        $lot_mapper = new Atlas_Model_LotsMapper();
        $arms_mapper = new Atlas_Model_ArmsTaskMapper();
        $log_mapper = new Atlas_Model_ArmsTaskLogMapper();

        foreach ($data as $row) {
            echo $row['supplier_code'] . "\n";
            try {
                $result = $lot_mapper->buildSupplierLotTests($row['ingredient_code'], $row['supplier_code']);
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
                continue;
            }

            if (is_array($result) && count($result) > 0) {
                $total_passed = 0;
                $message = "";
                foreach ($result as $lot) {
                    if ($lot['task']['status_id'] == 6) {
                        ++$total_passed;
                        $message .= $lot['lot']['item_key'] . ": " . $lot['lot']['lot_no'] . ", received on " . $lot['lot']['date_received'] . " and set as " . $lot['task']['status'] . ".<br />";
                    } else if ($lot['task']['status_id'] == 10 || $lot['task']['status_id'] == 11) {
                        $total_passed = 0;
                        $message = "";
                    }

                    if ($total_passed >= 3) {
                        break;
                    }
                }

                echo $lot['lot']['item_key'] . " " . $total_passed . "\n";

                // if three tests were passed w/o a fail in between, then it is fully qualified
                if ($total_passed == 3) {
                    try {
                        $arms_data = $arms_mapper->buildTaskDetail($row['ingredient_code'], $row['supplier_code']);
                    } catch (Exception $e) {
                        continue;
                    }

                    // set the supplier/ingredient combo status to Fully Qualified
                    $pc_entry = $pc_mapper->find($row['pc_id']);
                    $pc_entry->setStatus("FULLY QUALIFIED");
                    $pc_mapper->save($pc_entry);

                    // update log entries
                    $log_mapper->save(new Atlas_Model_ArmsTaskLog(array(
                                "task_id" => $arms_data['task_id'],
                                "user_id" => 41,
                                "message" => "The vendor: " . $row['supplier_code'] . " for item: " . $row['ingredient_code'] . " was updated to FULLY QUALIFIED.<br />Notes:<br />" . $message,
                                "date_created" => date("Y-m-d H:i:s", time())
                            )));

                    // push alert to queue
                    $mapper = new Atlas_Model_AlertQueueMapper();
                    $mapper->push(
                            "ARMS", "Vendor Status Updated", $arms_data['task_id'], array(
                        "subject" => "ARMS Alert; A vendor had it's status updated to FULLY QUALIFIED.",
                        "message" => "This alert was sent to notify you that the vendor: " . $row['supplier_code'] . " for item: " . $row['ingredient_code'] . " was updated to FULLY QUALIFIED on " . date("m/d/Y h:i A", time()) . " by Automated Atlas Script<br />Notes:<br />" . $message,
                            ), $arms_data['settings_id'], "Utility_Emails_AlertQueue"
                    );
                } else {
                    continue;
                }
            }
        }

        die();
    }

    //Daily Sales Report
    public function jaimeReportAction() {
        $request = $this->getRequest();
        $date = $request->getParam('date', "");
        if (trim($date) == "") {
            $date = date("Y-m-d", time());
        }
        $mapper     =   new Atlas_Model_Inform3sales();
        $day        =   $mapper->buildDayEnd($date);
        $week       =   $mapper->buildWeekToDate($date);
        $month      =   $mapper->buildMonthToDate($date);
        $year       =   $mapper->buildYearToDate($date);
        $day        =   "$".(number_format($day['current_day'], 2, '.', ','));
        $week       =   "$".(number_format($week['total'], 2, '.', ','));
        $month      =   "$".(number_format($month['total'], 2, '.', ','));
        $year       =   "$".(number_format($year['total'], 2, '.', ','));
        $calendar   =   new Atlas_Model_DayEndCalendarMapper(strtotime($date));
        echo "Sending Daily Sales...\n";
        $email = new Utility_Emails_JaimeReport($day, $week, $month, $year, $calendar,$date);
        $email->send();

        //Fix Order Checking Dates 
        $header_mapper = new Atlas_Model_OrderCheckerHeaderMapper();
        $results = $header_mapper->fixMissedOrders();
        if ($results > 0) {
            $email_checker = new Utility_Emails_OrderCheckerMaintenance($results);
            $email_checker->send();
        }
        die();
    }

    //Add tracking number to Calltag Items
    public function appendTrackingToCalltagsAction() {
        $mapper = new Atlas_Model_CalltagMapper();
        $fe_mapper = new Atlas_Model_FEWriteBackMapper();
        $calltags = $mapper->selectAll()->query()->fetchAll();

        foreach ($calltags as $row) {
            if ($row['bme_tracking'] == "" && $row['so_no'] != "" &&
                    (
                    strtoupper(substr($row['so_no'], 0, 2)) == "SO" ||
                    strtoupper(substr($row['so_no'], 0, 2)) == "00" ||
                    strtoupper(substr($row['so_no'], 0, 2)) == "S0"
                    )) {
                    try {
                        $fe_data = $fe_mapper->buildTrackingData($row['so_no']);
                        $row['bme_tracking'] = $fe_data[0]['trackingnumber'];
                        $mapper->save(new Atlas_Model_Calltag($row));
                    } catch (Exception $e) {
                        continue;
                    }
            }
        }

        die();
    }

    //Process Stickies that met target Date
    public function processLabelingStickiesAction() {
        echo "starting...\n";
        $mapper = new Atlas_Model_LabelingLabelImageStickyMapper();
        $stickies = $mapper->buildTargetStickies(date('Y-m-d'));

        if (is_array($stickies) && count($stickies) > 0) {
            $mapper = new Atlas_Model_PermissionGroupsMapper();
            $users = $mapper->buildUsersInGroups("84");
            $recipients = "";
            foreach ($users as $user) {
                $recipients = $user['email'] . ";";
            }
            if (is_array($users) && count($users) > 0) {
                $recipients = substr($recipients, 0, -1);
            }

            foreach ($stickies as $sticky) {
                // push alert to queue
                $alert_mapper = new Atlas_Model_AlertQueueMapper();
                $alert_mapper->push(
                        "Labeling", "Image Stickies", $sticky['label_id'], array(
                    "recipients" => $recipients,
                    "subject" => "Labeling Alert: Sticky target date met: " . $sticky['version'] . " " . $sticky['rev_code'],
                    "message" => $sticky['comments'] . " by " . $sticky['posted_by']
                        ), NULL, "Utility_Emails_AlertQueue"
                );
            }
        }

        echo "finished...\n";
        die();
    }

    //Process emails in the Queue
    public function processAlertQueueAction() {
        echo "starting...\n";
        $mapper = new Atlas_Model_AlertQueueMapper();
        $mapper->processQueue();
        echo "finished...\n";

        die();
    }

    //Adding Data to previously Sent Alerts
    public function emailfgreleasedcodesAction() {
        $mapper = new Atlas_Model_AlertQueueMapper();
        $alerts = $mapper->locateAlerts("QC", "FG Checklist", "Released");

        $message = "It has been brought to my attention that some of you need the Label Code on the FG Checklist Released emails. I apologize for not providing that originally, however, it will now be included in all future emails. Below you will find a list of all the FG Released emails that have been processed since the new alerting system was created.<br /><br />";

        $l_mapper = new Atlas_Model_LotsMapper();
        $c_mapper = new Atlas_Model_FinishedGoodChecklistsMapper();
        $message .= "<table><tr><td width='100'>Item Key</td><td width='100'>Lot No</td><td width='100'>Label Code</td></tr>";

        foreach ($alerts as $index => $alert) {
            $alerts[$index]['data'] = unserialize($alert['data']);

            $lot = $l_mapper->buildDetailByLotId($alert['content_id']);
            $checklists = $c_mapper->buildLatestChecklists($lot['static_lot_id']);

            $message .= "<tr><td>" . $lot['item_key'] . "</td><td>" . $lot['lot_no'] . "</td><td>" . $checklists[0]['label_code'] . "</td></tr>";
        }
        $message .= "</table><br /><br />Thanks for your patience.<br /> - raafat";

        $task_mapper = new Atlas_Model_TasksMapper();
        $task_users = $task_mapper->fetch($task_mapper->getTaskUsers($alerts[0]['data']['task_id'], 2));

        $recipients = array();
        $mapper = new Atlas_Model_AlertUserStatusMapper();
        if (is_array($task_users) && count($task_users) > 0) {
            foreach ($task_users as $user) {
                $recipients[] = array("name" => $user['name'], "email" => $user['email']);
            }
        }

        $email = new Utility_Emails_Message($recipients, "Atlas FG Released Emails", $message);
        $email->send();

        die();
    }

    //Send Alerts for Sourcing documents closer to expiration Date
    public function checkExpiredArmsFilesAction() {
        $mapper = new Atlas_Model_ProductCategorizationLinkFilesMapper();
        $one_month = $mapper->buildExpiredFiles(30);
        $one_week = $mapper->buildExpiredFiles(7);
        $expired = $mapper->buildExpiredFiles(0);

        $emails = array();
        $emails[] = array("email" => "crystal@jarrow.com", "name" => "Crystal");

        if (is_array($one_month) && count($one_month) > 0) {
            $email = new Utility_Emails_ExpiredFiles(30, $emails, $one_month);
            $email->send();
        }
        if (is_array($one_week) && count($one_week) > 0) {
            $email = new Utility_Emails_ExpiredFiles(7, $emails, $one_week);
            $email->send();
        }
        if (is_array($expired) && count($expired) > 0) {
            $email = new Utility_Emails_ExpiredFiles(0, $emails, $expired);
            $email->send();
        }

        die();
    }

    //Update Lots to correct status
    public function cleanRetroLotsAction() {
        Zend_Registry::set("user_id", 41);
        $mapper = new Atlas_Model_LotsMapper();

        echo "pulling FG lots...\n";
        $retro_fg = $mapper->buildRetroFGLots();
        echo "updating lots...\n";
        foreach ($retro_fg as $row) {
            if ($row['alt_status_id'] == 15 || $row['alt_status_id'] == 16) {
                print_r($row);
                echo "\n";
                $mapper->updateLotStatus($row['lot_id'], 8, 1);
            }
        }

        echo "pulling bulk/blend lots...\n";
        $retro_alt = $mapper->buildRetroAltLots();
        echo "updating lots...\n";
        foreach ($retro_alt as $row) {
            print_r($row);
            echo "\n";
            $mapper->updateLotStatus($row['lot_id'], 7, 1);
        }

        echo "finished\n";

        die();
    }

    //Fix Lots with incorrect Item Type
    public function cleanlotsAction() {
        $request = $this->getRequest();
        $clean_type = (int) $request->getParam('type', 0);

        if ($clean_type == 0) {
            $lots_mapper = new Atlas_Model_LotsMapper();
            $lots_mapper->cleanDirtyLotsAlt();
        } else {
            $lots_mapper = new Atlas_Model_LotsMapper();
            $lots_mapper->cleanDirtyLots();
        }

        die();
    }

    //Build QC Stats
    public function qchistoryAction() {
        $request = $this->getRequest();
        $start_date = $request->getParam('start', '');
        $end_date = $request->getParam('end', '');
        $report_data = array(
            "from_date" => date("Y-m-d", strtotime($start_date)),
            "to_date" => date("Y-m-d", strtotime($end_date)),
            "item_key" => ""
        );
        $mapper = new Atlas_Model_LotsMapper();
        $resulting_data = $mapper->buildDataEntryStats($report_data);

        $file = fopen(Zend_Registry::get("root_path") . "/scripts/csv_reports/qchistory_" . date("Y-m-d", time()) . ".xls", "w");

        $line = "<table>\n" .
                "\t<tr>\n" .
                "\t\t<td>Static ID</td>\n" .
                "\t\t<td>Item Key</td>\n" .
                "\t\t<td>Lot Number</td>\n" .
                "\t\t<td>Status</td>\n" .
                "\t\t<td>Test No</td>\n" .
                "\t\t<td>Test Type</td>\n" .
                "\t\t<td>Test Desc.</td>\n" .
                "\t\t<td>Date Received</td>\n" .
                "\t\t<td>Date Entered</td>\n" .
                "\t\t<td>Days Til Entry</td>\n" .
                "\t\t<td>TRF Date</td>\n" .
                "\t\t<td>Days Til Request</td>\n" .
                "\t\t<td>Sample Sent</td>\n" .
                "\t\t<td>Days Til Sent</td>\n" .
                "\t\t<td>Sample Received</td>\n" .
                "\t\t<td>Days Til Receipt</td>\n" .
                "\t\t<td>Report Date</td>\n" .
                "\t\t<td>Days Til Finalized</td>\n" .
                "\t</tr>\n";
        fputs($file, $line);

        foreach ($resulting_data['data'] as $row) {
            $test_types = array("", "assay", "heavy metals", "microbiological", "rancidity");
            $line = "\t<tr>\n" .
                    "\t\t<td>" . $row['static_lot_id'] . "</td>\n" .
                    "\t\t<td>" . $row['item_key'] . "</td>\n" .
                    "\t\t<td>" . strip_tags(str_replace(",", ";", $row['lot_number'])) . "</td>\n" .
                    "\t\t<td>" . $row['status'] . "</td>\n" .
                    "\t\t<td>" . strip_tags(str_replace(",", ";", $row['test_result_no'])) . "</td>\n" .
                    "\t\t<td>" . $test_types[(int) $row['test_result_type']] . "</td>\n" .
                    "\t\t<td>" . strip_tags(str_replace(",", ";", $row['test_description'])) . "</td>\n" .
                    "\t\t<td>" . $row['date_received'] . "</td>\n" .
                    "\t\t<td>" . $row['date_entered'] . "</td>\n" .
                    "\t\t<td>" . $row['entry_diff'] . "</td>\n" .
                    "\t\t<td>" . $row['trf_date'] . "</td>\n" .
                    "\t\t<td>" . $row['request_diff'] . "</td>\n" .
                    "\t\t<td>" . $row['sample_sent'] . "</td>\n" .
                    "\t\t<td>" . $row['sent_diff'] . "</td>\n" .
                    "\t\t<td>" . $row['sample_received'] . "</td>\n" .
                    "\t\t<td>" . $row['received_diff'] . "</td>\n" .
                    "\t\t<td>" . $row['report_date'] . "</td>\n" .
                    "\t\t<td>" . $row['report_diff'] . "</td>\n" .
                    "\t</tr>\n";
            fputs($file, $line);
        }

        $line = "</table>\n";
        fputs($file, $line);

        fclose($file);

        die();
    }

    //Update Missing For Formula Lines
    public function processFormulaItem($final_results, $version) {
        try {
            $header_mapper = new Atlas_Model_FormulaHeaderMapper();
            $note_mapper = new Atlas_Model_FormulaNoteMapper();
            $line_mapper = new Atlas_Model_FormulaLineMapper();
            $ingredient_mapper = new Atlas_Model_FormulaIngredientMapper();
            $supplier_mapper = new Atlas_Model_FormulaSupplierMapper();
            $unit_mapper = new Atlas_Model_FormulaUnitMapper();

            switch ($final_results['header_info']['formula_type']) {
                case "Tablets-Caps":
                    $formula_type = "TABS-CAPS";
                    break;
                case "Powders":
                    $formula_type = "POWDERS";
                    break;
                case "Probiotics":
                    $formula_type = "PROBIOTICS";
                    break;
                default:
                    throw new Exception("Invalid Header type.");
            }

            $data = array(
                "formula_type" => utf8_encode($formula_type),
                "formula_id" => utf8_encode($final_results['header_info']['formula_id']),
                "formula_desc" => utf8_encode($final_results['header_info']['formula_desc']),
                "manufacturer" => utf8_encode($final_results['header_info']['manufacturer']),
                "bottle_size" => utf8_encode($final_results['header_info']['bottle_size']),
                "serv_per_container" => utf8_encode($final_results['header_info']['serv_per_cont']),
                "bottle_count" => utf8_encode($final_results['header_info']['bottle_count']),
                "per_serving" => utf8_encode($final_results['header_info']['per_serving']),
                "qty_required" => utf8_encode($final_results['header_info']['qty_req']),
                "desicant" => utf8_encode($final_results['header_info']['desicant']),
                "unit_type" => utf8_encode($final_results['header_info']['unit_type']),
                "version" => $version,
                "date_created" => substr($final_results['header_info']['date_created'], 0, 10),
                "date_modified" => substr($final_results['header_info']['date_modified'], 0, 10),
                "modified_by" => $final_results['header_info']['modified_by'],
                "status" => 1,
                "active" => 1
            );
            if ($header_mapper->formulaVersionExists($data['formula_id'], $version)) {
                return;
            }
            if (trim($data['date_created']) == "") {
                $data['date_created'] = date("Y-m-d", time());
            }
            if (trim($data['date_modified']) == "") {
                $data['date_modified'] = date("Y-m-d", time());
            }
            if (trim($data['modified_by']) == "") {
                $data['modified_by'] = "imported by ATLAS/raafat_mikhaeil";
            }
            $header_id = $header_mapper->save(new Atlas_Model_FormulaHeader($data));

            if (isset($final_results['header_info']['notes']) && trim($final_results['header_info']['notes']) != "") {
                if (!$note_mapper->noteExists($final_results['header_info']['formula_id'], trim($final_results['header_info']['notes']))) {
                    $note_data = array(
                        "header_id" => $header_id,
                        "date_created" => date("Y-m-d", time()),
                        "created_by" => "imported from SharePoint (pegasus)",
                        "note" => trim($final_results['header_info']['notes']),
                        "item_key" => $final_results['header_info']['formula_id']
                    );
                    $note_mapper->save(new Atlas_Model_FormulaNote($note_data));
                }
            }

            foreach ($final_results['line_info'] as $index => $element) {
                foreach ($element as $line) {
                    if (is_array($line['ingredient']) && $line['ingredient'][0]['ID'] > 0) {
                        $ingredient = $line['ingredient'][0]['ID'];
                    } else {
                        if (trim($line['ingredient'][0]['LinkTitle']) == "") {
                            $ingredient = NULL;
                            echo "[" . $header_id . "] " . $data['formula_id'] . " " . $data['version'] . "\n";
                            print_r($line);
                            continue;
                        } else {
                            try {
                                $result = $ingredient_mapper->buildIngredientByName($line['ingredient'][0]['LinkTitle']);
                                $ingredient = $result['ingredient_id'];
                            } catch (Exception $e) {
                                $ingredient_data = array(
                                    "ingredient_name" => trim(utf8_encode($line['ingredient'][0]['LinkTitle'])),
                                    "ingredient_code" => ""
                                );
                                $ingredient = $ingredient_mapper->save(new Atlas_Model_FormulaIngredient($ingredient_data));
                            }
                        }
                    }

                    if (is_array($line['supplier']) && $line['supplier'][0]['ID'] > 0) {
                        $supplier = $line['supplier'][0]['ID'];
                    } else {
                        if (trim($line['supplier'][0]['LinkTitle']) == "") {
                            $supplier = 325;
                        } else {
                            try {
                                $result = $supplier_mapper->buildSupplierByName($line['supplier'][0]['LinkTitle']);
                                $supplier = $result['supplier_id'];
                            } catch (Exception $e) {
                                $supplier_data = array(
                                    "supplier_name" => trim($line['supplier'][0]['LinkTitle']),
                                    "supplier_code" => ""
                                );
                                $supplier = $supplier_mapper->save(new Atlas_Model_FormulaSupplier($supplier_data));
                            }
                        }
                    }

                    if (is_array($line['uom']) && $line['uom'][0]['ID'] > 0) {
                        $uom = $line['uom'][0]['ID'];
                    } else {
                        if (trim($line['unit'][0]['LinkTitle']) == "") {
                            $uom = 4;
                        } else {
                            try {
                                $result = $unit_mapper->buildUnitByName($line['unit'][0]['LinkTitle']);
                                $uom = $result['unit_id'];
                            } catch (Exception $e) {
                                $data = array(
                                    "unit_name" => $line['unit'][0]['LinkTitle']
                                );
                                $uom = $unit_mapper->save(new Atlas_Model_FormulaUnit($data));
                            }
                        }
                    }
                    if ((int) $uom <= 0) {
                        $uom = 4;
                    }

                    if (isset($line['activeinactive']) && trim($line['activeinactive']) != "") {
                        $active = ($line['activeinactive'] == "I") ? 0 : 1;
                    } else {
                        $active = 1;
                    }

                    if ($index == "CAPS") {
                        $line_data = array(
                            'header_id' => $header_id,
                            'ingredient' => $ingredient,
                            'supplier' => $supplier,
                            'line_type' => "TABS-CAPS",
                            'uom' => $uom,
                            'perc_overage' => $line['percentovrg'],
                            'kg_per_m' => $line['kgperm'],
                            'price' => $line['price'],
                            'pb_pmm' => $line['pbpmm'],
                            'lead_mcg' => $line['lead'],
                            'daily_dosage' => $line['dailydosages'],
                            'label_claim' => $line['labelclaim'],
                            'net_per_unit' => $line['netperunit'],
                            'perc_net' => $line['percentnet'],
                            'total_per_tab' => $line['totalpertab'],
                            'total_with_overage' => $line['totalovg'],
                            'active_inactive' => $active
                        );
                        $line_mapper->save(new Atlas_Model_FormulaLine($line_data));
                    } else if ($index == "POWDERS") {
                        $line_data = array(
                            'header_id' => $header_id,
                            'ingredient' => $ingredient,
                            'supplier' => $supplier,
                            'line_type' => "POWDERS",
                            'g_serv' => $line['gperserv'],
                            'g_btl' => $line['gperbtl'],
                            'perc_overage' => $line['percentovrg'],
                            'extended_total' => $line['extendedtotal'],
                            'kg_per_m' => $line['kgneededperm'],
                            'price' => $line['price'],
                            'pb_pmm' => $line['pbpmm'],
                            'lead_mcg' => $line['lead'],
                            'daily_dosage' => $line['dailydosage'],
                            'active_inactive' => $active
                        );
                        $line_mapper->save(new Atlas_Model_FormulaLine($line_data));
                    } else if ($index == "PROBIOTICS") {
                        $line_data = array(
                            'header_id' => $header_id,
                            'ingredient' => $ingredient,
                            'supplier' => $supplier,
                            'line_type' => "PROBIOTICS",
                            'kg_per_m' => $line['kgrequiredperm'],
                            'price' => $line['price'],
                            'pb_pmm' => $line['pbpmm'],
                            'lead_mcg' => $line['lead'],
                            'daily_dosage' => $line['dailydosage'],
                            'active_inactive' => $active,
                            'stock_conc' => $line['stockconc'],
                            'perc_in_formula' => $line['percentinformula'],
                            'label_claim_cap_millions' => $line['labelclaimmillions'],
                            'label_claim_cap_mg' => $line['labelclaimmg'],
                            'overage' => $line['overage'],
                            'with_overage_millions' => $line['overagemillions'],
                            'with_overage_mg' => $line['overagemg']
                        );
                        $line_mapper->save(new Atlas_Model_FormulaLine($line_data));
                    } else if ($index == "NON-PROBIOTICS") {
                        $line_data = array(
                            'header_id' => $header_id,
                            'ingredient' => $ingredient,
                            'supplier' => $supplier,
                            'line_type' => "NON-PROBIOTICS",
                            'kg_per_m' => $line['kgrequiredperm'],
                            'pb_pmm' => $line['pbpmm'],
                            'lead_mcg' => $line['lead'],
                            'daily_dosage' => $line['dailydosage'],
                            'active_inactive' => $active,
                            'label_claim_cap_mg' => $line['labelclaimmg'],
                            'overage' => $line['overage'],
                            'with_overage_mg' => $line['overagemg']
                        );
                        $line_mapper->save(new Atlas_Model_FormulaLine($line_data));
                    } else {
                        throw new Exception("Invalid Line type");
                    }
                }
            }
        } catch (Exception $e) {
            print_r($final_results);
            echo "\n";
            print_r($data);
            echo "\n";
            print_r($line_data);
            echo "\n";
            echo "Error: " . $e->getMessage();
            echo "\n";
        }
    }

    //Process Document Manager Queue
    public function processDMQueueAction() {
        $request = $this->getRequest();
        $date = $request->getParam("date", date("Y-m-d", time()));

        echo "starting, building data for given date: " . $date . "\n";

        $queue_mapper = new Atlas_Model_DMQueueMapper();
        $dm_mapper = new Atlas_Model_DocumentManagerMapper();

        $queue_mapper->buildDMQueue($date); // fill queue with files added since given date
        $queue_mapper->processQueue(3);     // process the queue for at most 3 hours

        echo "finished running Document Manager Queue\n";

        die();
    }

    //Build Monthly Sales and Email them to Sales Reps
    public function buildSalesReportsAction() {
        $request = $this->getRequest();
        $date = $request->getParam("date", "");

        $dir = Zend_Registry::get("root_path") . "/scripts/tex";
        $mapper = new Atlas_Model_AccountsKeyMapper();
        $users = $mapper->buildSalesMen();

        $current = date("m-Y", mktime(0, 0, 0, date("m", time()) - 1, 1, date("Y", time())));
        $tokens = explode("-", $current);
        $start_date = date("Y-m-d", mktime(0, 0, 0, $tokens[0], 1, $tokens[1]));
        $end_date = date("Y-m-d", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));

        if (trim($date) == "") {
            $mapper = new Atlas_Model_WHProductsMapper();
            $ctop_100_report = $mapper->buildCTop100Report($start_date, $end_date);
        }

        $reporting_results = array();
        foreach ($users as $user) {
            $mapper     =   new Atlas_Model_UsersMapper();
            $full_user  =   $mapper->getUserFromUsername($user['A_username'])->query()->fetchAll();
            $email      =   $full_user[0]['email'];
            $name       =   $full_user[0]['name'];
            if (trim($user['JC_username']) != "") {
                if (trim($date) == "") {
                    $mapper = new Atlas_Model_WHSalesMapper();
                    $monthly_report = $mapper->buildMonthlyReport($start_date, $end_date, $user['BM_username'], $user['m3_username']);
                    $mmonth_report = $mapper->build13MonthReport($start_date, $end_date, $user['BM_username'], $user['m3_username']);

                    $mapper = new Atlas_Model_WHProductsMapper();
                    $top_100_report = $mapper->buildTop100Report($start_date, $end_date, $user['BM_username'], $user['m3_username']);

                    $mapper = new Atlas_Model_Inform3sales();
                    $clist_report = $mapper->buildSalesmanCustomerList($user['m3_username']);

                    // create 1 MONTH PDF
                    $m1_filename = str_replace(" ", "_", $name) . "_1MonthReport_" . date("Y-m-d", time()) . ".pdf";
                    $mapper = new Atlas_Model_PDFMapper('c', 'A4', 'fullpage');
                    $mapper->addContent(
                            $this->view->partial('/partials/scripts/sales1MonthReport.phtml', array(
                                "data" => $monthly_report,
                                "name" => $name,
                                "begin" => $start_date,
                                "end" => $end_date
                            ))
                    );
                    $mapper->outputPDFtoFile(Zend_Registry::get("root_path") . "/scripts/tex/" . $m1_filename);

                    // create 13 MONTH PDF
                    $m13_filename = str_replace(" ", "_", $name) . "_13MonthReport_" . date("Y-m-d", time()) . ".pdf";
                    $mapper = new Atlas_Model_PDFMapper('c', 'A4', 'fullpage');
                    $mapper->addContent(
                            $this->view->partial('/partials/scripts/sales13MonthReport.phtml', array(
                                "data" => $mmonth_report,
                                "name" => $name,
                                "begin" => $start_date,
                                "end" => $end_date
                            ))
                    );
                    $mapper->outputPDFtoFile(Zend_Registry::get("root_path") . "/scripts/tex/" . $m13_filename);

                    // create User TOP 100
                    $t100_filename = str_replace(" ", "_", $name) . "_Top100Report_" . date("Y-m-d", time()) . ".pdf";
                    $mapper = new Atlas_Model_PDFMapper('c', 'A4', 'fullpage');
                    $mapper->addContent(
                            $this->view->partial('/partials/scripts/salesTop100Report.phtml', array(
                                "data" => $top_100_report,
                                "name" =>$name,
                                "begin" => $start_date,
                                "end" => $end_date
                            ))
                    );
                    $mapper->outputPDFtoFile(Zend_Registry::get("root_path") . "/scripts/tex/" . $t100_filename);

                    // create company TOP 100
                    $ct100_filename = str_replace(" ", "_", $name) . "_CTop100Report_" . date("Y-m-d", time()) . ".pdf";
                    $mapper = new Atlas_Model_PDFMapper('c', 'A4', 'fullpage');
                    $mapper->addContent(
                            $this->view->partial('/partials/scripts/salesTop100Report.phtml', array(
                                "data" => $ctop_100_report,
                                "name" => "Company Top 100",
                                "begin" => $start_date,
                                "end" => $end_date
                            ))
                    );
                    $mapper->outputPDFtoFile(Zend_Registry::get("root_path") . "/scripts/tex/" . $ct100_filename);

                    // create CUSTOMER LIST
                    $clist_filename = str_replace(" ", "_", $name) . "_CListReport_" . date("Y-m-d", time()) . ".pdf";
                    $mapper = new Atlas_Model_PDFMapper('c', 'A4', 'fullpage');
                    $mapper->addContent(
                            $this->view->partial('/partials/scripts/salesCustomerList.phtml', array(
                                "data" => $clist_report,
                                "name" => $name,
                                "begin" => $start_date,
                                "end" => $end_date
                            ))
                    );
                    $mapper->outputPDFtoFile(Zend_Registry::get("root_path") . "/scripts/tex/" . $clist_filename);
                } else {
                    $m1_filename = str_replace(" ", "_", $full_user[0]['name']) . "_1MonthReport_" . $date . ".pdf";
                    $m13_filename = str_replace(" ", "_", $full_user[0]['name']) . "_13MonthReport_" . $date . ".pdf";
                    $t100_filename = str_replace(" ", "_", $full_user[0]['name']) . "_Top100Report_" . $date . ".pdf";
                    $ct100_filename = str_replace(" ", "_", $full_user[0]['name']) . "_CTop100Report_" . $date . ".pdf";
                    $clist_filename = str_replace(" ", "_", $full_user[0]['name']) . "_CListReport_" . $date . ".pdf";
                }

                $reporting_results[] = array(
                    "name"      => $name,
                    "email"     => $email,
                    "jc_username" => $user['JC_username'],
                    "a_username" => $user['A_username'],
                    "m1_filename" => $m1_filename,
                    "m13_filename" => $m13_filename,
                    "t100_filename" => $t100_filename,
                    "ct100_filename" => $ct100_filename,
                    "clist_filename" => $clist_filename
                );
                
            }
        }

        foreach ($reporting_results as $reports) {
            echo "uploading reports for: " . $reports['a_username'] . "\n";

            try {
                // copy to SocalWeb server
                if (APPLICATION_ENV == "production") {
                    echo $dir . "/" . $reports['m1_filename'] . "\n";
                    $curl_connect = new Utility_CURLConnection();
                    $curl_connect->uploadFileViaRedirect(
                            $dir . "/" . $reports['m1_filename'],  Zend_Registry::get("jw") . "/curl/upload/type/sales-reports/filename/" . $reports['m1_filename'] . "/pass/" . time() . "." . md5(time() . "_09q87543_SALTY_9q8er-")
                    );
                    echo $dir . "/" . $reports['m13_filename'] . "\n";
                    $curl_connect->uploadFileViaRedirect(
                            $dir . "/" . $reports['m13_filename'], Zend_Registry::get("jw") . "/curl/upload/type/sales-reports/filename/" . $reports['m13_filename'] . "/pass/" . time() . "." . md5(time() . "_09q87543_SALTY_9q8er-")
                    );
                    echo $dir . "/" . $reports['t100_filename'] . "\n";
                    $curl_connect->uploadFileViaRedirect(
                            $dir . "/" . $reports['t100_filename'],  Zend_Registry::get("jw") . "/curl/upload/type/sales-reports/filename/" . $reports['t100_filename'] . "/pass/" . time() . "." . md5(time() . "_09q87543_SALTY_9q8er-")
                    );
                    echo $dir . "/" . $reports['ct100_filename'] . "\n";
                    $curl_connect->uploadFileViaRedirect(
                            $dir . "/" . $reports['ct100_filename'],  Zend_Registry::get("jw") . "/curl/upload/type/sales-reports/filename/" . $reports['ct100_filename'] . "/pass/" . time() . "." . md5(time() . "_09q87543_SALTY_9q8er-")
                    );
                    echo $dir . "/" . $reports['clist_filename'] . "\n";
                    $curl_connect->uploadFileViaRedirect(
                            $dir . "/" . $reports['clist_filename'], Zend_Registry::get("jw") . "/curl/upload/type/sales-reports/filename/" . $reports['clist_filename'] . "/pass/" . time() . "." . md5(time() . "_09q87543_SALTY_9q8er-")
                    );
                }
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
                die();
            }

            echo "writing data to jarrow database\n";

            // save entries in database
            $cu_mapper = new Atlas_Model_CalUsersMapper();
            $csr_mapper = new Atlas_Model_CalSalesReportsMapper();
            $entry = new Atlas_Model_CalSalesReports(array(
                        "filename" => $reports['m1_filename'],
                        "filesize" => filesize($dir . "/" . $reports['m1_filename']),
                        "rptname" => "Sales Report Monthly",
                        "rptmo" => $tokens[0],
                        "rptyr" => $tokens[1],
                        "user_id" => $cu_mapper->buildUserId($reports['jc_username']),
                        "group_id" => 0,
                        "rpttype" => 1,
                        "filedate" => date("Y-m-d H:i:s", time())
                    ));
            $csr_mapper->save($entry);

            $entry = new Atlas_Model_CalSalesReports(array(
                        "filename" => $reports['m13_filename'],
                        "filesize" => filesize($dir . "/" . $reports['m13_filename']),
                        "rptname" => "13 Month Sales Report",
                        "rptmo" => $tokens[0],
                        "rptyr" => $tokens[1],
                        "user_id" => $cu_mapper->buildUserId($reports['jc_username']),
                        "group_id" => 0,
                        "rpttype" => 1,
                        "filedate" => date("Y-m-d H:i:s", time())
                    ));
            $csr_mapper->save($entry);

            $entry = new Atlas_Model_CalSalesReports(array(
                        "filename" => $reports['t100_filename'],
                        "filesize" => filesize($dir . "/" . $reports['t100_filename']),
                        "rptname" => "Sales Report Top 100",
                        "rptmo" => $tokens[0],
                        "rptyr" => $tokens[1],
                        "user_id" => $cu_mapper->buildUserId($reports['jc_username']),
                        "group_id" => 0,
                        "rpttype" => 1,
                        "filedate" => date("Y-m-d H:i:s", time())
                    ));
            $csr_mapper->save($entry);

            $entry = new Atlas_Model_CalSalesReports(array(
                        "filename" => $reports['ct100_filename'],
                        "filesize" => filesize($dir . "/" . $reports['ct100_filename']),
                        "rptname" => "Company Top 100",
                        "rptmo" => $tokens[0],
                        "rptyr" => $tokens[1],
                        "user_id" => $cu_mapper->buildUserId($reports['jc_username']),
                        "group_id" => 0,
                        "rpttype" => 1,
                        "filedate" => date("Y-m-d H:i:s", time())
                    ));
            $csr_mapper->save($entry);

            $entry = new Atlas_Model_CalSalesReports(array(
                        "filename" => $reports['clist_filename'],
                        "filesize" => filesize($dir . "/" . $reports['clist_filename']),
                        "rptname" => "Customer List",
                        "rptmo" => $tokens[0],
                        "rptyr" => $tokens[1],
                        "user_id" => $cu_mapper->buildUserId($reports['jc_username']),
                        "group_id" => 0,
                        "rpttype" => 1,
                        "filedate" => date("Y-m-d H:i:s", time())
                    ));
            $csr_mapper->save($entry);
            
            $email  =   new Utility_Emails_MonthlySalesReports(
                                    array(
                                        'Sales Report Monthly'  =>  $reports['m1_filename'],
                                        '13 Month Sales Report' =>  $reports['m13_filename'],
                                        'Sales Report Top 100'  =>  $reports['t100_filename'],
                                        'Company Top 100'       =>  $reports['ct100_filename'],
                                        'Customer List'         =>  $reports['clist_filename']
                                        ), 
                                    $reports['name'], 
                                    $reports['email'], 
                                    date('m-d-Y',  strtotime($start_date)),
                                    date('m-d-Y',  strtotime($end_date))
                            );
            $email->send();
        }
        die();
    }

    //Upload Zip Codes for Store Locator
    public function parseZipcodesAction() {
        $in_file = Zend_Registry::get("root_path") . "/scripts/zipcodes.csv";
        $out_file = Zend_Registry::get("root_path") . "/scripts/zipcodes.sql";

        $file_1 = fopen($in_file, "r");
        $file_2 = fopen($out_file, "w");

        while (!feof($file_1)) {
            $line = fgets($file_1);
            $tokens = explode(",", trim($line));
            $zip = $tokens[0];

            while (strlen($zip) < 5) {
                $zip = '0' . $zip;
            }
            $sql = "INSERT INTO tblzipcode (zipcode, city, state, latitude, longitude) VALUES ('" . $zip . "','" . str_replace("'", "\\'", $tokens[1]) . "','" . $tokens[2] . "','" . $tokens[3] . "','" . $tokens[4] . "');\n";
            fwrite($file_2, $sql);
        }

        fclose($file_1);
        fclose($file_2);

        die();
    }

    //Add stores from a specific file
    public function parseStoresAction() {
        $request = $this->getRequest();
        $file = $request->getParam("file", "");

        $in_file = Zend_Registry::get("root_path") . "/scripts/" . $file;
        $file = fopen($in_file, "r");

        $count = 0;
        while (!feof($file)) {
            ++$count;
            $id = $count;
            $line = fgets($file);
            $tokens = explode(",", trim($line));
            while (strlen($id) < 4) {
                $id = "0" . $id;
            }

            if ($count > 1) {
                $data = array(
                    "trackid" => $tokens[0] . $id,
                    "name" => $tokens[1],
                    "address" => $tokens[2],
                    "city" => $tokens[5],
                    "state" => $tokens[4],
                    "zipcode" => $tokens[6],
                    "phone" => $tokens[7],
                    "fax" => "",
                    "status" => 1
                );

                $entry = new Atlas_Model_Retailers($data);
                $mapper = new Atlas_Model_RetailersMapper();
                $mapper->save($entry);
            }
        }

        fclose($file);

        die();
    }

    //Send Alert for Pending Lots
    public function reportLotPendingAction() {
        echo "gathering lots with 'Pending QC Checklist' status\n";
        $mapper = new Atlas_Model_TasksMapper();
        $data = $mapper->buildPendingReport();

        echo "emailing data\n";
        if (is_array($data) && count($data) > 0) {
            $email = new Utility_Emails_LotsPending($data);
            $email->send();
        }

        echo "finished\n";
        die();
    }

    //Send Alert For Missing CofA Lots
    public function missingcofaAction() {
        $ts = time();
        $start = date("Y-m-d", mktime(0, 0, 0, date("m", $ts), (date("d", $ts) - 7), date("Y", $ts)));
        $end = date("Y-m-d", $ts);

        echo "gathering data\n";
        $mapper = new Atlas_Model_LotsMapper();
        $data = $mapper->buildWeeklyMissingCofA($start, $end);
        $data = $mapper->buildLots($data);

        echo "sending email\n";
        if (is_array($data) && count($data) > 0) {
            $email = new Utility_Emails_MissingCofA($data);
            $email->send();
        }

        echo "finished\n";
        die();
    }

    //Send Alerts for Warning and Past Due Tests
    public function stabilityTestAlertsAction() {
        echo "gathering test schedule information\n";
        $schedule_mapper = new Atlas_Model_TestScheduleMapper();
        $lot_mapper = new Atlas_Model_STLotsMapper();
        $task_mapper = new Atlas_Model_STTasksMapper();

        echo "finding warning tasks\n";
        $warning_tasks = $task_mapper->buildWarningDateTasks();
        echo "finding due tasks\n";
        $due_tasks = $task_mapper->buildDueDateTasks();

        echo "building warning tasks\n";
        foreach ($warning_tasks as $task) {
            $test = $schedule_mapper->find($task['test_id'])->toArray();
            $lot = $lot_mapper->find($task['lot_id'])->toArray();
            $task['status_id'] = 2;
            $entry = $task_mapper->find($task['task_id']);
            $entry->setStatus_id(2);
            $task_mapper->save($entry);

            $warning_lots[] = array("test" => $test, "task" => $task, "lot" => $lot);
        }

        echo "building due tasks\n";
        foreach ($warning_tasks as $task) {
            $test = $schedule_mapper->find($task['test_id'])->toArray();
            $lot = $lot_mapper->find($task['lot_id'])->toArray();
            $task['status_id'] = 3;
            $entry = $task_mapper->find($task['task_id']);
            $entry->setStatus_id(3);
            $task_mapper->save($entry);

            $due_lots[] = array("test" => $test, "task" => $task, "lot" => $lot);
        }

        echo "sending email\n";
        if (count($warning_tasks) > 0) {
            $email = new Utility_Emails_StabilityTestWarningEmail($warning_lots);
            $email->send();
        }
        if (count($due_tasks) > 0) {
            $email = new Utility_Emails_StabilityTestDueEmail($due_lots);
            $email->send();
        }

        echo "finished\n";
        die();
    }

    //Sales reps Weekly reports
    public function salesmanReportAction() {
        echo "gathering salesmen current month to date\n";
        $mapper = new Atlas_Model_WHSalesMapper();
        $data = $mapper->buildCurrentSalesmenReport();

        $summary = array();
        foreach ($data as $key => $row) {
            if (trim($row['salespkey']) != "") {
                try {
                    echo "gathering previous month to date for " . $row['salespkey'] . "\n";
                    $previous = $mapper->buildPreviousSalesmenReport($row['salespkey']);
                    $company_results = $mapper->buildCompanyBreakdownReport($row['salespkey']);
                    $ak_mapper = new Atlas_Model_AccountsKeyMapper();
                    $user = $ak_mapper->buildContactByBM($row['salespkey']);

                    // build summary
                    $summary[] = array("user" => $user, "current" => $row['total'], "previous" => $previous['total']);

                    echo "sending email\n\n";
                    // send the user an email report
                    $email = new Utility_Emails_SalesmenReport($user, $row['total'], $previous['total'], $company_results);
                    $email->send();
                } catch (Exception $e) {
                    echo $e->getMessage();
                    $summary[] = array("user" => array("name" => $row['salespkey']), "current" => $row['total'], "previous" => 0);
                    echo " could not match user " . $row['salespkey'] . "\n\n";
                }
            } else {
                $summary[] = array("user" => array("name" => "unassigned agent"), "current" => $row['total'], "previous" => 0);
            }
        }

        echo "sending summary email\n";
        // send the summary email
        $email = new Utility_Emails_SalesmenSummaryReport($summary);
        $email->send();

        echo "finished\n";
        die();
    }

    //Send WH Electronic Invoices
    public function wholefoodsSalesAction() {
        $request = $this->getRequest();
        $invoice_nums = $request->getParam("invoices", "");
        $cust_nums = $request->getParam("cust", "");
        $date = $request->getParam("date", "");
        $admin = Zend_Registry::get("admin");
        $email = "";
        $XML = "";
        if (trim($date) != "") {
            $start_date = $date;
            $end_date = $date;
        } else {
            $start_date = date("Y-m-d");
            $end_date = $start_date;
        }
        $output_path = Zend_Registry::get("root_path") . "/scripts/processed/jarrow_inv_" . $start_date . "_TO_" . $end_date . "_" . date("h-i-s-A") . ".xml";

        echo "report ran from " . $start_date . " to " . $end_date . "\n";
        $email .= "Report ran from " . $start_date . " to " . $end_date . "<br />\n";

        echo "pulling data\n";
        // gather invoice header data
        $mapper = new Atlas_Model_Inform3sales();
        if (trim($invoice_nums) != "") {
            $invoices = $mapper->buildGroupInvoiceByNumbers($invoice_nums);
        } else if (trim($cust_nums) != "") {
            $invoices = $mapper->buildCustInvoice($start_date, $end_date, $cust_nums);
        } else {
            $invoices = $mapper->buildGroupInvoiceWF($start_date, $end_date);
        }
        $invoice_count = count($invoices);

        echo $invoice_count . " invoices found\n";

        $email .= $invoice_count . " row" . (($invoice_count > 0) ? "s" : "") . " returned<br />\n";

        $total = 0;
        if ($invoice_count > 0) {
            echo "building each invoice as xml item\n";
            // BUILD XML FILE INPUT
            $XML .= "<invoiceList>\n";
            foreach ($invoices as $row) {
                if (trim($row["store_num"]) == "60052" || trim($row['cust_num']) == "WHFO376") {
                    continue;
                }
                $calc_total = 0;
                ++$total;

                // gather invoice line item data
                $mapper = new Atlas_Model_Inform3sales();
                $details = $mapper->buildInvoiceDetail($row['tranno']);
                unset($mapper);
                $detail_count = count($details);

                if ($detail_count > 0) {
                    // Distinguish between SALES & CREDIT
                    // OPTIONS: SA, CR
                    $doc_type = substr($row["trantype"], 0, 1);
                    $file_type = ($doc_type == "A") ? "IN" : "CM";
                    $total_type = ($doc_type == "A") ? "" : "";
                    $invoice_num = ($doc_type == "A") ? "SALES" . $row["invoice_num"] : "CREDT" . $row["invoice_num"];

                    $XML .= "\t<invoice>\n";
                    $XML .= "\t\t<version>wfm02</version>\n";
                    $XML .= "\t\t<vendor_id>16-160-4046</vendor_id>\n";
                    $XML .= "\t\t<usage_indicator>P</usage_indicator>\n";
                    $XML .= "\t\t<file_type>" . $file_type . "</file_type>\n";
                    $XML .= "\t\t<trans_dt>" . date("m/d/Y") . "</trans_dt>\n";
                    $XML .= "\t\t<store>CTR</store>\n";
                    $XML .= "\t\t<store_num>" . trim($row["store_num"]) . "</store_num>\n";
                    $XML .= "\t\t<store_dept>GROCERY</store_dept>\n";
                    $XML .= "\t\t<buyer_address>\n";
                    $XML .= "\t\t\t<street1>" . str_replace("&", " ", $row["street1"]) . "</street1>\n";
                    $XML .= "\t\t\t<building />\n";
                    $XML .= "\t\t\t<street2 />\n";
                    $XML .= "\t\t\t<city>" . str_replace("&", " ", $row["city"]) . "</city>\n";
                    $XML .= "\t\t\t<area />\n";
                    $XML .= "\t\t\t<state>" . $row["state"] . "</state>\n";
                    $XML .= "\t\t\t<postal>" . trim($row["postal"]) . "</postal>\n";
                    $XML .= "\t\t\t<country>" . trim($row["country"]) . "</country>\n";
                    $XML .= "\t\t</buyer_address>\n";
                    $XML .= "\t\t<invoice_num>" . $invoice_num . "</invoice_num>\n";
                    $XML .= "\t\t<invoice_date>" . $row["invoice_date"] . "</invoice_date>\n";
                    $XML .= "\t\t<cust_num>" . trim($row["cust_num"]) . "</cust_num>\n";
                    $XML .= "\t\t<po_num>" . ereg_replace("[^0-9]", "", $row["po_num"]) . "</po_num>\n";
                    $XML .= "\t\t<currency>" . $row["currency"] . "</currency>\n";
                    $XML .= "\t\t<shipvia>" . substr($row["shipvia"], 0, 4) . "</shipvia>\n";
                    $XML .= "\t\t<order_date>" . $row["order_date"] . "</order_date>\n";
                    $XML .= "\t\t<order_num>" . $row["order_num"] . "</order_num>\n";
                    $XML .= "\t\t<est_delivery_date></est_delivery_date>\n";
                    $XML .= "\t\t<reference></reference>\n";
                    $XML .= "\t\t<lineCount>" . $detail_count . "</lineCount>\n";
                    $XML .= "\t\t<lineItems>\n";
                    foreach ($details as $line) {
                        $forbidden = array(174, 194, 153);
                        $descrr = $line["descrip"];
                        $newstring = '';
                        for ($y = 0; $y < strlen($descrr); $y++) {
                            if (in_array(ord($descrr[$y]), $forbidden))
                                continue;
                            else
                                $newstring.= $descrr[$y];
                        } $line["descrip"] = $newstring;
                        $calc_total += $line["net_ext_cost"];
                        $XML .= "\t\t\t<lineItem>\n";
                        $XML .= "\t\t\t\t<line_num>" . $line["line_num"] . "</line_num>\n";
                        $XML .= "\t\t\t\t<upc>" . $line["upc"] . "</upc>\n";
                        $XML .= "\t\t\t\t<vendor_item_num>" . trim($line["vendor_item_num"]) . "</vendor_item_num>\n";
                        $XML .= "\t\t\t\t<descrip>" . str_replace("&", " ", $line["descrip"]) . "</descrip>\n";
                        $XML .= "\t\t\t\t<brand>JARROW</brand>\n";
                        $XML .= "\t\t\t\t<lot_num></lot_num>\n";
                        $XML .= "\t\t\t\t<case_uom>EA</case_uom>\n"; //not verified
                        $XML .= "\t\t\t\t<item_qtyper>" . $line["item_qtyper"] . "</item_qtyper>\n";
                        $XML .= "\t\t\t\t<item_uom>" . trim($line["item_uom"]) . "</item_uom>\n";
                        $XML .= "\t\t\t\t<case_pack>1</case_pack>\n"; //Not verified
                        $XML .= "\t\t\t\t<alt_ordering_qty></alt_ordering_qty>\n";
                        $XML .= "\t\t\t\t<alt_ordering_uom></alt_ordering_uom>\n";
                        $XML .= "\t\t\t\t<free_goods_qty></free_goods_qty>\n";
                        $XML .= "\t\t\t\t<bottle_deposit_amt />\n";
                        $XML .= "\t\t\t\t<redemption_value_amt />\n";
                        $XML .= "\t\t\t\t<item_discount_amt></item_discount_amt>\n";
                        $XML .= "\t\t\t\t<item_discount_pct></item_discount_pct>\n";
                        $XML .= "\t\t\t\t<store_promo_amt></store_promo_amt>\n";
                        $XML .= "\t\t\t\t<promo_pct></promo_pct>\n";
                        $XML .= "\t\t\t\t<promo_desc></promo_desc>\n";
                        $XML .= "\t\t\t\t<item_freight_amt />\n";
                        $XML .= "\t\t\t\t<vat_amt />\n";
                        $XML .= "\t\t\t\t<vat_pct />\n";
                        $XML .= "\t\t\t\t<sales_tax_amt />\n";
                        $XML .= "\t\t\t\t<sales_tax_pct />\n";
                        $XML .= "\t\t\t\t<unit_cost>" . round($line["net_ext_cost"], 3) / $line["qty_shipped"] . "</unit_cost>\n";
                        $XML .= "\t\t\t\t<qty_shipped>" . $total_type . $line["qty_shipped"] . "</qty_shipped>\n";
                        $XML .= "\t\t\t\t<net_ext_cost>" . $total_type . round($line["net_ext_cost"], 3) . "</net_ext_cost>\n";
                        $XML .= "\t\t\t</lineItem>\n";
                    }
                    $XML .= "\t\t</lineItems>\n";
                    $XML .= "\t\t<summary>\n";
                    $XML .= "\t\t\t<invoice_amt>" . $total_type . round($calc_total, 3) . "</invoice_amt>\n";
                    $XML .= "\t\t\t<inv_freight_amt>" . $row['inv_freight_amt'] . "</inv_freight_amt>\n";
                    $XML .= "\t\t\t<inv_discount_amt></inv_discount_amt>\n";
                    $XML .= "\t\t\t<inv_discount_pct></inv_discount_pct>\n";
                    $XML .= "\t\t\t<inv_sales_tax_amt>" . $row['inv_sales_tax_amt'] . "</inv_sales_tax_amt>\n";
                    $XML .= "\t\t\t<inv_sales_tax_pct />\n";
                    $XML .= "\t\t\t<min_ordr_chrg></min_ordr_chrg>\n";
                    $XML .= "\t\t\t<late_ordr_chrg></late_ordr_chrg>\n";
                    $XML .= "\t\t\t<credit_allowance_amt />\n";
                    $XML .= "\t\t\t<credit_allowance_pct />\n";
                    $XML .= "\t\t\t<inv_restock_amt />\n";
                    $XML .= "\t\t\t<inv_restock_pct />\n";
                    $XML .= "\t\t\t<full_service_amt />\n";
                    $XML .= "\t\t\t<full_service_pct />\n";
                    $XML .= "\t\t\t<merchandising_fee_amt />\n";
                    $XML .= "\t\t\t<merchandising_fee_pct />\n";
                    $XML .= "\t\t\t<excise_tax_amt />\n";
                    $XML .= "\t\t\t<excise_tax_pct />\n";
                    $XML .= "\t\t\t<message></message>\n";
                    $XML .= "\t\t</summary>\n";
                    $XML .= "\t</invoice>\n";

                    if (number_format(($calc_total + $row["inv_sales_tax_amt"]), 2) != number_format($row["invoice_amt"], 2)) {
                        $email1 = new Utility_Emails_Message(
                                        array(
                                            array("email" => $admin['email'], "name" => $admin['name'])
                                        ),
                                        "WholeFoods e-Invoice incorrect total " . $invoice_num,
                                        $invoice_num . " calculated total: " . $calc_total . " BME invoice total: " . $row["invoice_amt"] . "<br />" .
                                        "po number: " . ereg_replace("[^0-9]", "", $row["po_num"]) . "<br />" .
                                        "cust code: " . $row['cust_num'] . "<br />" .
                                        "store num: " . $row['store_num'] . "<br />" .
                                        "invoice date: " . $row['invoice_date'] . "<br />" .
                                        "order date: " . $row['order_date'] . "<br />"
                        );
                        $email1->send();
                    }
                }
            }
            $XML .= "</invoiceList>";

            echo "writing to file\n";
            // WRITE XML TO FILE
            if ($invoice_count > 0 && $total > 0) {
                $file = fopen($output_path, 'w') or die("can't open file");
                if (fwrite($file, $XML)) {
                    $email .= "Job Completed. File " . $output_path . " was created.<br />\n";
                } else {
                    $email .= "Job Failed. File was not created due to error.<br />\n";
                }
                fclose($file);
            } else {
                $email .= "No rows where returned, no file was created.<br />\n";
            }

            echo "saving output to file\n";
            // save output to file for emailing later
            $file = fopen(Zend_Registry::get("root_path") . "/scripts/output/WHFO_invoices_output.txt", 'w') or die("can't open file");
            if (fwrite($file, $email)) {
                echo "Output written to file.\n";
            } else {
                echo "Output not written to file.\n";
            }
            fclose($file);
        } else {
            $email .= "No rows where returned, no file was created.<br />\n";
        }

        $message = new Utility_Emails_Message(
                        array(
                            array("name" => "Primary", "email" => "primary@jarrow.com")
                        ),
                        "Whole Foods Sales output", $email
        );
        $message->send();

        echo "finished\n";
        die();
    }

    //SEND WF electronic Credits
    public function wholefoodsCreditsAction() {
        $request = $this->getRequest();
        $invoices = $request->getParam("invoices", "");
        $date = $request->getParam("date", "");

        $email = "";
        $XML = "";
        if (trim($date) != "") {
            $start_date = $date;
            $end_date = $date;
        } else {
            $start_date = date("Y-m-d");
            $end_date = $start_date;
        }
        $output_path = Zend_Registry::get("root_path") . "/scripts/processed/jarrow_cm_" . $start_date . "_" . date("h-i-s-A") . ".xml";

        echo "report ran from " . $start_date . " to " . $end_date . "\n";
        $email .= "Report ran from " . $start_date . " to " . $end_date . "<br />\n";

        echo "pulling data\n";
        // gather credit exclusions
        $mapper = new Atlas_Model_Inform3sales();
        if (trim($invoices) != "") {
            echo "built by invoice\n";
            $credits = $mapper->buildGroupCreditByNumbers($invoices);
        } else {
            echo "built by date\n";
            $credits = $mapper->buildGroupCreditWF($start_date, $end_date);
        }
        $credit_count = count($credits);
        echo $credit_count . " credits found\n";
        $email .= $credit_count . " row" . (($credit_count > 0) ? "s" : "") . " returned<br />\n";

        $total = 0;
        if ($credit_count > 0) {
            echo "building each invoice into xml item\n";
            // BUILD XML FILE INPUT
            $XML .= "<invoiceList>\n";
            foreach ($credits as $row) {
                if (trim($row["store_num"]) == "60052" || trim($row['cust_num']) == "WHFO376") {
                    continue;
                }
                $calc_total = 0;
                ++$total;

                // gather invoice line item data
                $mapper = new Atlas_Model_Inform3sales();
                $details = $mapper->buildInvoiceDetail($row['tranno']);
                unset($mapper);
                $detail_count = count($details);

                if ($detail_count > 0) {
                    // Distinguish between SALES & CREDIT
                    // OPTIONS: SA, CR
                    $doc_type = substr($row["trantype"], 0, 1);
                    $file_type = ($doc_type == "A") ? "IN" : "CM";
                    $total_type = ($doc_type == "A") ? "" : "";
                    $invoice_num = ($doc_type == "A") ? "SALES" . $row["invoice_num"] : "CREDT" . $row["invoice_num"];

                    $XML .= "\t<invoice>\n";
                    $XML .= "\t\t<version>wfm02</version>\n";
                    $XML .= "\t\t<vendor_id>16-160-4046</vendor_id>\n";
                    $XML .= "\t\t<usage_indicator>P</usage_indicator>\n";
                    $XML .= "\t\t<file_type>" . $file_type . "</file_type>\n";
                    $XML .= "\t\t<trans_dt>" . date("m/d/Y") . "</trans_dt>\n";
                    $XML .= "\t\t<store>CTR</store>\n";
                    $XML .= "\t\t<store_num>" . $row["store_num"] . "</store_num>\n";
                    $XML .= "\t\t<store_dept>GROCERY</store_dept>\n";
                    $XML .= "\t\t<buyer_address>\n";
                    $XML .= "\t\t\t<street1>" . str_replace("&", " ", $row["street1"]) . "</street1>\n";
                    $XML .= "\t\t\t<building />\n";
                    $XML .= "\t\t\t<street2 />\n";
                    $XML .= "\t\t\t<city>" . str_replace("&", " ", $row["city"]) . "</city>\n";
                    $XML .= "\t\t\t<area />\n";
                    $XML .= "\t\t\t<state>" . trim($row["state"]) . "</state>\n";
                    $XML .= "\t\t\t<postal>" . trim($row["postal"]) . "</postal>\n";
                    $XML .= "\t\t\t<country>" . trim($row["country"]) . "</country>\n";
                    $XML .= "\t\t</buyer_address>\n";
                    $XML .= "\t\t<invoice_num>" . $invoice_num . "</invoice_num>\n";
                    $XML .= "\t\t<invoice_date>" . $row["invoice_date"] . "</invoice_date>\n";
                    $XML .= "\t\t<cust_num>" . trim($row["cust_num"]) . "</cust_num>\n";
                    $XML .= "\t\t<po_num>" . ereg_replace("[^0-9]", "", $row["po_num"]) . "</po_num>\n";
                    $XML .= "\t\t<currency>" . $row["currency"] . "</currency>\n";
                    $XML .= "\t\t<shipvia>" . substr($row["shipvia"], 0, 4) . "</shipvia>\n";
                    $XML .= "\t\t<order_date>" . $row["order_date"] . "</order_date>\n";
                    $XML .= "\t\t<order_num>" . $row["order_num"] . "</order_num>\n";
                    $XML .= "\t\t<est_delivery_date></est_delivery_date>\n";
                    $XML .= "\t\t<reference></reference>\n";
                    $XML .= "\t\t<lineCount>" . $detail_count . "</lineCount>\n";
                    $XML .= "\t\t<lineItems>\n";
                    foreach ($details as $line) {
                        $forbidden = array(174, 194, 153);
                        $descrr = $line["descrip"];
                        $newstring = '';
                        for ($y = 0; $y < strlen($descrr); $y++) {
                            if (in_array(ord($descrr[$y]), $forbidden))
                                continue;
                            else
                                $newstring.= $descrr[$y];
                        } $line["descrip"] = $newstring;
                        $calc_total += $line['net_ext_cost'];
                        $XML .= "\t\t\t<lineItem>\n";
                        $XML .= "\t\t\t\t<line_num>" . $line["line_num"] . "</line_num>\n";
                        $XML .= "\t\t\t\t<upc>" . $line["upc"] . "</upc>\n";
                        $XML .= "\t\t\t\t<vendor_item_num>" . trim($line["vendor_item_num"]) . "</vendor_item_num>\n";
                        $XML .= "\t\t\t\t<descrip>" . str_replace("&", " ", $line["descrip"]) . "</descrip>\n";
                        $XML .= "\t\t\t\t<brand>JARROW</brand>\n";
                        $XML .= "\t\t\t\t<lot_num></lot_num>\n";
                        $XML .= "\t\t\t\t<case_uom>EA</case_uom>\n"; //not verified
                        $XML .= "\t\t\t\t<item_qtyper>" . $line["item_qtyper"] . "</item_qtyper>\n";
                        $XML .= "\t\t\t\t<item_uom>" . trim($line["item_uom"]) . "</item_uom>\n";
                        $XML .= "\t\t\t\t<case_pack>1</case_pack>\n"; //Not verified
                        $XML .= "\t\t\t\t<alt_ordering_qty></alt_ordering_qty>\n";
                        $XML .= "\t\t\t\t<alt_ordering_uom></alt_ordering_uom>\n";
                        $XML .= "\t\t\t\t<free_goods_qty></free_goods_qty>\n";
                        $XML .= "\t\t\t\t<bottle_deposit_amt />\n";
                        $XML .= "\t\t\t\t<redemption_value_amt />\n";
                        $XML .= "\t\t\t\t<item_discount_amt></item_discount_amt>\n";
                        $XML .= "\t\t\t\t<item_discount_pct></item_discount_pct>\n";
                        $XML .= "\t\t\t\t<store_promo_amt></store_promo_amt>\n";
                        $XML .= "\t\t\t\t<promo_pct></promo_pct>\n";
                        $XML .= "\t\t\t\t<promo_desc></promo_desc>\n";
                        $XML .= "\t\t\t\t<item_freight_amt />\n";
                        $XML .= "\t\t\t\t<vat_amt />\n";
                        $XML .= "\t\t\t\t<vat_pct />\n";
                        $XML .= "\t\t\t\t<sales_tax_amt />\n";
                        $XML .= "\t\t\t\t<sales_tax_pct />\n";
                        $XML .= "\t\t\t\t<unit_cost>" . round($line["net_ext_cost"], 3) / $line["qty_shipped"] . "</unit_cost>\n";
                        $XML .= "\t\t\t\t<qty_shipped>" . $total_type . $line["qty_shipped"] . "</qty_shipped>\n";
                        $XML .= "\t\t\t\t<net_ext_cost>" . $total_type . round($line["net_ext_cost"], 3) . "</net_ext_cost>\n";
                        $XML .= "\t\t\t</lineItem>\n";
                    }
                    $XML .= "\t\t</lineItems>\n";
                    $XML .= "\t\t<summary>\n";
                    $XML .= "\t\t\t<invoice_amt>" . $total_type . round($calc_total, 3) . "</invoice_amt>\n";
                    $XML .= "\t\t\t<inv_freight_amt>" . $row['inv_freight_amt'] . "</inv_freight_amt>\n";
                    $XML .= "\t\t\t<inv_discount_amt></inv_discount_amt>\n";
                    $XML .= "\t\t\t<inv_discount_pct></inv_discount_pct>\n";
                    $XML .= "\t\t\t<inv_sales_tax_amt>" . $row['inv_sales_tax_amt'] . "</inv_sales_tax_amt>\n";
                    $XML .= "\t\t\t<inv_sales_tax_pct />\n";
                    $XML .= "\t\t\t<min_ordr_chrg></min_ordr_chrg>\n";
                    $XML .= "\t\t\t<late_ordr_chrg></late_ordr_chrg>\n";
                    $XML .= "\t\t\t<credit_allowance_amt />\n";
                    $XML .= "\t\t\t<credit_allowance_pct />\n";
                    $XML .= "\t\t\t<inv_restock_amt />\n";
                    $XML .= "\t\t\t<inv_restock_pct />\n";
                    $XML .= "\t\t\t<full_service_amt />\n";
                    $XML .= "\t\t\t<full_service_pct />\n";
                    $XML .= "\t\t\t<merchandising_fee_amt />\n";
                    $XML .= "\t\t\t<merchandising_fee_pct />\n";
                    $XML .= "\t\t\t<excise_tax_amt />\n";
                    $XML .= "\t\t\t<excise_tax_pct />\n";
                    $XML .= "\t\t\t<message></message>\n";
                    $XML .= "\t\t</summary>\n";
                    $XML .= "\t</invoice>\n";

                    if (number_format(($calc_total + $row["inv_sales_tax_amt"]), 2) != number_format($row["invoice_amt"], 2)) {
                        $email1 = new Utility_Emails_Message(
                                        array(
                                            array("email" => $admin['email'], "name" => $admin['name'])
                                        ),
                                        "WholeFoods e-Invoice (credit) incorrect total " . $invoice_num,
                                        $invoice_num . " calculated total: " . $calc_total . " BME invoice total: " . $row["invoice_amt"] . "<br />" .
                                        "po number: " . ereg_replace("[^0-9]", "", $row["po_num"]) . "<br />" .
                                        "cust code: " . $row['cust_num'] . "<br />" .
                                        "store num: " . $row['store_num'] . "<br />" .
                                        "invoice date: " . $row['invoice_date'] . "<br />" .
                                        "order date: " . $row['order_date'] . "<br />"
                        );
                        $email1->send();
                    }
                }
            }
            $XML .= "</invoiceList>";

            echo "writing to file\n";
            // WRITE XML TO FILE
            if ($credit_count > 0 && $total > 0) {
                $file = fopen($output_path, 'w') or die("can't open file");
                if (fwrite($file, $XML)) {
                    $email .= "Job Completed. File " . $output_path . " was created.<br />\n";
                } else {
                    $email .= "Job Failed. File was not created due to error.<br />\n";
                }
                fclose($file);
            } else {
                $email .= "No rows where returned, no file was created.<br />\n";
            }

            echo "saving output\n";
            // save output to file for emailing later
            $file = fopen(Zend_Registry::get("root_path") . "/scripts/output/WHFO_credits_output.txt", 'w') or die("can't open file");
            if (fwrite($file, $email)) {
                echo "Output written to file.\n";
            } else {
                echo "Output not written to file.\n";
            }
            fclose($file);
        } else {
            $email .= "No rows where returned, no file was created.<br />\n";
        }

        $message = new Utility_Emails_Message(
                        array(
                            array("name" => "Primary", "email" => "primary@jarrow.com")
                        ),
                        "Whole Foods Credits output", $email
        );
        $message->send();

        echo "finished\n";
        die();
    }

    //Send Confirmation Email for Invoices and Credits sent to WF
    public function whfoConfirmationAction() {
        // build email content
        $email = "INVOICES SAVED FILE CONTENT:<br />";
        if ($file = fopen(Zend_Registry::get("root_path") . "/scripts/output/WHFO_invoices_output.txt", 'r')) {
            while (!feof($file)) {
                $email .= fgets($file);
            }
            fclose($file);
        }
        $email .= "<hr />";
        $email .= "<br />";
        $email .= "CREDITS SAVED FILE CONTENT:<br />";
        if ($file = fopen(Zend_Registry::get("root_path") . "/scripts/output/WHFO_credits_output.txt", 'r')) {
            while (!feof($file)) {
                $email .= fgets($file);
            }
            fclose($file);
        }
        $email .= "<hr />";
        $email .= "<br />";
        $email .= "FTP SAVED FILE CONTENT:<br />";
        if ($file = fopen(Zend_Registry::get("root_path") . "/scripts/output/WHFO_ftp_output.txt", 'r')) {
            while (!feof($file)) {
                $email .= fgets($file);
            }
            fclose($file);
        }

        // send email
        $email = new Utility_Emails_WholefoodsInvoiceConfirmation($email);
        $email->send();

        die();
    }

    //Build Daily Returns and Save it to Warehouse DB
    public function warehouseCalltagPopulationAction() {
        // get function parameters
        $request = $this->getRequest();
        $begin_date = $request->getParam("begin", NULL);
        $end_date = $request->getParam("end", NULL);

        if ($begin_date == NULL || $end_date == NULL) {
            $begin_date = date("Y-m-d", time());
            $end_date = date("Y-m-d", time());
        }

        echo "building and saving the calltag data\n";
        $mapper = new Atlas_Model_Inform3sales();
        $calltags = $mapper->buildReturns($begin_date, $end_date);

        $mapper = new Atlas_Model_WHShippingCalltagsMapper();
        foreach ($calltags as $entry) {
            $mapper->save($entry);
        }
        echo "finished\n";

        die();
    }

    //Build Daily sales and save it to Warehouse DB
    public function warehousePopulationAction() {
        // get function parameters
        $request = $this->getRequest();
        $begin_date = $request->getParam("begin", NULL);
        $end_date = $request->getParam("end", NULL);
        $flag = false;

        if ($begin_date == NULL || $end_date == NULL) {
            $flag = true;
            $begin_date = date("Y-m-d", time());
            $end_date = date("Y-m-d", time());
        }

        echo "building dollar sales data\n";
        // get new invoice headers
        $mapper = new Atlas_Model_Inform3sales();
        $new_invoices = $mapper->buildNewInvoices();
        $inv_no = '';
        foreach ($new_invoices as $invoice) {
            $inv_no .= $invoice['UAIVNO'] . ',';
        }
        $invoices = rtrim($inv_no, ',');
        $dollar_sales = $mapper->buildDollarSales($invoices);
        $dollar_count = count($dollar_sales);
        unset($mapper);

        echo "saving dollar sales\n";
        // load them into the dollarsales table
        if ($dollar_count > 0) {
            $mapper = new Atlas_Model_WHSalesMapper();
            foreach ($dollar_sales as $row) {
                $entry = array(
                    'custkey' => addslashes(trim($row['custkey'])),
                    'custname' => addslashes(trim($row['custname'])),
                    'invdate' => date('Y-m-d', strtotime($row['invdate'])),
                    'tranno' => addslashes(trim($row['tranno'])),
                    'oedocid' => addslashes(trim($row['oedocid'])),
                    'salespkey' => addslashes(trim($row['salespkey'])),
                    'recuserid' => addslashes(trim($row['recuserid'])),
                    'crmemoflg' => addslashes(trim($row['crmemoflg'])),
                    'doctot' => addslashes(trim($row['doctot'])),
                    'custaddr1' => addslashes(trim($row['custaddr1'])),
                    'custcity' => addslashes(trim($row['custcity'])),
                    'custstate' => addslashes(trim($row['custstate'])),
                    'custzip' => addslashes(trim($row['custzip'])),
                    'salespersn_ky' => addslashes(trim($row['salespersn_ky']))
                );

                try {
                    $mapper->save($entry);
                } catch (Exception $e) {
                    echo $e->getMessage();
                    print_r($entry);
                    echo "\n<br />\n";
                    print_r($e);
                    echo "\n<br />\n";
                }
            }
        }
        unset($dollar_sales);

        echo "building product sales data\n";
        // get new line details
        $mapper = new Atlas_Model_Inform3sales();
        $product_sales = $mapper->buildProductSales($invoices);
        $product_count = count($product_sales);
        unset($mapper);

        echo "saving product sales\n";
        // load them into the productsales table
        if ($product_count > 0) {
            $mapper = new Atlas_Model_WHProductsMapper();
            foreach ($product_sales as $row) {
                $entry = array(
                    'custkey' => addslashes(trim($row['custkey'])),
                    'custname' => addslashes(trim($row['custname'])),
                    'invdate' => date('Y-m-d', strtotime($row['invdate'])),
                    'tranno' => addslashes(trim($row['tranno'])),
                    'oedocid' => addslashes(trim($row['oedocid'])),
                    'recuserid' => addslashes(trim($row['recuserid'])),
                    'doctot' => addslashes(trim($row['doctot'])),
                    'crmemoflg' => addslashes(trim($row['crmemoflg'])),
                    'salespkey' => addslashes(trim($row['salespkey'])),
                    'itemkey' => addslashes(trim($row['itemkey'])),
                    'shipqty' => addslashes(trim($row['shipqty'])),
                    'unitprice' => addslashes(trim($row['unitprice'])),
                    'totaldisc' => addslashes(trim($row['totaldisc'])),
                    'description' => addslashes(trim($row['description'])),
                    'inclasskey' => addslashes(trim($row['inclasskey'])),
                    'salespersn_ky' => addslashes(trim($row['salespersn_ky'])),
                    'address_1' => addslashes(trim($row['address_1'])),
                    'city' => addslashes(trim($row['city'])),
                    'state' => addslashes(trim($row['state'])),
                    'zip_code' => addslashes(trim($row['zip_code'])),
                    'user4' => addslashes(trim($row['user4']))
                );

                try {
                    $mapper->save($entry);
                } catch (Exception $e) {
                    echo $e->getMessage();
                    print_r($entry);
                    echo "\n<br />\n";
                    print_r($e);
                    echo "\n<br />\n";
                }
            }
        }
        unset($product_sales);
        //Adding new invoices
        $mapper = new Atlas_Model_Inform3sales();
        if (is_array($new_invoices) && count($new_invoices) > 0)
            $mapper->InsertNewInvoices($new_invoices);
        echo "sending email\n";
        // send email confirmation
        $email = new Utility_Emails_WarehouseConfirmation($begin_date, $end_date, $dollar_count, $product_count);
        $email->send();

        echo "finished\n";
        die();
    }

    //Import Data from ShipStation API
    public function ssimportAction() {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        $results = array();
        $mapper = new Atlas_Model_ShipstationMapper();
        $values = array(    'shipDateStart' => $start_date,
                            'shipDateEnd'   => $end_date,
                            'includeShipmentItems' => "true",
                            'page'          => 1,
                            'sortBy'        => "CreateDate",
                            'sortDir'       => "ASC",
                            'pageSize'      => 500       );
        $results[] = $mapper->PullData('shipments', $values);
        $total_pages = $results[0]['pages'];

        if ((int) $total_pages > 0) {
            for ($i = 2; $i <= $total_pages; $i++) {
                $values['page'] = $i;
                $mapper = new Atlas_Model_ShipstationMapper();
                $results[] = $mapper->PullData('shipments', $values);
            }
        }
        foreach ($results as $result) {
            foreach ($result['shipments'] as $data) {
                unset($data['dimensions']);
                unset($data['insuranceOptions']);
                if ($data['batchNumber'] != NULL && $data['batchNumber'] != "") {
                    $data['createDate'] = date('Y-m-d H:i:s', strtotime($data['createDate']));
                    $data['shipDate'] = date('Y-m-d', strtotime($data['shipDate']));
                    if ($data['voidDate'] != '')
                        $data['voidDate'] = date('Y-m-d', strtotime($data['voidDate']));
                    else
                        $data['voidDate'] = '0000-00-00';
                    $data['shipTo_name'] = $data['shipTo']['name'];
                    $data['shipTo_company'] = $data['shipTo']['company'];
                    $data['shipTo_street1'] = $data['shipTo']['street1'];
                    $data['shipTo_street2'] = $data['shipTo']['street2'];
                    $data['shipTo_street3'] = $data['shipTo']['street3'];
                    $data['shipTo_city'] = $data['shipTo']['city'];
                    $data['shipTo_state'] = $data['shipTo']['state'];
                    $data['shipTo_postalCode'] = $data['shipTo']['postalCode'];
                    $data['shipTo_country'] = $data['shipTo']['country'];
                    $data['shipTo_phone'] = $data['shipTo']['phone'];
                    $data['shipTo_residential'] = $data['shipTo']['residential'];
                    $data['shipTo_addressVerified'] = $data['shipTo']['addressVerified'];
                    $data['weight_value'] = $data['weight']['value'];
                    $data['weight_units'] = $data['weight']['units'];
                    $data['weightUnits'] = $data['weight']['WeightUnits'];
                    $data['storeId'] = $data['advancedOptions']['storeId'];

                    $headermapper = new Atlas_Model_ShipmentsHeaderMapper();
                    $header = new Atlas_Model_ShipmentsHeader($data);
                    $headermapper->save($header);

                    foreach ($data['shipmentItems'] as $shipmentItems) {
                        $shipmentItems['orderId'] = $data['orderId'];
                        $shipmentItems['itemoptions'] = $shipmentItems['options'];
                        unset($shipmentItems['options']);
                        unset($shipmentItems['weight']);
                        $itemmapper = new Atlas_Model_ShipmentItemsMapper();
                        $item = new Atlas_Model_ShipmentItems($shipmentItems);
                        $itemmapper->save($item);
                    }
                }
            }
        }
        echo 'Importing completed successfully';
        die();
    }

    //Send Daily Picking Data Report
    public function dailyPickAction() {
        $request        =   $this->getRequest();
        $date           =   $request->getParam("date", date("Y-m-d"));
        
        $activity_mapper=   new Atlas_Model_ScActivityMapper();
        $missing_logouts=   $activity_mapper->BuildMissingLogouts($date);

        if(is_array($missing_logouts) && count($missing_logouts)>0){
            $sc_cart_mapper =   new Atlas_Model_ScOrdersCartMapper();
            $user_last_scan =   $sc_cart_mapper->buildLastScan($date);
            foreach($missing_logouts as $record){
                if($user_last_scan[$record['user_id']] != '' || $user_last_scan[$record['user_id']] == '0000-00-00 00:00:00'){
                    $logout = New Atlas_Model_ScActivity($record);
                    $logout->setLogout_date($user_last_scan[$record['user_id']]);
                    $activity_mapper->save($logout);
                }
            }
        }
        
        $pick_mapper    =   new Atlas_Model_ScOrdersPickMapper();
        $records        =   $pick_mapper->BuildDailyReport($date);
        
        $shpmnts_mapper =   new Atlas_Model_ShipmentItemsMapper();
        $shipments      =   $shpmnts_mapper->buildTotals($date);
        
        if(is_array($records) && count($records)>0){
            $mail_data1     =   new Utility_Emails_PickingReport($records, $shipments, $date);
            $mail_data1->send();
        }
        die();
    }

    //Send Daily error Reports to CSA Manager
    public function csaadminreportsAction() {

        $mapper = new Atlas_Model_Inform3jiipicklist();
        $form_data = array();
        $files = array();
        $date = date('m_d_Y_H_i_s');
        $form_data['start_date'] = date('Ymd');
        $form_data['end_date'] = date('Ymd');

        /* Category Mismatch report file generating */
        $data = $mapper->categoryMismatch();
        if (count($data) > 0 && is_array($data)) {
            $filename = 'CategoryMismatch_' . $date . '.csv';

            $fp = fopen(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename, 'w');
            fputcsv($fp, array('Category Mismatch', date('m/d/Y')));
            fputcsv($fp, array('CO_Number', 'Delivery_Number', 'CO_Delivery_Method', 'Delivery_Method', 'CO_Delivery_Term', 'Delivery_Term', 'CSA', 'Order_Date'));
            foreach ($data as $csa) {
                fputcsv($fp, array($csa['CO_Number'], $csa['Delivery_Number'], $csa['CO_Delivery_Method'], $csa['Delivery_Method'], $csa['CO_Delivery_Term'], $csa['Delivery_Term'], $csa['CSA'], $csa['Order_Date']));
            }
            fclose($fp);
            unset($data);
            $files[] = $filename;
        }
        /* -------------------------------------------------------------------------------------------------------------- */


        /* Order Status Report file generating */
        $data = $mapper->buildOrderStatus($form_data);
        if (count($data) > 0 && is_array($data)) {
            $filename = 'OrderStatus_' . $date . '.csv';
            $fp = fopen(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename, 'w');
            fputcsv($fp, array('Order Status Report', date('m/d/Y')));
            fputcsv($fp, array('Customer No', 'Customer Name', 'CO', 'PO', 'OrderDate', 'RESP', 'ShipVia', 'Category', 'Status'));
            foreach ($data as $record) {
                fputcsv($fp, array($record['custkey'], $record['custname'], $record['ordno'], $record['ponumber'], $record['ord_date'], $record['responsible'], $record['shipvia'], $record['category'], $record['lowest_status'] . '/' . $record['highest_status']));
            }
            fclose($fp);
            unset($data);
            $files[] = $filename;
        }
        /* -------------------------------------------------------------------------------------------------------------- */

        /* Multiple Discount Error Report file generating */
        $data = $mapper->buildMultipleDiscountErrorReport($form_data);
        if (count($data) > 0 && is_array($data)) {
            $filename = 'MultipleDiscountError_' . $date . '.csv';
            $fp = fopen(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename, 'w');
            fputcsv($fp, array('Multiple Discount Error Report', date('m/d/Y')));
            fputcsv($fp, array('Order Date', 'Order no', 'Status', 'Item', 'DISC1', 'DISC2', 'DISC3'));
            foreach ($data as $record) {
                fputcsv($fp, array($record['CO_Date'], $record['Order_no'], $record['Status'], $record['Item'], $record['DISC1'], $record['DISC2'], $record['DISC3']));
            }
            fclose($fp);
            unset($data);
            $files[] = $filename;
        }

        /* -------------------------------------------------------------------------------------------------------------- */

        /* Sending generated file and unseting data, deleting file */
        $recipients[] = array('email' => 'hgutierrez@jarrow.com', 'name' => 'Hector');
        $email = new Utility_Emails_AttachReportFile($recipients, $files);
        $email->send();
        foreach ($files as $filename) {
            unlink(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename);
        }
        die();
    }

    //Send Monthly Inventory Valuation Report
    public function monthlyvalutionAction() {
        $current = date("m-Y", mktime(0, 0, 0, date("m", time()) , 1, date("Y", time())));
        $tokens = explode("-", $current);
        $month = $tokens[0];
        if ((int) $month >= 4 && (int) $month <= 12)
            $year = $tokens[1] + 1;
        else
            $year = $tokens[1];
        $end_date = date("Ymd", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));
        $report_date = date("m/d/Y", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));
        $file_date = date("m_d_Y", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));
        $periods = array('04' => '1', '05' => '2', '06' => '3', '07' => '4', '08' => '5', '09' => '6', '10' => '7', '11' => '8', '12' => '9', '01' => '10', '02' => '11', '03' => '12');
        $period = $periods[$month];
        $infomapper = new Atlas_Model_Inform3jiipicklist();
        $invbalance = $infomapper->buildinventorybalance($year, $period);
        $invvaluation = $infomapper->buildinvvaluation($end_date);

        if (count($invvaluation) > 0 && is_array($invvaluation)) {
            $filename = 'Monthly_Inventory_Report_' . $file_date . '.csv';
            $fp = fopen(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename, 'w');
            fputcsv($fp, array('Monthly Inventory Report', $report_date, 'Year', $year, 'Period', $period));
            fputcsv($fp, array('GL_ACCOUNT', 'WHS', 'WHS_Name', 'Item', 'Item_Name', 'Lot', 'Expiration', 'Status',
                'Qty', 'UoM', 'Cost', 'Valuation', 'Cost_Method', 'Item_Type', 'Item_Group', 'Product_Group'));
            foreach ($invvaluation as $csa) {
                fputcsv($fp, array($csa['GL_ACCOUNT'], $csa['WHS'], $csa['WHS_Name'], $csa['Item'], $csa['Item_Name'], $csa['Lot'], $csa['Expiration'], $csa['Status'],
                    round($csa['Qty'],2), $csa['UoM'], round($csa['Cost'],2), round($csa['Valuation'],2), $csa['Cost_Method'], $csa['Item_Type'], $csa['Item_Group'], $csa['Product_Group']));
            }
            fclose($fp);
        }
        $email = new Utility_Emails_Monthlyinvrep($invbalance, $filename, $year, $period, $report_date);
        if ($email->send()) {
            unlink(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename);
        }
        die();
    }

    //Send Monthly Inventory Valuation Report JII
    public function monthlyvalutionjiiAction() {
        $current = date("m-Y", mktime(0, 0, 0, date("m", time()) , 1, date("Y", time())));
        $tokens = explode("-", $current);
        $end_date = date("Ymd", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));
        $report_date = date("m/d/Y", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));
        $file_date = date("m_d_Y", mktime(0, 0, 0, $tokens[0], cal_days_in_month(CAL_GREGORIAN, $tokens[0], $tokens[1]), $tokens[1]));
        $infomapper = new Atlas_Model_Inform3jiipicklist();
        $invvaluation = $infomapper->buildJiiMonthlyValuation($end_date);
        if (count($invvaluation) > 0 && is_array($invvaluation)) {
            $filename = 'JII_Monthly_Inventory_Report_' . $file_date . '.csv';
            $fp = fopen(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename, 'w');
            fputcsv($fp, array('Monthly Inventory Report', $report_date));
            fputcsv($fp, array('WHS', 'WHS_Name', 'Item', 'Item_Name', 'Status', 'Qty', 'UoM', 'Cost', 'Valuation', 'Cost_Method', 'Item_Type', 'Item_Group'));
            foreach ($invvaluation as $csa) {
                fputcsv($fp, array($csa['WHS'], $csa['WHS_Name'], $csa['Item'], $csa['Item_Name'], $csa['Status'], round($csa['Qty'],2), $csa['UoM'], round($csa['Cost'],2), round($csa['Valuation'],2), $csa['Cost_Method'], $csa['Item_Type'], $csa['Item_Group']));
            }
            fclose($fp);
        }
        $email = new Utility_Emails_MonthlyinvrepJii($filename, $report_date);
        if ($email->send()) {
            unlink(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename);
        }
        die();
    }

    //Upload Subscribers Daily to Mailing Service Provider VIA API
    public function subscriberstosendgridAction() {
        $date_from = date("Y-m-d", strtotime("-1 days"));
        $date_to = date("Y-m-d", strtotime("-1 days"));
        $subscriber_mapper = new Atlas_Model_SubscribersMapper();
        $subscribers = $subscriber_mapper->buildDaySubscribers($date_from, $date_to);
        $recepients = array();
        foreach ($subscribers as $subscriber) {
            $recepients[] = array('email' => strtolower(trim($subscriber['email'])), 'first_name' => $subscriber['first_name'], 'last_name' => $subscriber['last_name'], 'group' => $subscriber['group']);
        }
        $post_data1 = json_encode($recepients);

        //         Add Recepients
        $sg_mapper          =   new Atlas_Model_SendgridMapper();
        $pushed_contacts    =   $sg_mapper->addContactPost($post_data1);
        
        //Add recepient IDs to list
        $sg_mapper   =   new Atlas_Model_SendgridMapper();
        $contacts    =   $sg_mapper->addContactToListPost($pushed_contacts);
        die();
    }
    
    //Send Lot Status 3 weekly
    public function status3monthlyrepAction() {
        $report_date = date("m/d/Y");
        $infomapper = new Atlas_Model_Inform3jiipicklist();
        $monthlyrep = $infomapper->status3monthlyrep();
        if (count($monthlyrep) > 0 && is_array($monthlyrep)) {
            $filename = 'Status_3_monthly_report_' . date("m_d_Y") . '.csv';
            $fp = fopen(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename, 'w');
            fputcsv($fp, array('Status 3 Monthly Report', $report_date));
            fputcsv($fp, array('ITEM', 'DESCRIPTION', 'QTY', 'LOT', 'EXPDATE' , 'WHSE', 'LOCATION', 'STATUS', 'ITEMTYPE', 'ITEMGRP', 'COST', 'CostTotal', 'LOTSTATUS','BUYER'));
            foreach ($monthlyrep as $csa) {
                fputcsv($fp, array($csa['ITEM'], str_replace(',', ' ', $csa['DESCRIPTION']),round($csa['QTY'],2), $csa['LOT'], $csa['EXPDATE'], $csa['WHSE'], $csa['LOCATION'], $csa['STATUS'], $csa['ITEMTYPE'], $csa['ITEMGRP'], '$'.$csa['COST'], '$'.$csa['CostTotal'], $csa['LOTSTATUS'], $csa['BUYER']));
            }
            fclose($fp);
        }
        $email = new Utility_Emails_Status3MonthlyRep($filename, $report_date);
        if ($email->send()) {
            unlink(Zend_Registry::get("root_path") . "/public/uploads/csv/" . $filename);
        }
        die();
    }

    //Send Notification for New orders to be processed
    public function sgordersAction() {
            //Set SFTP CONNECTION AND CHECK NEW ORDERS
            $sftp_connection    =   new Utility_SFTPConnectionSg();
            $sftp               =   $sftp_connection->buildSftpConnectionSg();
            $file_exts          =   array('txt','csv');
            $sftp->chdir('outgoing');
            $files          =   $sftp->nlist();  
            $new_orders     =   0;
            foreach($files as $file){
                $file_parts     =   explode('.', $file);
                $file_ext       =   strtolower(end($file_parts));
                if(in_array($file_ext, $file_exts) && $file_ext == 'csv')
                    $new_orders++;
            }
            if((int)$new_orders > 0){
                $subject    =   "New Genius Central orders have been submitted";
                $body       =   $new_orders." New orders submitted and ready to process";
                $to_recipients      =   array();
                $to_recipients[]    =   array('email' => 'admin@example.com', 'name' => 'Admin');
                $email = new Utility_Emails_SgOrders($subject, $body, $to_recipients);
                $email->send();
            }
            die();
    }
    
    //Send Daily Report For Uploaded Orders VIA API
    public function sgordersreportAction() {
            $date           =   date('Y-m-d');
            $sg_mapper  =   new Atlas_Model_SgOrdersMapper();
            $results    =   $sg_mapper->buildOrderTotals($date);
            if((int)$results['end_of_day_sg']['orders_count'] > 0 
                    || (int)$results['end_of_day_atl']['orders_count'] > 0
                        || (int)$results['end_of_day_sps']['orders_count'] > 0){
                $subject    =   "Order Uploads Total Report";
                $to_recipients      =   array();
                $to_recipients[]    =   array('email' => 'admin@example.com', 'name' => 'Admin');
                $email = new Utility_Emails_OrderUploadTotals($subject, $results, $to_recipients);
                $email->send();
            }
            die();
    }
    
    //Send weekly Months On Hand Report
    public function mohreportAction() {
            $date           =   date('m/d/Y');
            $infor_mapper   =   new Atlas_Model_Inform3jiipicklist();
            $results        =   $infor_mapper->mohreport();
            if(is_array($results) || count($results) > 0){
                $subject    =   "Priority A MOH Report(".$date.")";
                $to_recipients      =   array();
                $to_recipients[]    =   array('email' => 'admin@example.com', 'name' => 'Admin');
                $email = new Utility_Emails_BasicReport($subject, $results, $to_recipients,1);
                $email->send();
            }
            die();
    }
    
    //Send Daily Reports From ERP MS SQL DB
    public function infordailyreportsAction() {
        
        $infor_mapper   =   new Atlas_Model_Inform3jiipicklist();
        $date           =   date('d-m-Y');
        //Send DO Orders
        $data = $infor_mapper->buildNewDos();
        $mail_data = new Utility_Emails_DoReport($data);
        $mail_data->send();
        
        //Send MO Orders
        $mo_data = $infor_mapper->buildMoCalendar(array('start_date' =>$date, 'end_date' => $date, 'calendar' => 0));
        $mail_mo_data = new Utility_Emails_MoReport($mo_data);
        $mail_mo_data->send();
        $infor_mapper->buildDoDataCopy();
        
        //Send Sub MO Orders
        $submo_data = $infor_mapper->SubMoCalendar(array('start_date' => $date, 'end_date' => $date));
        $mail_submo_data = new Utility_Emails_SubMoReport($submo_data);
        $mail_submo_data->send();
        die();
    }

    //Create Folders and Files Recursively
    public function filecreationAction() {

        try{
            $mapper = New Atlas_Model_UploadedFilesMapper();
            $mapper->recursiveFolderCreation('/images/uploads/new_clinicalstudies/');
            $mapper->recursiveFileCreation('/images/uploads/clinicalstudies/','/images/uploads/new_clinicalstudies/');
        } catch (Exception $e) {
            echo "\n";
            echo $e->getMessage() . "\n";
        }
        die();
    }

    public function __call($methodName, $args) {
        echo "Action is not defined in this Controller.\n";
        die();
    }

}