<?php

/**
* 	$image = new PhrailsPaperclip('image', 'File, Rsc', 'user:null', 'key:null');
*	
*	$image->path('/assets/images/{id}/{file-name}-{style}.{extension}')
*	
*	$image->style('medium', '300X300>');
*	$image->style('thumb', '25X25#', 'png')
*	
*	$schema->rule('image_file_name', $image->types('image/png', 'image/jpeg', 'image/jpg', '%s should be an image of type:  jpg, jpeg or png.'));
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
	
	private $model, $column, $storage, $container, $styles, $path, $hasPath=false;
	private $public=false;
	
	private $valid_storage = array('File', 'Rsc');
	
	function __construct($model, $column, $storage='File', $container=null)
	{
		//Make sure it is a valid storage mechanism.
		if(!in_array($storage, $this->valid_storage))
			throw new Exception('Invalid PhrailsPaperclip Storage mechanism.');
		//Initialize the vars
		$this->column = $column;
		$this->model = $model;
		$property = $column . '_file_name';
		$this->model->$property = $this->get('name');
		$this->model->filters()->afterSave(array($column, 'write'));
		$this->model->$column = $this;
		$this->storage = $storage;
		$this->container = $container;
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
		
		$storage = 'PhrailsPaperclip' . $this->storage;
		
		$path = $this->getPath();
		
		$attachment = new $storage();
		return $attachment->write($this->get('tmp_name'), $path);
	}
	
	/**
	 * Get the path to the file
	 *
	 * @return string
	 * @author Justin Palmer
	 **/
	public function getPath()
	{
		$path = '';
		$model_column_name = $this->column . '_file_name';
		$style = self::original;
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
	 * Get the url for the item.
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function url($style=self::original)
	{
		print 'url' . '<br/>';
		return $this->getPath();
	}
	
	/**
	 * Set the various styles for images
	 *
	 * @return void
	 * @author Justin Palmer
	 **/
	public function style($name, $gd, $type=null)
	{
		$o = new stdClass;
		$o->name = $name;
		$o->gd = $gd;
		$o->type = $type;
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
	public function hasPath()
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
		//Is it in the files global, return it.
		if(isset($_FILES[Inflections::singularize($this->model->table_name())][$name][$files_name]))
			return $_FILES[Inflections::singularize($this->model->table_name())][$name][$files_name];
		//is the model property not null, return it.
		if($this->model->$files_name !== null)
			return $this->model->$files_name;
		return null;
	}
	
	
}
