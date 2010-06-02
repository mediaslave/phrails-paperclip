<?php
/**
* Interface for all of the cloud adapters
*/
interface PhrailsPaperclipCloud
{
	
	/**
	 * Write the file to the cloud
	 *
	 * @return boolean
	 * @author Justin Palmer
	 **/
	public function write($object, $file_name);
	
	/**
	 * Read a document to a variable
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function read($object);
	
	/**
	 * Stream a document to the browser
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function stream($object, $disposition='attachment');
}
