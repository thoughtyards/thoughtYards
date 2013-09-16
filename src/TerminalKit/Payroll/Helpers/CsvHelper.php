<?php
/**
 * Thoughtyards CSV Helpers Class.
 *
 * This is Helper Class to deal with CSV Files in the Program
 * This Class is Catered Through Dependency Injection.
 *
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/
 * @copyright Demo
 * @license GNU
 */

namespace TerminalKit\Payroll\Helpers;

class CsvHelper{

	/**
	 * Properties which can be used in the program
	 *
	 * @param array $args
	 * @return declaration
	 */
	private $args = array (
							'csv_file'			=>	NULL,
							'csv_delimiter'		=>	",",
							'csv_fields_num'	=>	TRUE,
							'csv_head_read'		=>	TRUE,
							'csv_head_label'	=>	TRUE,				
							'csv_write_array'	=>	NULL,
						 );

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Update default arguments, It will update default array of class i.e $args
	 * @param array $args - input arguments
	 * @param array $defatuls - default arguments
	 * @return array
	 */

	private function ParseArgs( $args = array(), $defaults = array() ) {
		return array_merge( $defaults, $args );
	}

	/**
	 * Set default arguments, It will set default array of class i.e $args
	 * @private
	 * @param array $args - input arguments
	 * @return 0
	 */

	private function SetArgs( $args = array() ) {

		$defaults = $this->getArgs();
		$args = $this->ParseArgs( $args, $defaults );
		$this->args = $args;
	}


	/**
	 * Get default arguments, It will set default array of class i.e $args
	 * @public
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * Set Read, It will read CSV file.
	 * @private
	 * @return array
	 */
	public function setRead( $args ) {

		/* Arguments */
		$this->SetArgs( $args );
		$args = $this->getArgs();
		extract( $args );

		/* Temporary Array */
		$temp = array();
		$temp['csv_file_read']			= FALSE;
		$temp['csv_file_read_status']	= "Unknown Error";
		$temp['csv_file']				= $csv_file;

		/* CSV File Validation: Readability */
		if( !is_readable( $csv_file ) ) {
			$temp['csv_file_read_status']	= "CSV File is not Readable";
			return $temp;
		}

		/* CSV Data Array */
		$csv_file_data = array();

		/* CSV Head Label Logic */
		$csv_head_label_array = array();


		/* CSV Read Algorithm */
		$row = 1;
		$handle = fopen( $csv_file, "r" );
		while ( ( $data = fgetcsv( $handle, 1000, $csv_delimiter ) ) !== FALSE ) {

			/* CSV First Row: Assumed as Head */
			if( $csv_head_read  == FALSE && $row == 1 ) {

				/* Next Row */
				$row++;
				/* Skip Head */
				continue;
					
			}

			/* CSV Fields in Current Row */
			$num = count($data);

			/* Should We Take Fields Info */
			if( $csv_fields_num == TRUE ) {
				$csv_file_data[$row]['fields'] = $num;
			}

			/* Read CSV Fields in Current Row */
			for ( $c = 0; $c < $num; $c++ ) {

				/* CSV Standard Read */
				$csv_file_data[$row][$c] = $data[$c];

				/* CSV Head Label Logic */
				if( $csv_head_read  == TRUE && $csv_head_label == TRUE ) {
					$head_label = strtolower ( $csv_file_data[1][$c] );
					$csv_file_data[$row][$head_label] = $data[$c];
				}
			}

			/*  Next Row */
			$row++;
		}
		fclose($handle);

		/* Ready to Return */
		$temp['csv_file_read'] = TRUE;
		$temp['csv_file_read_status']	= "CSV File Read Successfully";
		$temp['csv_file_data'] = $csv_file_data;


		return $temp;

	}


	/**
	 * Get Read, It will convert CSV data into Array.
	 * @public
	 * @return array
	 */
	public function getRead( $args = array() ) {
		return $this->setRead( $args );
	}

	/**
	 * Set Write It will write CSV file.
	 * @private
	 * @return array
	 */
	private function setWrite( $args ) {

		/* Arguments */
		$this->setArgs( $args );
		$args = $this->getArgs();
		extract( $args );

		/* Temporary Array */
		$temp = array();
		$temp['csv_file_write']			= FALSE;
		$temp['csv_file_write_status']	= "Unknown Error";
		$temp['csv_file']				= $csv_file;

		if($args['exportPath']!==null)
		{
			if(!is_dir($args['exportPath'])){
				mkdir( $args['exportPath'], 0777,true);
			}
			$csv_file=$args['exportPath'].$csv_file;
		}
			
			
		/* File Opening: Validation */
		if ( !$handle = fopen( $csv_file, 'w' ) ) {
			$temp['csv_file_write_status'] = "Cannot Open File";
			return $temp;
		}

		/* CSV Write Array: Validation */
		if( !( is_array( $csv_write_array ) && count( $csv_write_array ) >= 1 ) ) {
			$temp['csv_file_write_status'] = "Unable to Process CSV Write Array";
			return $temp;
		}

		/* Prepare Data to Write */
		$data = "";
		foreach( $csv_write_array as $val ) {

			$data_temp = '';
			foreach( $val as $val2 ) {
				$data_temp .= $val2 . $csv_delimiter;
			}

			$data .= rtrim( $data_temp, $csv_delimiter ) . "\r\n";
		}

		/* Write Data */
		if ( fwrite( $handle, $data ) === FALSE ) {
			$temp['csv_file_write_status'] = "Cannot Write to File";
			return $temp;
		}

		else {

			$temp['csv_file_write']			= TRUE;
			$temp['csv_file_write_status'] = "SUCCESS: CSV File Written Successfully. Check ". $csv_file."\n\n";

		}

		return $temp;

	}


	/**
	 * Get Write,It will write CSV file from PHP Array
	 * @public
	 * @return array
	 */
	public function getWrite( $args = array() ) {
		return $this->setWrite( $args );
	}

	public function downloadCSV($filePath,$data)
	{
	 header("Content-type: text/csv");
	 header("Content-Disposition: attachment; filename=$filePath.csv");
	 header("Pragma: no-cache");
	 header("Expires: 0");
	}

	/**
	 * Desctructor if required
	 */

	public function __destruct() {
	}
}
?>