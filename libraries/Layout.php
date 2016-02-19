<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter Layout library -by Sujeet <sujeetkv90@gmail.com>
 * v 1.1
 */

class Layout
{
	protected $CI;
	
	protected $layout_title = NULL;
	protected $app_title = NULL;
	protected $_process_title = true;
	
	protected $element_dir = 'elements/';
	protected $layout_element_dir = '';
	
	protected $layout_dir = '_layouts/';
	protected $layout_view = 'default';
	
	protected $css_list = array(), $js_list = array();
	protected $css_attr = array('rel'=>'stylesheet','type'=>'text/css');
	protected $js_attr = array('type'=>'text/javascript');
	
	protected $block_list = array(), $block_name, $block_append = false, $block_replace = false;
	
	public function __construct(){
		$this->CI =& get_instance();
		
		// Grab layout from called controller
		if(isset($this->CI->layout_dir)) $this->layout_dir = $this->CI->layout_dir;
		if(isset($this->CI->layout_view)) $this->layout_view = $this->CI->layout_view;
		
		$this->_set_element_dir();
		
		log_message('debug', "Layout Class Initialized");
	}
	
	/**
	 * Load or Get rendered template view
	 * @param	string $title
	 * @param	array $data
	 * @param	bool $return
	 * @param	string $layout_view
	 * @param	string $layout_dir
	 */
	public function view($view, $data = NULL, $return = false, $layout_view = NULL, $layout_dir = NULL){
		if($this->_process_title) $this->_normalize_title();
		
		// Render resources
		$_data['title_for_layout'] = $this->layout_title;
		
		$_data['css_for_layout'] = '';
		foreach($this->css_list as $v)
			$_data['css_for_layout'] .= sprintf('<link%s href="%s" />', $v['attributes'], $v['resource']);
		
		$_data['js_for_layout'] = '';
		foreach($this->js_list as $v)
			$_data['js_for_layout'] .= sprintf('<script%s src="%s"></script>', $v['attributes'], $v['resource']);
		
		// Render template
		$layout_dir = $layout_dir ? rtrim($layout_dir, '/\\') . '/' : $this->layout_dir;
		
		$this->_set_element_dir($layout_dir); // dynamic element dir
		
		$_data['content_for_layout'] = $this->CI->load->view($view, $data, true);
		$this->block_replace = true;
		
		$_layout_view = $layout_dir . ($layout_view ? $layout_view : $this->layout_view);
		$output = $this->CI->load->view($_layout_view, $_data, $return);
		
		$this->_set_element_dir(); // reset element dir
		
		return $output;
	}
	
	/**
	 * Load or Get layout element view
	 * @param	string $name
	 */
	public function element($name, $data = NULL, $return = false, $layout_dir = NULL){
		$element_dir = $layout_dir ? rtrim($layout_dir, '/\\') . '/' . $this->element_dir : $this->layout_element_dir;
		$element = $this->CI->load->view($element_dir . $name, $data, $return);
		return $element;
	}
	
	/**
	 * Set page title
	 * @param	string $title
	 */
	public function title($title){
		$this->layout_title = $title;
	}
	
	/**
	 * Set application title
	 * @param	string $app_title
	 */
	public function set_app_title($app_title){
		$this->app_title = $app_title;
	}
	
	/**
	 * Enable or Disable processed title
	 * @param	bool $process_title
	 */
	public function process_title($process_title = true){
		$this->_process_title = (bool) $process_title;
	}
	
	/**
	 * Adds CSS resource to current page
	 * @param	string $resource_url
	 * @param	mixed $attributes
	 */
	public function add_css($resource_url, $attributes = ''){
		if(is_array($attributes)){
			$attributes = $this->_parse_attributes(array_merge($this->css_attr,$attributes));
		}else{
			$attributes = $this->_parse_attributes($this->css_attr) . $this->_parse_attributes($attributes);
		}
		$this->css_list[] = array('resource'=>$resource_url, 'attributes'=>$attributes);
	}
	
	/**
	 * Adds Javascript resource to current page
	 * @param	string $resource_url
	 * @param	mixed $attributes
	 */
	public function add_js($resource_url, $attributes = ''){
		if(is_array($attributes)){
			$attributes = $this->_parse_attributes(array_merge($this->js_attr,$attributes));
		}else{
			$attributes = $this->_parse_attributes($this->js_attr) . $this->_parse_attributes($attributes);
		}
		$this->js_list[] = array('resource'=>$resource_url, 'attributes'=>$attributes);
	}
	
