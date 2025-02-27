<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Profiler
{
	private $benchmark = NULL;
	protected $_available_sections = array(
		'benchmarks',
		'get',
		'memory_usage',
		'post',
		'uri_string',
		'controller_info',
		'queries',
		'http_headers',
		'session_data',
		'config'
		);

	protected $_query_toggle_count = 25;
	protected $dbs = array();

	public $_compile_benchmarks;
	public $_compile_get;
	public $_compile_memory_usage;
	public $_compile_post;
	public $_compile_uri_string;
	public $_compile_controller_info;
	public $_compile_queries;
	public $_compile_http_headers;
	public $_compile_session_data;
	public $_compile_config;

	public function add_db( $db )
	{
		$this->dbs[] = $db;
	}

	public function __construct()
	{
		$this->benchmark = new HC3_Profiler_Benchmark;

		// default all sections to display
		foreach ($this->_available_sections as $section){
			if ( ! isset($config[$section])){
				$this->{'_compile_' . $section} = TRUE;
			}
		}

		// $this->set_sections($config);
		
		// if wordpress
		if( defined('WPINC') ){
			global $wpdb;
			$this->add_db( $wpdb );
		}
	}

	function mark($name)
	{
		$this->benchmark->mark($name);
		return $this;
	}

	public function set_sections($config)
	{
		foreach ($config as $method => $enable){
			if (in_array($method, $this->_available_sections)){
				$this->{'_compile_' . $method} = ($enable !== FALSE) ? TRUE : FALSE;
			}
		}
	}

	protected function _compile_benchmarks()
	{
		$profile = array();

		foreach ( $this->benchmark->marker as $key => $val){
			// We match the "end" marker so that the list ends
			// up in the order that it was defined
			if (preg_match("/(.+?)_end/i", $key, $match)){
				if (isset($this->benchmark->marker[$match[1].'_end']) AND isset($this->benchmark->marker[$match[1].'_start'])){
					$profile[$match[1]] = $this->benchmark->elapsed_time($match[1].'_start', $key);
				}
			}
		}

		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_benchmarks" style="border:1px solid #900;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#900;">&nbsp;&nbsp;'.'profiler_benchmarks'.'&nbsp;&nbsp;</legend>';
		$output .= "\n";
		$output .= "\n\n<table style='width:100%'>\n";

		foreach ($profile as $key => $val){
			$key = ucwords(str_replace(array('_', '-'), ' ', $key));
			$output .= "<tr><td style='padding:5px;width:50%;color:#000;font-weight:bold;background-color:#ddd;'>".$key."&nbsp;&nbsp;</td><td style='padding:5px;width:50%;color:#900;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>\n";
		}

		$output .= "</table>\n";
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_queries()
	{
		$dbs = $this->dbs;

		if( (count($dbs) == 0) && defined('NTS_DEVELOPMENT3') ){
			global $wpdb;
			$dbs[] = $wpdb;
		}

		if (count($dbs) == 0){
			$output  = "\n\n";
			$output .= '<fieldset id="ci_profiler_queries" style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
			$output .= "\n";
			$output .= '<legend style="color:#0000FF;">&nbsp;&nbsp;'.'profiler_queries'.'&nbsp;&nbsp;</legend>';
			$output .= "\n";
			$output .= "\n\n<table style='border:none; width:100%;'>\n";
			$output .="<tr><td style='width:100%;color:#0000FF;font-weight:normal;background-color:#eee;padding:5px'>".'profiler_no_db'."</td></tr>\n";
			$output .= "</table>\n";
			$output .= "</fieldset>";

			return $output;
		}

		// Key words we want bolded
		$highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR&nbsp;', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');

		$output  = "\n\n";

		$count = 0;

		foreach ($dbs as $db){
			$queries = method_exists($db, 'queries') ? $db->queries() : $db->queries;
			if( ! $queries ) $queries = array();

			$count++;

			$hide_queries = (count($queries) > $this->_query_toggle_count) ? ' display:none' : '';

			$show_hide_js = '(<span style="cursor: pointer;" onclick="var s=document.getElementById(\'ci_profiler_queries_db_'.$count.'\').style;s.display=s.display==\'none\'?\'\':\'none\';this.innerHTML=this.innerHTML==\''.'profiler_section_hide'.'\'?\''.'profiler_section_show'.'\':\''.'profiler_section_hide'.'\';">'.'profiler_section_hide'.'</span>)';

			if ($hide_queries != ''){
				$show_hide_js = '(<span style="cursor: pointer;" onclick="var s=document.getElementById(\'ci_profiler_queries_db_'.$count.'\').style;s.display=s.display==\'none\'?\'\':\'none\';this.innerHTML=this.innerHTML==\''.'profiler_section_show'.'\'?\''.'profiler_section_hide'.'\':\''.'profiler_section_show'.'\';">'.'profiler_section_show'.'</span>)';
			}

			$output .= '<fieldset style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
			$output .= "\n";
			$output .= '<legend style="color:#0000FF;">&nbsp;&nbsp;'.'profiler_queries'.': '.count($queries).'&nbsp;&nbsp;'.$show_hide_js.'</legend>';
			$output .= "\n";
			$output .= "\n\n<table style='width:100%;{$hide_queries}' id='ci_profiler_queries_db_{$count}'>\n";

			if (count($queries) == 0){
				$output .= "<tr><td style='width:100%;color:#0000FF;font-weight:normal;background-color:#eee;padding:5px;'>".'profiler_no_queries'."</td></tr>\n";
			}
			else {
				foreach ($queries as $key => $val){
					list( $val, $time ) = $val;
					$time = number_format( $time, 4);
					$output .= "<tr><td style='padding:5px; vertical-align: top;width:1%;color:#900;font-weight:normal;background-color:#ddd;'>".$time."&nbsp;&nbsp;</td><td style='padding:5px; color:#000;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>\n";
				}
			}

			$output .= "</table>\n";
			$output .= "</fieldset>";

		}

		return $output;
	}

	protected function _compile_get()
	{
		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_get" style="border:1px solid #cd6e00;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#cd6e00;">&nbsp;&nbsp;'.'profiler_get_data'.'&nbsp;&nbsp;</legend>';
		$output .= "\n";

		if (count($_GET) == 0){
			$output .= "<div style='color:#cd6e00;font-weight:normal;padding:4px 0 4px 0'>".'profiler_no_get'."</div>";
		}
		else {
			$output .= "\n\n<table style='width:100%; border:none'>\n";

			foreach ($_GET as $key => $val){
				if ( ! is_numeric($key)){
					$key = "'".$key."'";
				}

				$output .= "<tr><td style='width:50%;color:#000;background-color:#ddd;padding:5px'>&#36;_GET[".$key."]&nbsp;&nbsp; </td><td style='width:50%;padding:5px;color:#cd6e00;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val)){
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
				}
				else {
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}

			$output .= "</table>\n";
		}
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_post()
	{
		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_post" style="border:1px solid #009900;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#009900;">&nbsp;&nbsp;'.'profiler_post_data'.'&nbsp;&nbsp;</legend>';
		$output .= "\n";

		if (count($_POST) == 0){
			$output .= "<div style='color:#009900;font-weight:normal;padding:4px 0 4px 0'>".'profiler_no_post'."</div>";
		}
		else {
			$output .= "\n\n<table style='width:100%'>\n";

			foreach ($_POST as $key => $val){
				if ( ! is_numeric($key)){
					$key = "'".$key."'";
				}

				$output .= "<tr><td style='width:50%;padding:5px;color:#000;background-color:#ddd;'>&#36;_POST[".$key."]&nbsp;&nbsp; </td><td style='width:50%;padding:5px;color:#009900;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val)){
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, TRUE))) . "</pre>";
				}
				else {
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}

			$output .= "</table>\n";
		}
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_uri_string()
	{
		return;
		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_uri_string" style="border:1px solid #000;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#000;">&nbsp;&nbsp;'.'profiler_uri_string'.'&nbsp;&nbsp;</legend>';
		$output .= "\n";

		if ($this->CI->uri->uri_string == ''){
			$output .= "<div style='color:#000;font-weight:normal;padding:4px 0 4px 0'>".'profiler_no_uri'."</div>";
		}
		else {
			$output .= "<div style='color:#000;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->uri->uri_string."</div>";
		}

		$output .= "</fieldset>";
		return $output;
	}

	protected function _compile_controller_info()
	{
		return;
		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_controller_info" style="border:1px solid #995300;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#995300;">&nbsp;&nbsp;'.'profiler_controller_info'.'&nbsp;&nbsp;</legend>';
		$output .= "\n";

		$output .= "<div style='color:#995300;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->router->fetch_class()."/".$this->CI->router->fetch_method()."</div>";

		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_memory_usage()
	{
		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_memory_usage" style="border:1px solid #5a0099;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#5a0099;">&nbsp;&nbsp;'.'profiler_memory_usage'.'&nbsp;&nbsp;</legend>';
		$output .= "\n";

		if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != ''){
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:4px 0 4px 0'>".number_format($usage).' bytes</div>';
		}
		else {
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:4px 0 4px 0'>".'profiler_no_memory'."</div>";
		}

		$output .= "</fieldset>";
		return $output;
	}

	protected function _compile_http_headers()
	{
		$output  = "\n\n";
		$output .= '<fieldset id="ci_profiler_http_headers" style="border:1px solid #000;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#000;">&nbsp;&nbsp;'.'profiler_headers'.'&nbsp;&nbsp;(<span style="cursor: pointer;" onclick="var s=document.getElementById(\'ci_profiler_httpheaders_table\').style;s.display=s.display==\'none\'?\'\':\'none\';this.innerHTML=this.innerHTML==\''.'profiler_section_show'.'\'?\''.'profiler_section_hide'.'\':\''.'profiler_section_show'.'\';">'.'profiler_section_show'.'</span>)</legend>';
		$output .= "\n";

		$output .= "\n\n<table style='width:100%;display:none' id='ci_profiler_httpheaders_table'>\n";

		foreach (array('HTTP_ACCEPT', 'HTTP_USER_AGENT', 'HTTP_CONNECTION', 'SERVER_PORT', 'SERVER_NAME', 'REMOTE_ADDR', 'SERVER_SOFTWARE', 'HTTP_ACCEPT_LANGUAGE', 'SCRIPT_NAME', 'REQUEST_METHOD',' HTTP_HOST', 'REMOTE_HOST', 'CONTENT_TYPE', 'SERVER_PROTOCOL', 'QUERY_STRING', 'HTTP_ACCEPT_ENCODING', 'HTTP_X_FORWARDED_FOR') as $header){
			$val = (isset($_SERVER[$header])) ? $_SERVER[$header] : '';
			$output .= "<tr><td style='vertical-align: top;width:50%;padding:5px;color:#900;background-color:#ddd;'>".$header."&nbsp;&nbsp;</td><td style='width:50%;padding:5px;color:#000;background-color:#ddd;'>".$val."</td></tr>\n";
		}

		$output .= "</table>\n";
		$output .= "</fieldset>";

		return $output;
	}

	protected function _compile_config()
	{
		return;
	}

	private function _compile_session_data()
	{
		return;
	}

	public function render( $content )
	{
		$out = $content . $this->run();
		return $out;
	}

	public function run()
	{
		$output = "<div class='hc-xs-hide' id='codeigniter_profiler' style='clear:both;background-color:#fff;padding:10px; margin-top: 5em;'>";
		$fields_displayed = 0;

		foreach ($this->_available_sections as $section){
			if ($this->{'_compile_' . $section} !== FALSE){
				$func = "_compile_{$section}";
				$output .= $this->{$func}();
				$fields_displayed++;
			}
		}

		if ($fields_displayed == 0){
			$output .= '<p style="border:1px solid #5a0099;padding:10px;margin:20px 0;background-color:#eee">'.'profiler_no_profiles'.'</p>';
		}

		$output .= '</div>';
		return $output;
	}
}

class HC3_Profiler_Benchmark
{
	var $marker = array();

	function mark($name)
	{
		$this->marker[$name] = microtime();
	}

	function elapsed_time($point1 = '', $point2 = '', $decimals = 4)
	{
		if ($point1 == ''){
			return '{elapsed_time}';
		}

		if ( ! isset($this->marker[$point1])){
			return '';
		}

		if ( ! isset($this->marker[$point2])){
			$this->marker[$point2] = microtime();
		}

		list($sm, $ss) = explode(' ', $this->marker[$point1]);
		list($em, $es) = explode(' ', $this->marker[$point2]);

		return number_format(($em + $es) - ($sm + $ss), $decimals);
	}

	function memory_usage()
	{
		return '{memory_usage}';
	}
}
