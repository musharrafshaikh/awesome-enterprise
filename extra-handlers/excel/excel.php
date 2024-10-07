<?php
namespace aw2\excel;

\aw2_library::add_service('excel', 'Excel Library', ['namespace' => __NAMESPACE__]);

\aw2_library::add_service('excel.write_bulk', 'Bulk write in the excel file. Use excel.write_bulk', ['namespace' => __NAMESPACE__]);
function write_bulk($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('file_name' => '', 'folder' => '', 'file_format' => 'Xlsx', 'data' => '', 'template_file' => '', 'template_folder' => ''), $atts));
    $xlsdata = \aw2_library::get($data);
    
    $file_path = $folder . $file_name;
    if (!array_key_exists('pageno', $xlsdata)) {
        $pageno = 1;
    } else {
        $pageno = $xlsdata['pageno'];
    }
    if ($pageno === 1) {
        if ($template_file) {
            $template_path = $template_folder . $template_file;
            $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($template_path);
        } else {
            $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        }
        //Add Header
        $objPHPExcel->setActiveSheetIndex(0);
        if (array_key_exists('header', $xlsdata)) {
            $objPHPExcel->getActiveSheet()->fromArray($xlsdata['header'], null, 'A1');
        }
    } else {
        $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $objPHPExcel->setActiveSheetIndex(0);
    }
    // Add data
    if (array_key_exists('rows', $xlsdata)) {
        $row = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow() + 1;
        $objPHPExcel->getActiveSheet()->fromArray($xlsdata['rows'], null, 'A' . $row);
    }
    $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);
    $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, $file_format);
    $objWriter->save($file_path);
}

\aw2_library::add_service('excel.file_reader', 'Read the excel file. Use excel.file_reader', ['namespace' => __NAMESPACE__]);
function file_reader($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('file_path' => '', 'folder' => '', 'file_format' => 'Excel2007', 'start_from' => '2', 'limit' => ''), $atts));
    if (!is_readable($file_path)) {
        \aw2_library::set_error('File ' . $file_path . ' is not readable');
        return;
    }
    
    /**  Identify the type of $inputFileName  **/
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
    /**  Create a new Reader of the type that has been identified  **/
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    $fileObj = $objReader->load($file_path);
    $sheetObj = $fileObj->getActiveSheet();
    $return_value = array();
    foreach ($sheetObj->getRowIterator($start_from, $limit) as $key => $row) {
        if (isExcelRowEmpty($row)) {
            continue;
        }
        foreach ($row->getCellIterator() as $cell) {
            $return_value[$key][] = $cell->getCalculatedValue();
        }
    }
    $return_value = \aw2_library::post_actions('all', $return_value, $atts);
    return $return_value;
}

function isExcelRowEmpty($row)
{
    foreach ($row->getCellIterator() as $cell) {
        if ($cell->getValue()) {
            return false;
        }
    }
    return true;
}

\aw2_library::add_service('excel.info', 'Read and return the excel info. Use excel.info', ['namespace' => __NAMESPACE__]);
function info($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('file_path' => '', 'file_format' => 'Excel2007'), $atts));
    if (!is_readable($file_path)) {
        \aw2_library::set_error('File ' . $file_path . ' is not readable');
        return;
    }
    
    /**  Identify the type of $inputFileName  **/
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
    /**  Create a new Reader of the type that has been identified  **/
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    $worksheetData = $objReader->listWorksheetInfo($file_path);
    \aw2_library::set('worksheets', $worksheetData);
    $total_rows = $worksheetData[0]['totalRows'];
    $total_rows = \aw2_library::post_actions('all', $total_rows, $atts);
    return $total_rows;
}

