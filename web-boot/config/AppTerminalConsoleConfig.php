<?php

// This is the main Web application configuration. Any writable
// console Application properties can be configured here.

return array(
	'basePath'=>Gateway::getAppRoot().'/src/TerminalKit/Payroll',
	'name'=>'Terminal Kit - Payroll Console App',
	'preload'=>array('log'),
	// dipendency injection configuration
    'container' => array(
        'class' => 'TYContainer',
        'services' => array(
		   'csv_helper'=>'\TerminalKit\Payroll\Helpers\CsvHelper',
		   'salary.manager'=>'\TerminalKit\Payroll\Managers\SalaryManager',
		   'terminal.helper'=>'\TerminalKit\Payroll\Helpers\TerminalHelper'		   
        )
    ),
	'components'=>array(
	'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'post/<id:\d+>/<title:.*?>'=>'post/view',
				'posts/<tag:.*?>'=>'post/index',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		),
	);
