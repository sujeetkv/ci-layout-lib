<?php  defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodeIgniter Layout library
 * v 1.4
 * @author Sujeet <sujeetkv90@gmail.com>
 * @link https://github.com/sujeet-kumar/ci-layout-lib
 */

class Layout
{
	const LF = "\n";
	
	protected $CI;
	
	protected $layout_title = NULL;
	protected $layout_main_title = NULL;
	protected $_process_title = true;
	
	protected $element_base = '_elements/';
	
	protected $layout_base = '_layouts/';
	protected $layout_dir = '';
	protected $layout_view = 'default';
	
	protected $css_list = array(), $js_list = array(), $meta_list = array();
	protected $css_attr = array('rel'=>'stylesheet','type'=>'text/css');
	protected $js_attr = array('type'=>'text/javascript');
	
	protected $block_list = array(), $block_name, $block_replace = false, $_block_override = false;
	
	public function __construct(){
		$this->CI =& get_instance();
		
		// Grab layout from called controller
		if(isset($this->CI->layout_dir)) $this->layout_dir = $this->CI->layout_dir;
		if(isset($this->CI->layout_view)) $this->layout_view = $this->CI->layout_view;
		
		log_message('debug', "Layout Class Initialized");
	}
	
	/**
	 * Load or Get rendered view
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
		
		$_data['meta_for_layout'] = '';
		foreach($this->meta_list as $m)
			$_data['meta_for_layout'] .= sprintf('<meta%s />' . self::LF, $m);
		
		$_data['css_for_layout'] = '';
		foreach($this->css_list as $s)
			$_data['css_for_layout'] .= sprintf('<link%s href="%s" />' . self::LF, $s['attributes'], $s['resource']);
		
		$_data['js_for_layout'] = '';
		foreach($this->js_list as $j)
			$_data['js_for_layout'] .= sprintf('<script%s src="%s"></script>' . self::LF, $j['attributes'], $j['resource']);
		
		// Render template
		$_data['content_for_layout'] = $this->CI->load->view($view, $data, true);
		$this->_block_override = true;
		
		$layout_dir = $layout_dir ? rtrim($layout_dir, '/\\') . '/' : $this->layout_dir;
		$_layout_view = $this->layout_base . $layout_dir . ($layout_view ? $layout_view : $this->layout_view);
		
		$output = $this->CI->load->view($_layout_view, $_data, $return);
		
		return $output;
	}
	
	/**
	 * Load or Get element view
	 * @param	string $view
	 * @param	array $data
	 * @param	bool $return
	 */
	public function element($view, $data = NULL, $return = false){
		$element = $this->CI->load->view($this->element_base . $view, $data, $return);
		return $element;
	}
	
	/**
	 * Set page title
	 * @param	string $layout_title
	 */
	public function title($layout_title){
		$this->layout_title = $layout_title;
	}
	
	/**
	 * Set main title
	 * @param	string $layout_main_title
	 */
	public function main_title($layout_main_title){
		$this->layout_main_title = $layout_main_title;
	}
	
	/**
	 * Enable or Disable processed title
	 * @param	bool $process_title
	 */
	public function process_title($process_title = true){
		$this->_process_title = (bool) $process_title;
	}
	
	/**
	 * Adds meta tag to current page
	 * @param	string $name
	 * @param	string $content
	 * @param	string $type
	 * @param	bool $overwrite
	 */
	public function add_meta($name, $content, $type = 'name', $overwrite = true){
		$meta_attributes = $this->_parse_attributes(array($type => $name, 'content' => $content));
		if(! $overwrite) $this->meta_list[] = $meta_attributes;
		else $this->meta_list[strtolower($name)] = $meta_attributes;
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
		empty($layout_dir) or $this->layout_dir = rtrim($layout_dir, '/\\') . '/';
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
		empty($layout_view) or $this->layout_view = $layout_view;
		empty($layout_dir) or $this->layout_dir = rtrim($layout_dir, '/\\') . '/';
	}
	
	/**
	 * Get layout view
	 */
	public function get_layout(){
		return $this->layout_view;
	}
	
	/**
	 * Assign block in view and Replace block in layout
	 * @param	string $name
	 * @param	bool $replace
	 */
	public function block($name = '', $replace = false){
		if($name != ''){
			$this->block_name = $name;
			$this->block_replace = $replace;
			ob_start();
		}else{
			$block_output = ob_get_clean();
			
			if($this->_block_override){
				// Replace overriden block in layout
				if(! empty($this->block_list[$this->block_name])){
					echo $this->block_list[$this->block_name]['replace'] 
							? $this->block_list[$this->block_name]['output'] 
							: $block_output . $this->block_list[$this->block_name]['output'];
				}else{
					echo $block_output;
				}
			}else{
				// Override block in template
				if(! empty($this->block_list[$this->block_name])){
					$this->block_list[$this->block_name]['output'] = $this->block_list[$this->block_name]['replace'] 
							? $this->block_list[$this->block_name]['output'] 
							: $block_output . $this->block_list[$this->block_name]['output'];
					$this->block_list[$this->block_name]['replace'] = (bool) $this->block_replace;
				}else{
					$this->block_list[$this->block_name] = array(
						'output' => $block_output,
						'replace' => (bool) $this->block_replace
					);
				}
			}
			$this->block_name = NULL;
			$this->block_replace = false;
		}
	}
	
	/**
	 * Set block output
	 * @param	string $name
	 * @param	string $output
	 * @param	bool $replace
	 */
	public function set_block($name, $output, $replace = false){
		$this->block_list[$name] = array(
			'output' => $output,
			'replace' => (bool) $replace
		);
	}
	
	/**
	 * Get block output
	 * @param	string $name
	 * @param	bool $get_array
	 */
	public function get_block($name, $get_array = false){
		if(isset($this->block_list[$name])){
			return ($get_array) ? $this->block_list[$name] : $this->block_list[$name]['output'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Helper function to process page title
	 */
	protected function _normalize_title(){
		$_title = '';
		
		if(! is_null($this->layout_title)){
			$_title .= $this->layout_title;
		}else{
			$_tmp = array();
			
			$_act = strtolower($this->CI->router->method);
			$_ctr = strtolower($this->CI->router->class);
			$_mod = property_exists($this->CI->router, 'module') /* detect modular extension */
					? strtolower(rtrim($this->CI->router->module, '/')) 
					: strtolower(rtrim($this->CI->router->directory, '/'));
			
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
		
		if(! empty($this->layout_main_title)){
			$_title .= (empty($_title) ? '' : ' | ') . $this->layout_main_title;
		}
		
		$this->layout_title = $_title;
	}
	
	/**
	 * Helper function to parse HTML element attributes
	 * @param	mixed $attributes
	 */
	protected function _parse_attributes($attributes){
		$att = '';
		if(! empty($attributes)){
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