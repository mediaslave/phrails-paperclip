<?php
include_once('cloud/rsc/cloudfiles.php');
/**
* Interface for all of the cloud adapters
*/
class PhrailsPaperclipRsc extends AbstractPhrailsPaperclipCloud
{
	
	private $container; 
	
	/**
	 * Constructor
	 *
	 * @return Rsc
	 * @author Justin Palmer
	 **/
	public function __construct($container, $user, $key)
	{
		$this->container = $container;
		$this->cloud = new CF_Authentication($user, $key);
	}
	
	/**
	 * Connect to the cloud and get the container back.
	 * 
	 * @param string $container
	 * @return resource
	 * @author Justin Palmer
	 */
	protected function connect($container){
		$this->cloud->authenticate();
		$conn = new CF_Connection($this->cloud);
		
		return $conn->get_container($container);
	}
	/**
	 * Write the file to the cloud
	 *
	 * @return boolean
	 * @author Justin Palmer
	 **/
	public function write($object, $file_name)
	{
		$con = $this->connect($this->container);
		
		$file = $con->create_object($file_name);
		
		if(is_file($object)){
			$handle = fopen($object, 'r');
			$contents = fread($handle, filesize($object));
			fclose($handle);
			return $file->write($contents);
		}else{
			return $file->write($object);
		}
	}
	
	/**
	 * Read a document to a variable
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function read($object)
	{
		$con = $this->connect($this->container);
		$file = $con->get_object($object);
		if(!$con->is_public()){
			return $file->read();
		}else{
			return $file->public_uri();
		}
	}
	
	/**
	 * Stream a document to the browser
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function stream($object, $disposition='attachment')
	{
		$con = $this->connect($this->container);
		$file = $con->get_object($object);
		header("Content-Type: " . $file->content_type);
	    header('Content-transfer-encoding: binary');
	    header('Cache-Control: private');
	    header('Pragma: public');
	    header('Content-Disposition: ' . $disposition . '; filename="'. $file->name . '"');
	    $output = fopen("php://output", "w");
	    $file->stream($output);
	    fclose($output);
	}
}
