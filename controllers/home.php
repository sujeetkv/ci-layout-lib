<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller
{
	public function __construct(){
		parent::__construct();
		$this->load->library('layout');
	}
	
	public function index(){
		$data['name'] = 'Sujeet';
		
		$this->layout->add_meta('Content-Type', 'text/html; charset=utf-8', 'http-equiv');
		$this->layout->add_meta('keywords', 'Demo, Keywords');
		$this->layout->add_meta('description', 'this is demo description');
		
		$this->layout->add_css(base_url('assets/css/styles.css'), array('media'=>'all'));
		$this->layout->add_js('http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js');
		
		$this->layout->title('Home');
		$this->layout->view('home', $data);
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */
