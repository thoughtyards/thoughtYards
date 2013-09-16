<?php

/**
 * Thoughtyards Gateway Class.
 *
 * This script is meant to be run on command line to execute
 * one of the pre-defined console commands.
 *
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/
 * @copyright Demo
 * @license GNU
 */

use ThoughtYards\Kinetics\Component\ThoughtException;
use ThoughtYards\Kinetics\Fixtures\YAML\TYConfig;
use ThoughtYards\Kinetics\ConsoleKernel\CConsoleApplication;
use ThoughtYards\Kinetics\HttpKernel\WebKit;
use ThoughtYards\Kinetics\HttpKernel\TYRequest;
use ThoughtYards\Kinetics\HttpKernel\TYUrlNormalizer;

//Explicitly Mentioning as a FINAL so It could not be inherited

final class Gateway
{
	public static $classMap=array();
	public static $_coreClasses=array();
	public static $enableIncludePath=false;

	private static $_aliases=array(); // alias => path
	private static $_imports=array();					// alias => class name or directory
	private static $_baseUrl;						// list of include paths
	private static $_app;
	private static $_logger;
	private static $_includePaths ;

	/**
	 * Registry collection
	 *
	 * @var array
	 */
	static private $_registry                   = array();

	/**
	 * Application root absolute path
	 *
	 * @var string
	 */
	static private $_appRoot;
	/**
	 * Application root absolute path
	 *
	 * @var string
	 */
	static private $_config;

	/**
	 *
	 * Register any variable inside the application
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param unknown_type $graceful
	 */

	public static function register($key, $value, $graceful = false)
	{
		if (isset(self::$_registry[$key])) {
			if ($graceful) {
				return;
			}
			throw new ThoughtException('ThoughtYards registry key "'.$key.'" already exists');
		}
		self::$_registry[$key] = $value;
	}

