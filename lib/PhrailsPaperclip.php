<?php

/**
* 	$image = new PhrailsPaperclip('image', 'File, Rsc', 'user:null', 'key:null');
*	
*	$image->path('/assets/images/{id}/{file-name}-{style}.{extension}')
*	
*	$image->style('medium', '300X300>');
*	$image->style('thumb', '25X25#', 'png')
*	
*	$schema->rule('image_file_name', $image->content_type_rule('image/png', 'image/jpeg', 'image/jpg'));
*	
*	$this->image = $image;
*	
*	
*	//Stores the asset where it should go file or Rsc or ...
*	//Throws an exception if the file can not get saved.
*	//returns an array of items to be stored in the model.
*	$this->image->process();
*/
class PhrailsPaperclip
{
	const original = 'original';
	
	private $model, $column, $storage, $container=null, $styles=array(), $path, $hasPath=false;
	private $public=false;
	/**
	 * The storage object making 
	 */
	private $attachment;
	/**
	 * Store the url when we get it.
	 */
	private $url = null;
	
	private $files = array();
	
	private $valid_storage = array('File', 'Rsc');
	
	function __construct($model, $column, $storage='File')
	{	
		$key = array_pop(explode('\\', get_class($model)));
		$config = Registry::get('pr-plugin-phrails-paperclip');
		if($config === null || !(isset($config->$key))){
			throw new Exception('Phrails Paperclip relies on having a config/phrails-paperclip.ini file.  Defined correctly.');
		}
		//Set storage and container before we make sure that we have a 
		//valid storage type.
		$this->storage = $storage;
		//If we have what we need for a cloud service then feed it to the object
		if(isset($config->global, $config->$key)){
			$this->setAttachmentObject($config->$key, $config->global->user, $config->global->key);
			$this->container = $config->$key;
		//Else just pass null
		}else{
			//Make sure we have a valid storage area and create it.
			$this->setAttachmentObject();
		}
		//Initialize the vars
		$this->column = $column;
		$this->model = $model;
		$property = $column . '_file_name';
		$this->model->$property = $this->get('name');
		$this->model->filters()->afterSave(array($column, 'write'));
		$this->model->$column = $this;
		$this->styles = array();
	}
	
	/**
	 * Store the asset where it belongs.  
	 * 
	 * @throws Exception
	 * @return void
	 * @author Justin Palmer
	 **/
	public function write()
	{
		//If we don't have a file we will return.
		if(!$this->fileUploaded())
			return;
		
		if(empty($this->styles)){
			$path = $this->getPath();
			$this->files[] = array($this->get('tmp_name'), $path);
		}else{
			$this->convert();
		}
		foreach($this->files as $array){
			$this->attachment->write($array[0], $array[1]);
		}
	}
	
	/**
	 * Get the path to the file
	 * 
	 * @param string $style
	 * @return string
	 * @author Justin Palmer
	 **/
	public function getPath($style='')
	{
		$path = '';
		
		//test
		if($this->container !== null && !$this->hasPath())
			return $this->get('name');
		
		$model_column_name = $this->column . '_file_name';
		$style = ($style === '') ? self::original : $style;
		$files_name = $this->get('name');
		if($files_name === null){
			$files_name = $this->model->$model_column_name;
		}
		$pieces = explode('.', $files_name);
		$extension = '';
		if(sizeof($pieces) > 1)
			$extension = array_pop($pieces);
		$file_name_no_extension = implode('.', $pieces);
		if($this->hasPath()){
			$path = $this->path;
			$id = $this->model->primary_key();
			$path = str_replace('{id}', $this->model->$id, $path);
			$path = str_replace('{file-name}', $file_name_no_extension, $path);
			$path = str_replace('{style}', $style, $path);
			$path = str_replace('{extension}', $extension, $path);
			$path = str_replace('{time}', time(), $path);
		}else{
			$install_path = Registry::get('pr-install-path');
			$default_path = $install_path . '/public/images/paperclip/'; 
			$file_name = $file_name_no_extension . '-' . $style;
			if($extension != '')
			 	$file_name .= '.' . $extension;
			$path = $default_path . $file_name;
		}
		return $path;
	}
	
