<?php
/**
 * TerminalKit\Payroll Controller Class.
 *
 * This Controller is meant to be run on web Browser to get execute
 * Defined controller.
 * Property of Web kit Conroller. Action decined for Controller
 * Salary Controller
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/
 * @copyright Demo
 * @license GNU
 */


namespace TerminalKit\Payroll\controllers;

use Thoughtyards\Kinetics\Component\ThoughtException;

use ThoughtYards\Kinetics\Component\Controller;

class ShellController extends Controller
{
	/*
	 * @public action salary
	 * accessed through /shell/salary
	 * @Return CSV file
	 */
	public function actionSalary()
	{
		try
		{
			$SalaryManager=$this->getGateWayApp()->getContainer()->get('salary.manager')->getSalary();
			if($SalaryManager!==null)
			{
				$exportPath=$this->getGateWay()->getConfig('config')->Payroll->export_dir;
				$csv_file='Salary.csv';
					
				$args = array (
				'csv_file'			=>	$csv_file,
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
}
