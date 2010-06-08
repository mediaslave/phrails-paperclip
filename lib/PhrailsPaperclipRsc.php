<?php
include_once('cloud/rsc/cloudfiles.php');
/**
* Interface for all of the cloud adapters
*/
class PhrailsPaperclipRsc extends AbstractPhrailsPaperclipCloud
{
	
	/**
	 * Constructor
	 *
	 * @return Rsc
	 * @author Justin Palmer
	 **/
	public function __construct($username, $key)
	{
		$this->cloud = new CF_Authentication($username, $key);
	}
	
	protected function connect(){
		
	}
	/**
	 * Write the file to the cloud
	 *
	 * @return boolean
	 * @author Justin Palmer
	 **/
	public function write($object, $file_name)
	{
		$size = (float) sprintf("%u", filesize($file_name));
		$fp = fopen($file_name, "r");
		return $object->write($fp, $size);
	}
	
	/**
	 * Read a document to a variable
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function read($object)
	{
		return $object->read();
	}
	
	/**
	 * Stream a document to the browser
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function stream($object, $disposition='attachment')
	{
		header("Content-Type: " . $object->content_type);
	    header('Content-transfer-encoding: binary');
	    header('Cache-Control: private');
	    header('Pragma: public');
	    header('Content-Disposition: ' . $disposition . '; filename="'. $object->name . '"');
	    $output = fopen("php://output", "w");
	    $object->stream($output);
	    fclose($output);
	}
}
