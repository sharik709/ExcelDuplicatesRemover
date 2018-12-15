<?php

namespace App\Http\Controllers;

use function file_get_contents;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelDuplicateController extends Controller
{


	public function compare(Request $request)
	{
	    $file1 = collect($this->parse_csv_file(base_path().'/Supression.csv')); // unique numbers from this file will be downloaded
	    $file2 = collect($this->parse_csv_file(base_path().'/Exclusive-(2).csv')); // the one you want to filter against

	    $uniqueNumbers = $file1->diff($file2);

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
        foreach ($uniqueNumbers as $number) {
            $csv->insertOne([$number]);
        }

        return $csv->output('unique_numbers.csv');

	}

    function parse_csv_file($csvfile) {
        $csv = Array();
        $rowcount = 0;
        if (($handle = fopen($csvfile, "r")) !== FALSE) {
            $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            $header_colcount = count($header);
            while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
                $row_colcount = count($row);
                if ($row_colcount == $header_colcount) {
                    $entry = array_combine($header, $row);
                    $csv[] = $row[0];
                }
                else {
                    error_log("csvreader: Invalid number of columns at line " . ($rowcount + 2) . " (row " . ($rowcount + 1) . "). Expected=$header_colcount Got=$row_colcount");
                    return null;
                }
                $rowcount++;
            }
            //echo "Totally $rowcount rows found\n";
            fclose($handle);
        }
        else {
            error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }
        return $csv;
    }

}