	/**
	 * convert the images according to the styles.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	private function convert()
	{
		foreach($this->styles as $style){
			$path = $name = $this->getPath($style->style);
			if($this->storage !== 'File'){
				$path = '/tmp/' . $name;
			}
			$command = "convert " . $this->get('tmp_name') . " -resize $style->size $style->command $path";
			$command = escapeshellcmd($command);
			exec($command);
			$this->files[] = array($path, $name);
		}
	}
	
	/**
	 * Get the url for the item.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function url($style='', $stream=false)
	{
		$model_column_name = $this->column . '_file_name';
		$file = $this->model->$model_column_name;
		if($file == '')
			return '';
		
		if($this->url !== null)
			return $this->url;
		
		//print 'url' . '<br/>';
		if($this->storage != 'File'){
			if($this->hasPath()){
				$file = $this->getPath($style);
			}
			$this->url = ($stream) ? $this->attachment->stream($file, 'inline')
								   : $this->attachment->read($file);
			return $this->url;
		}else{
			return $this->url = $this->getPath($style);
		}
	}
	
	/**
	 * Download the file.
	 *
	 * @return mixed
	 * @author Justin Palmer
	 **/
	public function download($disposition='attachment')
	{
		if($this->storage != 'File'){
			$model_column_name = $this->column . '_file_name';
			$file = $this->model->$model_column_name;
			if($this->hasPath()){
				$file = $this->getPath();
			}
			$this->attachment->stream($file, $disposition);
		}
	}
	
	/**
	 * Set the various styles for images
	 *
	 * @param string $name
	 * @param string $size Size will be passed to the -resize parameter of <code>convert</code>
	 * @param string $command Additional commands to be passed to <code>convert</code>
	 * @return void
	 * @author Justin Palmer
	 **/
	public function style($style, $size, $command='')
	{
		$o = new stdClass;
		$o->style = $style;
		$o->size = $size;
		$o->command = $command;
		$this->styles[] = $o;
	}
	
	/**
	 * Set the rule for the attachment
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function content_type_rule($types)	
	{
		$args = func_get_args();
		return new ContentTypeRule($this->get('type'), $args);
	}
	
	/**
	 * The path that the attachment should be saved to.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function path($path)
	{
		$this->hasPath = true;
		$this->path = $path;
	}
	
	/**
	 * Did the user set a path
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	private function hasPath()
	{
		return $this->hasPath;
	}
	
	/**
	 * Should the attachment be public or private.
	 * 
	 * This is ONLY taken into consideration when the storage is a cloud storage.	
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function isPublic($public=false)
	{
		$this->public = $public;
	}
	
	/**
	 * Get information from the files global.
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function get($name)
	{
		$files_name = $this->column . '_file_name';
		$table_name = Inflections::singularize($this->model->table_name());
		//Is it in the files global, return it.
		if(isset($_FILES[$table_name][$name][$files_name]))
			return $_FILES[$table_name][$name][$files_name];
		//is the model property not null, return it.
		if($this->model->$files_name !== null)
			return $this->model->$files_name;
		return null;
	}
	
	public function fileUploaded(){
		return ($this->get('error') == UPLOAD_ERR_OK) ? true : false;
	}
	
	/**
	 * Create the correct storage object.
	 *
	 * @return InterfacePhrailsPaperclip
	 * @author Justin Palmer
	 **/
	private function setAttachmentObject($container=null, $user=null, $key=null)
	{
		//Make sure it is a valid storage mechanism.
		if(!in_array($this->storage, $this->valid_storage))
			throw new Exception('Invalid PhrailsPaperclip Storage mechanism.');
		
		$storage = 'PhrailsPaperclip' . $this->storage;
		
		$this->attachment = new $storage($container, $user, $key);
	}
	
	
}