	/**
	 * Set layout directory
	 * @param	string $layout_dir
	 */
	public function set_layout_dir($layout_dir){
		if(!empty($layout_dir)) $this->layout_dir = rtrim($layout_dir, '/\\') . '/';
		$this->_set_element_dir(); // reset element dir
	}
	
	/**
	 * Get layout directory
	 */
	public function get_layout_dir(){
		return $this->layout_dir;
	}
	
	/**
	 * Set layout view
	 * @param	string $layout_view
	 * @param	string $layout_dir
	 */
	public function set_layout($layout_view, $layout_dir = NULL){
		if(!empty($layout_view)) $this->layout_view = $layout_view;
		if(!empty($layout_dir)) $this->layout_dir = rtrim($layout_dir, '/\\') . '/';
	}
	
	/**
	 * Get layout view
	 */
	public function get_layout(){
		return $this->layout_view;
	}
	
	/**
	 * Assign block in template and Replace block in layout
	 * @param	string $name
	 * @param	bool $append
	 */
	public function block($name = '', $append = false){
		if($name != ''){
			$this->block_name = $name;
			$this->block_append = $append;
			ob_start();
		}else{
			$block_output = ob_get_clean();
			
			if($this->block_replace){
				// Replace overriden block in layout
				if(!empty($this->block_list[$this->block_name])){
					echo $this->block_list[$this->block_name]['append'] 
							? $block_output . $this->block_list[$this->block_name]['output'] 
							: $this->block_list[$this->block_name]['output'];
				}else{
					echo $block_output;
				}
			}else{
				// Override block in template
				$this->block_list[$this->block_name] = array(
					'output' => $block_output,
					'append' => (bool) $this->block_append
				);
			}
			
			$this->block_name = NULL;
			$this->block_append = false;
		}
	}
	
	/**
	 * Set block output
	 * @param	string $name
	 * @param	string $output
	 * @param	bool $append
	 */
	public function set_block($name, $output, $append = false){
		$this->block_list[$name] = array(
			'output' => $output,
			'append' => (bool) $append
		);
	}
	
	/**
	 * Get specified block output if assigned
	 * @param	string $name
	 * @param	bool $raw_data
	 */
	public function get_block($name, $raw_data = false){
		if(isset($this->block_list[$name])){
			return ($raw_data) ? $this->block_list[$name] : $this->block_list[$name]['output'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Set element dir
	 * @param	string $layout_dir
	 */
	protected function _set_element_dir($layout_dir = NULL){
		$this->layout_element_dir = ($layout_dir ? $layout_dir : $this->layout_dir) . $this->element_dir;
	}
	
	/**
	 * Helper function to set page title
	 */
	protected function _normalize_title(){
		$_title = '';
		
		if(! is_null($this->layout_title)){
			$_title .= $this->layout_title;
		}else{
			$_tmp = array();
			
			$_act = strtolower($this->CI->router->fetch_method());
			$_ctr = strtolower($this->CI->router->fetch_class());
			$_mod = method_exists($this->CI->router, 'fetch_module') /* detect modular extension */
					? strtolower(rtrim($this->CI->router->fetch_module(), '/')) 
					: strtolower(rtrim($this->CI->router->fetch_directory(), '/'));
			
			if($_act != 'index'){
				$_tmp[] = str_replace(array('_','-'), ' ', $_act);
			}
			
			if($_act == 'index' or !in_array($_ctr, array('index', 'home'))){
				$_tmp[] = str_replace(array('_','-'), ' ', $_ctr);
			}
			
			if(! empty($_mod) and !in_array($_mod, array($_act, $_ctr))){
				$_tmp[] = str_replace(array('_','-'), ' ', $_mod);
			}
			
			$_title .= ucwords(implode(' - ', $_tmp));
		}
		
		if(! empty($this->app_title)){
			$_title .= (empty($_title) ? '' : ' | ') . $this->app_title;
		}
		
		$this->layout_title = $_title;
	}
	
	/**
	 * Helper function to parse HTML element attributes
	 * @param	mixed $attributes
	 */
	protected function _parse_attributes($attributes){
		$att = '';
		if(!empty($attributes)){
			if(is_string($attributes)){
				$att .= ' '.$attributes;
			}elseif(is_array($attributes)){
				foreach($attributes as $key => $val) $att .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
			}
		}
		return $att;
	}
}

/* End of file Layout.php */