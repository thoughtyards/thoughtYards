<?php
/**
 * TerminalKit\Payroll Controller Class.
 *
 * This Controller is meant to be run on web Browser to get execute
 * Defined controller.
 * Property of Web kit Conroller. Action decined for Controller
 * Terminal Controller Class
 * Default class which gets load when no query params given 
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/
 * @copyright Demo
 * @license GNU
 */

namespace TerminalKit\Payroll\controllers;

use ThoughtYards\Kinetics\Terminal\Shell;
use ThoughtYards\Kinetics\Component\Controller;

class TerminalController extends Controller
{
	/**
	 * Intiate the Terminal Windos in the Browser
	 * Response Render the emulatedshell.php template file
	 * response CSS and JS path to template file
	 **/
	public function ActionIndex()
	{
		$InitTerminalHelper=$this->getGateWayApp()->getContainer()->get('terminal.helper')->TerminalDumper($this);
		if($InitTerminalHelper)
		{
		    $cssPath=$this->getGateWay()->getConfig('config')->Payroll->cssPath;
		    $jsPath=$this->getGateWay()->getConfig('config')->Payroll->jsPath;    		
		    $this->render('emulatedshell', array('cssRequire' => $cssPath,
    				'jsRequire' => $jsPath , 'routers'=>$this->getRoute()
			));
		}

	}
}