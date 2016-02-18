<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter Layout library -by Sujeet <sujeetkv90@gmail.com>
 * v 1.0
 */

class Layout
{
	private $CI;
	private $layout_title = '';
	private $layout_view = '_layouts/default';
	private $css_list = array(), $js_list = array();
	private $css_attr = array('rel'=>'stylesheet','type'=>'text/css');
	private $js_attr = array('type'=>'text/javascript');
	private $block_list = array(), $block_name, $block_replace = false;
	
	public function __construct(){
		$this->CI =& get_instance();
		// Grab layout from called controller
		if(isset($this->CI->layout_view)) $this->layout_view = $this->CI->layout_view;
		
		log_message('debug', "Layout Class Initialized");
	}
	
	/**
	 * Load or Get rendered template view
	 * @param	string $title
	 * @param	array $data
	 * @param	bool $return
	 */
	public function view($view, $data = NULL, $return = false, $layout_view = NULL){
		// Render resources
		$_data['title_for_layout'] = $this->layout_title;
		
		$_data['css_for_layout'] = '';
		foreach($this->css_list as $v)
			$_data['css_for_layout'] .= sprintf('<link%s href="%s" />', $v['attributes'], $v['resource']);
		
		$_data['js_for_layout'] = '';
		foreach($this->js_list as $v)
			$_data['js_for_layout'] .= sprintf('<script%s src="%s"></script>', $v['attributes'], $v['resource']);
		
		// Render template
		$_data['content_for_layout'] = $this->CI->load->view($view, $data, true);
		$this->block_replace = true;
		$_layout_view = ($layout_view) ? $layout_view : $this->layout_view;
		$output = $this->CI->load->view($_layout_view, $_data, $return);
		
		return $output;
	}
	
	/**
	 * Set page title
	 * @param	string $title
	 */
	public function title($title){
		$this->layout_title = $title;
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
	 * Set layout view
	 * @param	string $layout_view
	 */
	public function set_layout($layout_view){
		if(!empty($layout_view)) $this->layout_view = $layout_view;
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
	 */
	public function block($name = ''){
		if($name != ''){
			$this->block_name = $name;
			ob_start();
		}else{
			$block_output = ob_get_clean();
			
			if($this->block_replace){
				// Replace overriden block in layout
				if(!empty($this->block_list[$this->block_name])){
					echo $this->block_list[$this->block_name];
				}else{
					echo $block_output;
				}
			}else{
				// Override block in template
				$this->block_list[$this->block_name] = $block_output;
			}
		}
	}
	
	/**
	 * Get specified block if assigned
	 * @param	string $name
	 */
	public function get_block($name){
		return (!empty($this->block_list[$name])) ? $this->block_list[$name] : '';
	}
	
	/**
	 * Set block output
	 * @param	string $name
	 * @param	string $output
	 */
	public function set_block($name, $output){
		$this->block_list[$name] = $output;
	}
	
	/**
	 * Helper function to parse HTML element attributes
	 * @param	mixed $attributes
	 */
	private function _parse_attributes($attributes){
		$att = '';
		if(!empty($attributes)){
			if(is_string($attributes)){
				$att .= ' '.$attributes;
			}elseif(is_array($attributes)){
				foreach($attributes as $key => $val) $att .= ' ' . $key . '="' . $val . '"';
			}
		}
		return $att;
	}
}

/* End of file Layout.php */