	/**
	 * Unregister a variable from register by key
	 *
	 * @param string $key
	 */
	public static function unregister($key)
	{
		if (isset(self::$_registry[$key])) {
			if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
				self::$_registry[$key]->__destruct();
			}
			unset(self::$_registry[$key]);
		}
	}

	/**
	 * Retrieve a value from registry by a key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function registry($key)
	{
		if (isset(self::$_registry[$key])) {
			return self::$_registry[$key];
		}
		return null;
	}

	/**
	 * Load the Application By default configurations
	 * If not, the directory will be defaulted to 'protected'.
	 * Loads and keep in memory for the application
	 * @return TYConfig
	 */
	public static function boot($default)
	{
		self::setRoot(); //setting application eviroment variables
		self::getAppConfig(); //Initiate the Cofigurations from YML
		self::getConfig(); 		//Initiate the Cofigurations from YML inside config XXweb-bootXX/XXdirectoryXX etc.
	}



	public static function getAppConfig()
	{
		$yml=new TYConfig();
		return $yml->initConfig('booting');
	}

	/**
	 * Get All the YAML files in object format
	 * If not, the directory will be defaulted to all configuration loaded.
	 * Loads and keep in memory for the application
	 * @return TYConfig
	 */

	public static function getConfig($type=null, $fileName=null)
	{
		$yml=new TYConfig();
		return $yml->LoadConfig($type, $fileName);
		//@TODO ad logic to listen config, router in TYConfig.php
	}

	/**
	 * Set all my static data to defaults
	 *
	 */
	public static function reset()
	{
		self::$_registry        = array();
		self::$_appRoot         = null;
		self::$_app             = null;
		self::$_config          = null;
		// do not reset $headersSentThrowsException
	}


	/**
	 * Set application root absolute path
	 *
	 * @param string $appRoot
	 * @throws ThoughtYards Exceptions
	 * //@TODO condition for CLI commands to load without HTTP_HOST
	 */
	public static function setRoot($appRoot = '')
	{
		if (self::$_appRoot) {
			return ;
		}

		if ('' === $appRoot) {
			// automagically find application root by dirname of Thougtyards Application
			$appRoot = dirname(__DIR__);
		}

		$appRoot = realpath($appRoot);

		if (is_dir($appRoot) and is_readable($appRoot)) {
			self::$_appRoot = $appRoot.'\\';
			
			if(isset($_SERVER['HTTP_HOST'])!= null)
			self::$_baseUrl = $_SERVER['HTTP_HOST'];	

		} else {
			self::ThoughtException($appRoot . ' is not a directory or not readable by this user');
		}
	}

	/**
	 * Retrieve application root absolute path
	 *
	 * @return string
	 */
	public static function getAppRoot()
	{
		return self::$_appRoot;
	}

	/**
	 * Retrieve application root absolute path
	 *
	 * @param string $type
	 * @return string
	 */
	public static function getBaseUrl()
	{
		return self::$_baseUrl;
	}

	/**
	 * @return string the version of ThougtYards  framework
	 */
	public static function getVersion()
	{
		return '. Thoughtyards-development-version';
	}

	/**
	 * Creates a Web application instance.
	 * @param mixed $config application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 * @return CWebApplication
	 */
	public static function WebKitInit($config=null)
	{
		return $obj= new WebKit($config);
	}

	/**
	 * Creates a console application instance.
	 * @param mixed $config application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 * @return CConsoleApplication
	 */
	public static function CosoleKitInit($config=null)
	{
		return $obj= new CConsoleApplication($config);		
	}

	/**
	 * Returns the application singleton or null if the singleton has not been created yet.
	 * @return CApplication the application singleton, null if the singleton has not been created yet.
	 */
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * Stores the application instance in the class static member.
	 * This method helps implement a singleton pattern for CApplication.
	 * Repeated invocation of this method or the CApplication constructor
	 * will cause the throw of an exception.
	 * To retrieve the application instance, use {@link app()}.
	 * @param CApplication $app the application instance. If this is null, the existing
	 * application singleton will be removed.
	 * @throws CException if multiple application instances are registered.
	 */
	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
		self::$_app=$app;
		else
		throw new ThoughtException('ThoughtYards application can only be created once.');
	}


	/**
	 * Imports a class or a directory.
	 *
	 * Importing a class is like including the corresponding class file.
	 * The main difference is that importing a class is much lighter because it only
	 * includes the class file when the class is referenced the first time.
	 *
	 * Importing a directory is equivalent to adding a directory into the PHP include path.
	 * If multiple directories are imported, the directories imported later will take
	 * precedence in class file searching (i.e., they are added to the front of the PHP include path).
	 *
	 * Path aliases are used to import a class or directory. For example,
	 * <ul>
	 *   <li><code>application.components.GoogleMap</code>: import the <code>GoogleMap</code> class.</li>
	 *   <li><code>application.components.*</code>: import the <code>components</code> directory.</li>
	 * </ul>
	 *
	 * The same path alias can be imported multiple times, but only the first time is effective.
	 * Importing a directory does not import any of its subdirectories.
	 *
	 * Starting from version 1.1.5, this method can also be used to import a class in namespace format
	 * (available for PHP 5.3 or above only). It is similar to importing a class in path alias format,
	 * except that the dot separator is replaced by the backslash separator. For example, importing
	 * <code>application\components\GoogleMap</code> is similar to importing <code>application.components.GoogleMap</code>.
	 * The difference is that the former class is using qualified name, while the latter unqualified.
	 *
	 * Note, importing a class in namespace format requires that the namespace corresponds to
	 * a valid path alias once backslash characters are replaced with dot characters.
	 * For example, the namespace <code>application\components</code> must correspond to a valid
	 * path alias <code>application.components</code>.
	 *
	 * @param string $alias path alias to be imported
	 * @param boolean $forceInclude whether to include the class file immediately. If false, the class file
	 * will be included only when the class is being used. This parameter is used only when
	 * the path alias refers to a class.
	 * @return string the class name or the directory that this alias refers to
	 * @throws CException if the alias is invalid
	 */
	public static function import($alias,$forceInclude=false)
	{
		if(isset(self::$_imports[$alias]))  // previously imported
		return self::$_imports[$alias];

		if(class_exists($alias,false) || interface_exists($alias,false))
		return self::$_imports[$alias]=$alias;

		if(($pos=strrpos($alias,'\\'))!==false) // a class name in PHP 5.3 namespace format
		{
			$namespace=str_replace('\\','.',ltrim(substr($alias,0,$pos),'\\'));
			if(($path=self::getPathOfAlias($namespace))!==false)
			{
				$classFile=$path.DIRECTORY_SEPARATOR.substr($alias,$pos+1).'.php';
				if($forceInclude)
				{
					if(is_file($classFile))
					require($classFile);
					else
					throw new ThoughtException('Alias'.$alias.' is invalid. Make sure it points to an existing PHP file and the file is readable.');
					self::$_imports[$alias]=$alias;
				}
				else
				self::$classMap[$alias]=$classFile;
				return $alias;
			}
			else
			{
				// try to autoload the class with an autoloader
				if (class_exists($alias,true))
				return self::$_imports[$alias]=$alias;
				else
				throw new ThoughtException('Alias '.$alias. ' is invalid. Make sure it points to an existing directory or file.');
			}
		}

		if(($pos=strrpos($alias,'.'))===false)  // a simple class name
		{
			if($forceInclude && self::autoload($alias))
			self::$_imports[$alias]=$alias;
			return $alias;
		}

		$className=(string)substr($alias,$pos+1);
		$isClass=$className!=='*';

		if($isClass && (class_exists($className,false) || interface_exists($className,false)))
		return self::$_imports[$alias]=$className;

		if(($path=self::getPathOfAlias($alias))!==false)
		{
			if($isClass)
			{
				if($forceInclude)
				{
					if(is_file($path.'.php'))
					require($path.'.php');
					else
					throw new ThoughtException("Alias '.$alias.' is invalid. Make sure it points to an existing PHP file and the file is readable.");
					self::$_imports[$alias]=$className;
				}
				else
				self::$classMap[$className]=$path.'.php';
				return $className;
			}
			else  // a directory
			{
				if(self::$_includePaths===null)
				{
					self::$_includePaths=array_unique(explode(PATH_SEPARATOR,get_include_path()));
					if(($pos=array_search('.',self::$_includePaths,true))!==false)
					unset(self::$_includePaths[$pos]);
				}

				array_unshift(self::$_includePaths,$path);

				if(self::$enableIncludePath && set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR,self::$_includePaths))===false)
				self::$enableIncludePath=false;

				return self::$_imports[$alias]=$path;
			}
		}
		else
		throw new ThoughtException("Alias ".$alias.' is invalid. Make sure it points to an existing directory or file.');
		
	}


	/**
	 * Creates an object and initializes it based on the given configuration.
	 *
	 * The specified configuration can be either a string or an array.
	 * If the former, the string is treated as the object type which can
	 * be either the class name or {@link Gateway::getPathOfAlias class path alias}.
	 * If the latter, the 'class' element is treated as the object type,
	 * and the rest of the name-value pairs in the array are used to initialize
	 * the corresponding object properties.
	 *
	 * Any additional parameters passed to this method will be
	 * passed to the constructor of the object being created.
	 *
	 * @param mixed $config the configuration. It can be either a string or an array.
	 * @return mixed the created object
	 * @throws CException if the configuration does not have a 'class' element.
	 */
	public static function createComponent($config)
	{
		if(is_string($config))
		{
			$type=$config;
			$config=array();
		}
		elseif(isset($config['class']))
		{
			$type=$config['class'];
			unset($config['class']);
		}
		else
		throw new ThoughtException(self::log('Object configuration must be an array containing a "class" element.'));

		if(!class_exists($type,false))
		$type=Gateway::import($type,true);

		if(($n=func_num_args())>1)
		{
			$args=func_get_args();
			if($n===2)
			$object=new $type($args[1]);
			elseif($n===3)
			$object=new $type($args[1],$args[2]);
			elseif($n===4)
			$object=new $type($args[1],$args[2],$args[3]);
			else
			{
				unset($args[0]);
				$class=new ReflectionClass($type);
				// Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
				// $object=$class->newInstanceArgs($args);
				$object=call_user_func_array(array($class,'newInstance'),$args);
			}
		}
		else {

			if($type=='TYRequest')
			{
				$object=new TYRequest;
			}
			elseif($type=='TYUrlNormalizer')
			{
				$object=new TYUrlNormalizer;
			}
			else $object=new $type;
		}
		foreach($config as $key=>$value)
		{
			$object->$key=$value;
		}

		return $object;
	}



	/**
	 * Translates an alias into a file path.
	 * Note, this method does not ensure the existence of the resulting file path.
	 * It only checks if the root alias is valid or not.
	 * @param string $alias alias (e.g. system.web.CController)
	 * @return mixed file path corresponding to the alias, false if the alias is invalid.
	 */
	public static function getPathOfAlias($alias)
	{
		if(isset(self::$_aliases[$alias]))
		return self::$_aliases[$alias];
		elseif(($pos=strpos($alias,'.'))!==false)
		{
			$rootAlias=substr($alias,0,$pos);
			if(isset(self::$_aliases[$rootAlias]))
			return self::$_aliases[$alias]=rtrim(self::$_aliases[$rootAlias].DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($alias,$pos+1)),'*'.DIRECTORY_SEPARATOR);
			elseif(self::$_app instanceof CWebApplication)
			{
				if(self::$_app->findModule($rootAlias)!==null)
				return self::getPathOfAlias($alias);
			}
		}
		return false;
	}

	/**
	 * Create a path alias.
	 * Note, this method neither checks the existence of the path nor normalizes the path.
	 * @param string $alias alias to the path
	 * @param string $path the path corresponding to the alias. If this is null, the corresponding
	 * path alias will be removed.
	 */
	public static function setPathOfAlias($alias,$path)
	{
		if(empty($path))
		unset(self::$_aliases[$alias]);
		else
		self::$_aliases[$alias]=rtrim($path,'\\/');
	}


	/**
	 * Writes a trace message.
	 * This method will only log a message when the application is in debug mode.
	 * @param string $msg message to be logged
	 * @param string $category category of the message
	 * @see log
	 */
	public static function appfootprints($msg,$level=false, $category='application')
	{
		if(self::getAppConfig()->booting->logging)
		self::log($msg,$level,$category);
	}

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
	 * and may be recorded in different media, such as file, email, database, using
	 * {@link CLogRouter}.
	 * @param string $msg message to be logged
	 * @param string $level level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	public static function log($msg,$level,$category='application')
	{
		if(self::getAppConfig()->booting->logging !=true) return;

		$level= ($level) ? $level : self::getAppConfig()->booting->log_warning_level;

		if(self::getAppConfig()->booting->developer_mode && $level>0)
		{
			$traces=debug_backtrace();
			$count=0;
			foreach($traces as $trace)
			{
				if(isset($trace['file'],$trace['line']))
				{
					$msg.="\nin ".$trace['file'].' ('.$trace['line'].')';
					if(++$count>=$level)
					break;
				}
			}
		}

		$logPath=self::getAppRoot().TY_DS.self::getAppConfig()->booting->log_dir;

		if(!is_dir($logPath)){
			mkdir( $logPath,0777,TRUE);
		}

		$logFileName = $logPath.$category.'.log';

		if(file_exists($logFileName))
		{
			$current = file_get_contents($logFileName);
			$current .= $msg."\n";
			file_put_contents($logFileName, $current);
		}
		else {
			$fileHandler=fopen($logFileName,"wb");
			fwrite($fileHandler,$msg);
			fclose($fileHandler);
		}


		//TODO Add complete class for the logging mechaninsm
		//self::$_logger->log($msg,$level,$category);
	}


	public static function beginProfile($token,$category='application')
	{
		self::log('begin:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to {@link beginProfile()} with the same token.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile
	 */
	public static function endProfile($token,$category='application')
	{
		self::log('end:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * @return CLogger message logger
	 */
	public static function getLogger()
	{
		if(self::$_logger!==null)
		return self::$_logger;
		else
		return self::$_logger=new CLogger;
	}

	/**
	 * Sets the logger object.
	 * @param CLogger $logger the logger object.
	 * @since 1.1.8
	 */
	public static function setLogger($logger)
	{
		self::$_logger=$logger;
	}


	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string $className class name
	 * @return boolean whether the class has been loaded successfully
	 */
	public static function autoload($className)
	{
		//deprecated after composer intiates the class
		// use include so that the error PHP file may appear
		if(isset(self::$classMap[$className]))
		include(self::$classMap[$className]);
		elseif(isset(self::$_coreClasses[$className]))
		include(BASE_PATH.self::$_coreClasses[$className]);
		else
		{
			// include class file relying on include_path
			if(strpos($className,'\\')===false)  // class without namespace
			{
				if(self::$enableIncludePath===false)
				{
					foreach(self::$_includePaths as $path)
					{
						$classFile=$path.DIRECTORY_SEPARATOR.$className.'.php';
						if(is_file($classFile))
						{
							include($classFile);
							if(GATEWAY_DEBUG && basename(realpath($classFile))!==$className.'.php')
							throw new ThoughtException("Class name '.$className.' does not match class file '.$classFile}");
							
							break;
						}
					}
				}
				else
				include($className.'.php');
			}
			else  // class name with namespace in PHP 5.3
			{
				$namespace=str_replace('\\','.',ltrim($className,'\\'));
				if(($path=self::getPathOfAlias($namespace))!==false)
				include($path.'.php');
				else
				return false;
			}
			return class_exists($className,false) || interface_exists($className,false);
		}
		return true;
	}
}
