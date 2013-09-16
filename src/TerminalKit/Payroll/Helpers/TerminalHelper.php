<?php
/**
 * Thoughtyards Terminal Helpers Class.
 *
 * This is Helper Class to deal with Terminal Emulation Files in the Program
 * This Class is Catered Through Dependency Injection.
 *
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/
 * @copyright Demo
 * Many Thanks to http://sourceforge.net/projects/shcmd                                    |
 * Pavel Tzonkov <pavelc@users.sourceforge.net>
 * Code has been referenced originally from the above source
 * @license GNU
 * No Responsibity of this class as this downloaded as is
 */


/**
 * Pavel Tzonkov <pavelc@users.sourceforge.net>
 * Code has been referenced originally from the above source
 * // Maximal number of characters per line in displayed
 // history dropdown

 //............................................................. USER ACCOUNTS
 // The passwords should be stored with their md5 sums.
 // For example, the following two lines do one and the same thing. If you
 // uncomment one of them it creates an user account with username 'user' and
 // password 'pass'.
 //
 // $user[] = "user";		$pass[] = md5("pass");
 // $user[] = "user";		$pass[] = "1a1dc91c907325c69271ddf0c944bc72";
 //
 // You can add more than one user accounts.
 **/
namespace TerminalKit\Payroll\Helpers;

class TerminalHelper{

	//ALIASES
	private $alias = array(
    'la'    => "ls -la",
    'rf'    => "rm -f",
    'unbz2' => "tar -xjpf",
    'ungz'  => "tar -xzpf",
    'top'   => "top -bn1"
    );

    public function TerminalDumper($controller) {
    	session_start();
    	//TODO @ Vipul Session should not be started Like This.
    	//Since this is the Demo application not ready for the production I have used this.
    	//Once the ThoughtYards Framework starts initiating the session own.its own session would be deleted form here.
    	//JUST a MEMORY HOG.. and wrong way to intiate the commands
    	//Comments by Vipul Dadhich

    	$pr_login = "Login:";
    	$pr_pass = "Password:";
    	$err = "Invalid login!";
    	$succ = "Successful login!";

    	unset($user, $pass);
    	$history_chars = 20;
    	$user[] = "user";		$pass[] = md5("pass");

    	if (isset($_GET['cmd']))
    	$_GET['cmd'] = $this->gpc_clear_slashes($_GET['cmd']);


    	//............................................................. NOT LOGGED IN
    	if (isset($_GET['cmd']) && !isset($_SESSION['shcmd']['user'])) {

    		//........................................... WE HAVE USERNAME & PASSWORD
    		if (isset($_SESSION['shcmd']['login']) && isset($_GET['cmd'])) {
    			$output = "\n$pr_pass";

    			//................................................... USERNAME EXISTS
    			if (in_array($_SESSION['shcmd']['login'], $user)) {

    				$key = array_search($_SESSION['shcmd']['login'], $user);

    				if ($pass[$key] != md5($_GET['cmd'])) { //........ WRONG PASSWORD
    					$output .= "\n$err\n";
    					unset($_SESSION['shcmd']['login']);
    					$prompt = $pr_login;

    				} else { //..................................... SUCCESSFUL LOGIN
    					$_SESSION['shcmd']['user'] = $_SESSION['shcmd']['login'];
    					$_SESSION['shcmd']['whoami'] = substr(shell_exec("whoami"), 0, -1);
    					$_SESSION['shcmd']['host'] = substr(shell_exec("uname -n"), 0, -1);



    					$output .= "\n$succ\n";
    					$prompt = $this->set_prompt();
    					unset($_SESSION['shcmd']['login']);
    				}

    			} else { //......................................... NO SUCH USERNAME
    				$output .= "\n$err\n";
    				unset($_SESSION['shcmd']['login']);
    				$prompt = $pr_login;
    			}

    			//................................................. WE HAVE ONLY USERNAME
    		} elseif (!isset($_SESSION['shcmd']['login'])) {
    			$_SESSION['shcmd']['login'] = $_GET['cmd'];
    			$output = "\n$pr_login {$_GET['cmd']}";
    			$prompt = $pr_pass;
    		}
    		$this->ajax_dump($prompt, $output);
    	} elseif (isset($_GET['cmd'])) {

    		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
    			$_SESSION['shcmd']['dir'] = getcwd(); 
    		}
    			else{
    				$_SESSION['shcmd']['dir'] = substr(shell_exec("pwd"), 0, -1);
    			}
    			 
    			chdir($_SESSION['shcmd']['dir']);

    			$prompt = $this->set_prompt();
    			$first_word = $this->first_word($_GET['cmd']);

    			switch ($first_word) {

    				case "exit":
    					session_destroy();
    					$output = "\n$prompt{$_GET['cmd']}\n" . substr(shell_exec("{$_GET['cmd']} 2>&1"), 0, -1);
    					break;

    				case "cd":
    					$output = "\n$prompt";
    					$result = shell_exec($_GET['cmd'] . " 2>&1 ; pwd");
    					$result = explode("\n", $result);

    					if (count($result) > 2) //.................. WE HAVE AN ERROR MESSAGE
    					$result[0] = "\n" . substr($result[0], strpos($result[0], "cd: "));
    					else {
    						$_SESSION['shcmd']['dir'] = $result[0];
    						$result[0] = "";
    					}

    					$prompt = $this->set_prompt();
    					$output .= $_GET['cmd'] . $result[0];
    					break;

    				default:
    					if (array_key_exists($_GET['cmd'], $this->alias))
    					$_GET['cmd'] = $this->alias[$_GET['cmd']];
    					$output = "\n$prompt{$_GET['cmd']}\n" . substr(shell_exec("{$_GET['cmd']} 2>&1"), 0, -1);
    			}

    			$this->ajax_dump($prompt, $output);

    	} else {

    		//@TODO make the layout file XML Loader and pass the CSS and JS loading to the XML's
    		//Loading it dynamically by adding actions.xml file referenced by request path.
    		//CSS and JS autoloading feature needs to be incorporated in ThoughtYards Framework.
    		//echo $cssPath=$this->getGateWay()->getAppRoot().$cssPath;
    		return true;

    	}
    }

    /**
     *
     * Dumping the ajax response back to template
     * @param string $prompt
     * @param string $output
     */
    protected function ajax_dump($prompt, $output) {
    	echo "$prompt\r$output";
    }

    /**
     *
     * Setting the prompt after each command get's Executed
     * @response string $output
     */
    protected function set_prompt() {
    	return $_SESSION['shcmd']['whoami'] . "@" . $_SESSION['shcmd']['host'] . " " . substr($_SESSION['shcmd']['dir'], strrpos($_SESSION['shcmd']['dir'], "/") + 1) . " $ ";
    }

    protected function first_word($str) {
    	list($str) = preg_split('/[ ;]/', $str);
    	return $str;
    }


    protected function gpc_clear_slashes($sbj) {
    	if (ini_get('magic_quotes_gpc'))
    	$sbj = stripslashes($sbj);
    	return $sbj;
    }

}
