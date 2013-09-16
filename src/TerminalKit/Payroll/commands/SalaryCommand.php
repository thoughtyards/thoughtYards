<?php
/**
 * TerminalKit\Payroll ConsoleCommand Class.
 *
 * This Controller is meant to be run on CLI window to get execute
 * Defined Command.
 * Property of ConsoleKernel . Action decined for Command
 * Salary Command Class
 * Works on the CLI pattern 
 * php console.php salary 
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/
 * @copyright Demo
 * @license GNU
 */

use ThoughtYards\Kinetics\ConsoleKernel\CConsoleCommand;
use ThoughtYards\Kinetics\Component\ThoughtException;

class SalaryCommand extends CConsoleCommand
{

	/*
	 * string $csvFile
	 * @private
	 * @decalaration default generation of file.
	 */
	private $csvFile='Salary.csv';

	/**
	 * Help for the Command Salary
	 * @public String
	 * @response String 
	 */
	public function getHelp()
	{
		return <<<EOD
USAGE
  execute salary [CSV FileName output | arguments]

DESCRIPTION
  This Command line interface allows you to interact with Mikko's Test Salary disbursement logic.
  
  This is Salary Payroll date's command by which employer can 
  generate the date's on disbtribution of salary/bonus to his emaployees. 

  EXPORT FILE- You can download the file through FTP.
  You can also change the file export path from  root/"booting.yml".
  
  The Framework used behind the scene is "ThoughtYards" .
  @@LICENCE GNU. 
  @@Framewok is developed by Vipul Dadhich,  just an inception for writing Mikko Test.

PARAMETERS
 * [ CSV FileName output | arguments ]

EOD;
	}

	
	/**
	 * default Run method
	 * @param args Array// Arguments passed from the CLI.
	 * @public
	 * @response String //status of the file generated , Exception if not valid fileType
	 */
	public function run($args)
	{
		try
		{
			$SalaryManager=$this->getGateWayApp()->getContainer()->get('salary.manager')->getSalary();
			if($SalaryManager!==null)
			{
				$exportPath=$this->getGateWay()->getConfig('config')->Payroll->export_dir;

				if(sizeof($args)>0){
					if('csv' == substr(strrchr($args[0], '.'), 1)) //checking valid file extension
					$this->csvFile= trim($args[0]); //Sanitization is handeled by CSV Helper
					else{
						$error= "ERROR: Invalid CSV file '".$args[0]."', Please enter the FileName with extension .CSV.\n\n";
						return false;
					}
				}

				$args = array (
				'csv_file'			=>	$this->csvFile,
				'csv_delimiter'		=>	",",
				'csv_write_array'	=>	$SalaryManager,
				'exportPath'        =>  $exportPath
				);

				$CsvHelper=$this->getGateWayApp()->getContainer()->get('csv_helper');
				$result= $CsvHelper->getWrite($args);
				echo $result['csv_file_write_status'];
				return 	$result;
			}
		}catch (ThoughtException $e) {
			throw new ThoughtException('Caught exception: '.  $e->getMessage());
		}
	}
	
	/**
	 * To show help after each command get executed
	 */
	public function __destruct()
	{
		//echo $this->getHelp();
	}
}
?>