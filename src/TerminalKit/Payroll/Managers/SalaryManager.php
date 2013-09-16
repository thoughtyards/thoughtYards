<?php
/*
 * Thought Yards the Innovation
 * Salary Component -Managers to the Contollers. Business logic to handle the Slary component.
 * LICENCE - GNU -
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * Salary Manager Component
 */

namespace TerminalKit\Payroll\Managers;

use ThoughtYards\Kinetics\Component\ThoughtException;
use ThoughtYards\Kinetics\Component\TYComponent;


class SalaryManager extends TYComponent
{
	/**
	 * Declaration of Columns
	 * @private
	 * @return array
	 * More columns can be added to increase the column numbers in the CSV.
	 */
	private $columnNames=array( "Month",
								"Bonus Date",
								"Salary Date" );

	/**
	 * @private Array fileOutput
	 */
	private $fileOutput=array();

	
	/**
	 * @private string fileType
	 * More Content File type can be added here. To get the file in different outputs like HTML, XML, TXT etc.
	 * Provide parser Classes must be initiated
	 */
	private $fileType='CSV';

	public function __construct(){
		$this->init();
	}

	/**
	 * Setting the Rows Title in Header of the File
	 * @private
	 * @return array
	 */
	public function init()
	{
		$this->setRowHeader($this->columnNames);
	}

	public function setRowHeader($row=array())
	{
		if(sizeof($row)>0 && is_array($row))
		$this->addRows($row);
	}

	/**
	 * Adding Rows to the File, Set the fileOutput Array
	 * @private
	 * @return nulle
	 */
	private function addRows( $row=array() )
	{
		if(sizeof($row)>0 && is_array($row))
		array_push($this->fileOutput, $row);
		else throw new ThoughtException($this->fixtures()->log('Object configuration must be an array containing element. (**Check root/var/ folder))', null, 'TerminalApp'));
		//trying to demonstrate the Exceptions getting logged in 'TerminalApp.log file through Framework'.
	}

	/**
	 * Get Salary for the Current Year.
	 * @public
	 * @return raw Array
	 */
	public function getSalary()
	{
		$currentDate = date('Y-m-d');

		$currentMonth = date('n',strtotime($currentDate));
		$startDate = date('Y-m-d',strtotime('first day of this month'));

		$tempDate = $startDate; //Temporary Varibale to store the Date.

		for($i=$currentMonth;$i<=12;$i++){

			$month=date('M',strtotime($tempDate)); //TODO @Vipul Get The Fulll Month name from here
			$bonusDate = $this->calculateBonus($tempDate);
			$salaryDate = $this->calculateSalary($tempDate);
			$singleRowData= array ($month, $bonusDate , $salaryDate ); //Adding array to returning Array
			$this->addRows($singleRowData);
			$tempDate = date("Y-m-d", strtotime("$tempDate +1 month"));
		}

		return $this->fileOutput;
	}

	/**
	 * Calculate the Bonus disbursement dates for Employee
	 * @private
	 * @return raw Array
	 */

	private function calculateBonus($tempDate){

		$bonusSateHingePoint = date('Y-m-d', strtotime($tempDate. ' + 14 day'));
		$day = date('D', strtotime($bonusSateHingePoint));
		if($day =='Sat' || $day =='Sun'){
			$bonusDate = date('Y-m-d', strtotime('next Wednesday',strtotime($bonusSateHingePoint)));
		}
		else
		$bonusDate = $bonusSateHingePoint;

		return $bonusDate;
	}

	/**
	 * Calculate the Salary disbursement dates for Employee
	 * @private
	 * @return raw Array
	 */
	private function calculateSalary($tempDate){
		$lastDay = date('Y-m-t', strtotime($tempDate));
		$day = date('D',strtotime($lastDay));
		if($day =='Sat' || $day =='Sun'){
			$salaryDate = date('Y-m-d', strtotime('last Friday',strtotime($lastDay)));
		}
		else
		$salaryDate = $lastDay;

		return $salaryDate;
	}
}