\aw2_library::add_service('excel.dataset_write', 'Write the excel file. Use excel.dataset_write', ['namespace' => __NAMESPACE__]);
function dataset_write($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('file_name' => '', 'folder' => '', 'file_format' => 'Xlsx', 'dataset' => '', 'template_file' => '', 'template_folder' => ''), $atts));
    
    $file_path = $folder . $file_name;
    if (empty($file_format)) {
        $file_format = 'Xlsx';
    }
    if (!array_key_exists('pageno', $dataset)) {
        $pageno = 1;
    } else {
        $pageno = $dataset['pageno'];
    }
    if ($pageno == 1) {
        if ($template_file) {
            $template_path = $template_folder . $template_file;
            $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($template_path);
        } else {
            $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        }
        //Add Header
        $objPHPExcel->setActiveSheetIndex(0);
        if (array_key_exists('header', $dataset)) {
            $objPHPExcel->getActiveSheet()->fromArray($dataset['header'], null, 'A1');
        }
    } else {
        $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $objPHPExcel->setActiveSheetIndex(0);
    }
    // Add data
    if (array_key_exists('rows', $dataset)) {
        $row = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow() + 1;
        $objPHPExcel->getActiveSheet()->fromArray($dataset['rows'], null, 'A' . $row);
    }
    $objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);
    $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, $file_format);
    $objWriter->save($file_path);
}

\aw2_library::add_service('excel.write_bulk_csv', 'Bulk write the excel file as csv. Use excel.write_bulk_csv', ['namespace' => __NAMESPACE__]);
function write_bulk_csv($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('file_name' => '', 'folder' => '', 'data' => ''), $atts));
    $xlsdata = \aw2_library::get($data);
    $file_path = $folder . $file_name;
    if (!array_key_exists('pageno', $xlsdata)) {
        $pageno = 1;
    } else {
        $pageno = $xlsdata['pageno'];
    }
    if ($pageno == 1) {
        $fp = fopen($file_path, 'w');
        if (array_key_exists('header', $xlsdata)) {
            fputcsv($fp, $xlsdata['header']);
        }
    } else {
        $fp = fopen($file_path, 'a');
    }
    if (array_key_exists('rows', $xlsdata)) {
        foreach ($xlsdata['rows'] as $fields) {
            fputcsv($fp, $fields);
        }
    }
    fclose($fp);
}

\aw2_library::add_service('excel.read_header', 'Read the header info of the excel file. Use excel.read_header', ['namespace' => __NAMESPACE__]);
function read_header($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('filename' => '', 'folder' => ''), $atts));

    $file_path = $folder . $filename;
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file_path);
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($file_path);
    $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumm);
    $arr = array();
    for ($col = 0; $col < $highestColumnIndex; $col++) {
        $row = 1;
        $cell = $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($col, $row);
        $arr[] = $cell->getValue();
    }
    $return_value = \aw2_library::post_actions('all', $arr, $atts);
    return $return_value;
}

\aw2_library::add_service('excel.read_post_data', 'Read the post data. Use excel.read_post_data', ['namespace' => __NAMESPACE__]);
function read_post_data($atts, $content = null, $shortcode)
{
    if (\aw2_library::pre_actions('all', $atts, $content, $shortcode) == false) {
        return;
    }
    extract(\aw2_library::shortcode_atts(array('filename' => '', 'folder' => '', 'posts_per_page' => '', 'offset' => 0), $atts));
	
	$file_path = $folder . $filename;
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file_path);
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($file_path);
    $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumm);
    $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    $arr = array();
    $arr['found_posts'] = $highestRow - 1;
    for ($col = 0; $col < $highestColumnIndex; $col++) {
        $row = 1;
        $cell = $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($col, $row);
        $pieces = explode(":", $cell->getValue());
        $new = array();
        $new['table'] = $pieces[0];
        $new['field'] = $pieces[1];
        $arr['header'][] = $new;
    }
    $start_row = 2 + $offset;
    $end_row = $start_row + $posts_per_page - 1;
    if ($end_row > $highestRow) {
        $end_row = $highestRow;
    }
    $sheetObj = $objPHPExcel->setActiveSheetIndex(0);
   
    for ($row = $start_row; $row <= $end_row; $row++) {
        $cols = array();
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $cell = $sheetObj->getCellByColumnAndRow($col, $row);
            $new = array();
            $new['table'] = $arr['header'][$col]['table'];
            $new['field'] = $arr['header'][$col]['field'];
            $new['value'] = $cell->getValue();
            if (is_null($new['value'])) {
                $new['value'] = '';
            }
            $cols[] = $new;
        }
        $arr['data'][] = $cols;
    }
    $return_value = \aw2_library::post_actions('all', $arr, $atts);
    return $return_value;
}
