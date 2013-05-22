<?php
/**
* Interface for all of the cloud adapters
* 
* @todo createPath method suppresses errors when using mkdir.
*/
class PhrailsPaperclipFile implements InterfacePhrailsPaperclip
{
	/**
	 * Write the file to the filesystem
	 *
	 * @return boolean
	 * @author Justin Palmer
	 **/
	public function write($object, $file_name, $container=null)
	{
		$file_name = $this->createPath($file_name);
		return move_uploaded_file($object, $file_name);
	}
	
	/**
	 * Create the path to where the file needs to be uploaded.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function createPath($path)
	{
		clearstatcache();
		$pieces = explode('/', $path);
		//Pop off the last item in the array.  We don't care about the file.
		$file_name = array_pop($pieces);
		//Shift off the first element.  Will always be blank.
		if($pieces[0] == '')
			array_shift($pieces);
		$path = getcwd();
		foreach($pieces as $folder){
			$path .= '/' . $folder;
			$mask = umask(002);
			if(!file_exists($folder)){
				//This is a hack to make mkdir not comlain.
				@mkdir($path, 0777);
			}	
			umask($mask);
		}
		return $path . '/' . $file_name;
	}
	
	/**
	 * Read a document to a variable
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function read($object)
	{
		
	}
	
	/**
	 * Stream a document to the browser
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function stream($object, $send_mime_type)
	{
	    if ($send_mime_type) {
			header("Content-Type: " . $object->content_type);
	    }
	    header('Content-transfer-encoding: binary');
	    header('Cache-Control: private');
	    header('Pragma: public');
	    $output = fopen("php://output", "w");
	    $object->stream($output);
	    fclose($output);
	}
}
