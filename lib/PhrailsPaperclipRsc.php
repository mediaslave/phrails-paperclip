<?php
include_once('vendor/autoload.php');

use OpenCloud\Rackspace;
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
		$this->cloud = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
																		'username' => $user,
																		'apiKey' 	   => $key));
	}
	
	/**
	 * Connect to the cloud and get the container back.
	 * 
	 * @param string $container
	 * @return resource - container resource
	 * @author Justin Palmer
	 */
	protected function connect($container){
		$this->cloud->authenticate();
		$service = $this->cloud->objectStoreService('cloudFiles');
		
		return $service->getContainer($container);

	}
	/**
	 * Write the file to the cloud
	 *
	 * @return boolean
	 * @author Justin Palmer
	 **/
	public function write($object, $file_name)
	{
		$container = $this->connect($this->container);
		
		if(is_file($object)){
			$object = fopen($object, 'r');
		}
		
		return $container->uploadObject($file_name, $object);
	}
	
	/**
	 * Read a document to a variable
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function read($object)
	{
		$container = $this->connect($this->container);
		return $container->getObject($object)->getContent();
	}
	
	/**
	 * Stream a document to the browser
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function stream($object, $send_mime_type)
	{
		$container = $this->connect($this->container);

		$object = $container->getObject($object);
		if($send_mime_type){
			header("Content-Type: " . $object->getContentType());
		}
	    header('Content-transfer-encoding: binary');
	    header('Cache-Control: private');
	    header('Pragma: public');
		header("Content-Length: " . $object->getContentLength());
	    $output = fopen("php://output", "w");
	    print $object->getContent();
	    fclose($output);
	}
}