<?php
/**
* Interface for all of the cloud adapters
*/
abstract class AbstractPhrailsPaperclipCloud implements InterfacePhrailsPaperclip
{
	
	protected $cloud;
	
	/**
	 * Write the file to the cloud
	 *
	 * @return boolean
	 * @author Justin Palmer
	 **/
	public function write($object, $file_name){}
	
	/**
	 * Read a document to a variable
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function read($object){}
	
	/**
	 * Stream a document to the browser
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function stream($object, $disposition='attachment'){}
	
	/**
	 * Get the cloud
	 *
	 * @return mixed
	 * @author Justin Palmer
	 **/
	public function cloud()
	{
		return $this->cloud;
	}
	
	/**
	 * Create a connection to the cloud.
	 * 
	 * @return mixed
	 */
	abstract protected function connect($container);
}
