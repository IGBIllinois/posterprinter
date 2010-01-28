<?php
include_once 'PHPExcel.php';
include_once 'PHPExcel/IOFactory.php';

//create_excel_2003_report()
//$data - double array - data values
//$file_name - string - name of the file to create
//prompts to save an xml excel file with the data
function create_excel_2003_report($data,$month,$year) {
	$filename = get_filename($month,$year,'xls');
	$excel_file = create_generic_excel($data,$month,$year);
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment;filename=" . $filename);
	header('Cache-Control: max-age=0');
	$writer = PHPExcel_IOFactory::createWriter($excel_file,'Excel5');
	$writer->save('php://output');

}

function create_excel_2007_report($data,$month,$year) {

	$filename = get_filename($month,$year,'xlsx');
	$excel_file = create_generic_excel($data,$month,$year);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename=" . $filename);
	header('Cache-Control: max-age=0');
	$writer = PHPExcel_IOFactory::createWriter($excel_file,'Excel2007');
	$writer->save('php://output');

}


function create_pdf_report($data,$month,$year) {

        $excel_file = create_generic_excel($data,$month,$year);
	header('Content-Type: application/pdf');
        header("Content-Disposition: attachment;filename=" . $filename);
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel_file,'PDF');
	$writer->save('php://output');
}

function create_generic_excel($data,$month,$year) {

	$excel_file = new PHPExcel();
	$excel_file->setActiveSheetIndex(0);
	$headings = array_keys($data[0]);
        for ($i=0;$i<count($headings);$i++) {
                $excel_file->getActiveSheet()->setCellValueByColumnAndRow($i,1,$headings[$i]);
		$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setBold(true);	
		$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setUnderline(PHPExcel_STYLE_Font::UNDERLINE_SINGLE);
		$excel_file->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);	
        }
	$rows = count($data);
	$row = 3;
	foreach ($data as $row_data) {
		$column=0;
		foreach ($row_data as $key => $value) {
			$excel_file->getActiveSheet()->setCellValueByColumnAndRow($column,$row,$value);
			if ($key == 'Cost') {
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			}
			else {
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			}	
			$column++;
		}
		$row++;
	}

	$excel_file->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$excel_file->getActiveSheet()->getPageSetup()->setFitToPage(true);
	$month_name = date('F', mktime(0,0,0,$month,1));
	$excel_file->getActiveSheet()->getHeaderFooter()->setOddHeader($month_name . " " . $year . "\n\rPoster Printing");
	
	return $excel_file;

}
function create_csv_report($data,$month,$year) {
	$filename = get_filename($month,$year,'csv');
	$delimiter = ",";
        $file_link = sys_get_temp_dir() . "/" . $filename;
	@unlink($file_link);
	$file_handle = fopen($file_link,"x");
        $headings = array_keys($data[0]);
       	fputcsv($file_handle,$headings,$delimiter);
	for ($i=0;$i<count($data);$i++) {
                $row = array_values($data[$i]);
		fputcsv($file_handle,$row,$delimiter);        
	}	
        fclose($file_handle);
	//Sets headers then downloads the csv report file.
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment; filename=" . $filename);
        readfile($file_link);
        unlink($file_link);

}

function get_filename($month,$year,$ext) { return "PosterReport-" . $month . "-" . $year . "." . $ext; }
?>
