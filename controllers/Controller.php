<?php
// Shared Controller

class Controller
{
		
	protected function inputs()
	{
		switch(self::get_request_method()){

			case "POST":
				return self::cleanInputs($_POST);
			break;

			case "GET":
			case "DELETE":
				return self::cleanInputs($_GET);
			break;

			case "PUT":
				parse_str(file_get_contents("php://input"),self::$_request);
				return self::cleanInputs($this->_request);
			break;

			default: self::response('', 406); break;
		}
	}	

	protected function get_request_method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}			
	
	protected function cleanInputs($data)
	{
		$clean_input = array();
		if(is_array($data)) {
			foreach($data as $k => $v){
				$clean_input[$k] = self::cleanInputs($v);
			}
		} else {
			if(get_magic_quotes_gpc()){
				$data = trim(stripslashes($data));
			}
			$data = strip_tags($data);
			$clean_input = trim($data);
		}
		return $clean_input;
	}

	protected function get_referer()
	{
		return $_SERVER['HTTP_REFERER'];
	}

	protected function response($data, $status)
	{
		self::$_code = ($status) ? $status : 200;
		self::set_headers();
		echo $data;
		exit;
	}

	protected function set_headers()
	{
		header("HTTP/1.1 ".self::$_code." ".self::get_status_message());
		header("Content-Type:".self::$_content_type);
	}

} // Ends Shared Controller