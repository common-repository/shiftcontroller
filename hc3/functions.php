<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Functions
{
	public static function glueArray( array $array )
	{
		$return = join( '_', $array );
		if( $return ){
			$return = '_' . $return . '_';
		}
		return $return;
	}

	public static function unglueArray( $string )
	{
		$return = array();

		$string = trim( $string );
		$string = trim( $string, '_' );
		if( strlen($string) ){
			$return = explode( '_', $string );
		}

		return $return;
	}

	public static function wpGetIdByShortcode( $shortcode )
	{
		global $wpdb;
		$ret = array();

		$pages = $wpdb->get_results( 
			"
			SELECT 
				ID, post_content
			FROM $wpdb->posts 
			WHERE 
				( post_type IN ('post', 'page') ) 
				AND 
				( post_content LIKE '%[" . $shortcode . "%]%' )
				AND 
				( post_status IN ('publish', 'private') )
			"
			);

		if( $pages ){
			foreach( $pages as $p ){
			// find shortcode
				$pos1 = strpos( $p->post_content, '[' . $shortcode );
				$pos2 = strpos( $p->post_content, ']', $pos1 );

				$shortcodeContent = substr( $p->post_content, $pos1, $pos2 - $pos1 + 1 );
				$shortcodeContent = substr( $p->post_content, $pos1, $pos2 - $pos1 ) . ' ]';
				$shortcodeAtts = shortcode_parse_atts( $shortcodeContent );

				unset( $shortcodeAtts[0] );
				unset( $shortcodeAtts[1] );

				// _print_r( $shortcodeAtts );
				// echo "$pos1 - $pos2\n$shortcodeContent\n$p->post_content\n\n";
				$ret[ $p->ID ] = $shortcodeAtts;
			}
		}

		return $ret;
	}

	public static function removeInvisibleCharacters( $str, $url_encoded = TRUE )
	{
		$non_displayables = array();
		
		// every control character except newline (dec 10)
		// carriage return (dec 13), and horizontal tab (dec 09)
		if ($url_encoded){
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}
		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do {
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}

	public static function buildCsv( $array, $separator = ',' )
	{
		$processed = array();
		reset( $array );
		foreach( $array as $a ){
			if( strpos($a, '"') !== FALSE ){
				$a = str_replace( '"', '""', $a );
			}

			$wrap = FALSE;

			if( strpos($a, $separator) !== FALSE ){
				$wrap = TRUE;
			}
			elseif( strpos($a, "\n") !== FALSE ){
				$wrap = TRUE;
			}

			if( $wrap ){
				$a = '"' . $a . '"';
			}

			$processed[] = $a;
		}

		$ret = join( $separator, $processed );
		return $ret;
	}

	public static function pushDownload( $filename, $data )
	{
	// Try to determine if the filename includes a file extension.
	// We need it in order to set the MIME type
		if (FALSE === strpos($filename, '.')){
			return FALSE;
		}

	// Grab the file extension
		$x = explode('.', $filename);
		$extension = end($x);

		// Load the mime types
		$mimes = array();

		// Set a default mime if we can't find it
		if ( ! isset($mimes[$extension])){
			$mime = 'application/octet-stream';
		}
		else {
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}

	// Generate the server headers
		if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE){
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header("Content-Length: ".strlen($data));
		}
		else {
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header("Content-Length: ".strlen($data));
		}

		exit($data);
	}

	public static function generateRand( $len = 12, $conf = array() )
	{
		$useLetters = isset($conf['letters']) ? $conf['letters'] : TRUE;
		$useHex = isset($conf['hex']) ? $conf['hex'] : FALSE;
		$useDigits = isset($conf['digits']) ? $conf['digits'] : TRUE;
		$useCaps = isset($conf['caps']) ? $conf['caps'] : FALSE;

		$salt = '';
		if( $useHex ){
			$salt .= 'abcdef';
		}
		if( $useLetters )
			$salt .= 'abcdefghijklmnopqrstuvxyz';
		if( $useDigits ){
			// $salt .= '0123456789';
			$salt .= '123456789';
		}
		if( $useCaps ){
			$salt .= 'ABCDEFGHIJKLMNOPQRSTUVXYZ';
		}

		// srand( (double) microtime() * 1000000 );
		$return = '';
		$i = 1;
		$array = array();
		while ( $i <= $len ){
			$num = rand() % strlen($salt);
			$tmp = substr($salt, $num, 1);
			$array[] = $tmp;
			$i++;
		}
		shuffle( $array );
		$return = join( '', $array );
		return $return;
	}

	static function adjustColorBrightness( $hex, $steps )
	{
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if( strlen($hex) == 3 ){
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach( $color_parts as $color ){
			$color = hexdec($color); // Convert to decimal
			$color = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}
		return $return;
	}

	static function arithmeticMean( $a )
	{
		$sum = 0;
		foreach( $a as $element ){
			$sum += $element;
		}
		$return = $sum / count($a);
		return $return;
	}

	static function geometricMean( $array )
	{
		if( ! count($array) ){
			return 0;
		}

		$chunkSize = 10;

		$total = count($array);
		$power = 1 / $total;

		$chunkProducts = array();
		$chunks = array_chunk( $array, $chunkSize );

		foreach( $chunks as $chunk ){
			$chunkProducts[] = pow( array_product($chunk), $power );
		}

		$result = array_product( $chunkProducts );
		return $result;
	}

	static function meanSquareError( $array1, $array2 )
	{
		$totals = array();

		$count = count( $array1 );
		for( $ii = 0; $ii < $count; $ii++ ){
			$thisOne = pow( ($array2[$ii] - $array1[$ii]), 2 );
			$totals[] = $thisOne;
		}

		$return = array_sum($totals) / count($totals);
		return $return;
	}

	static function removeFromArray( $array, $what, $replaceWith = NULL )
	{
		$return = $array;
		for( $ii = count($return) - 1; $ii >= 0; $ii-- ){
			if( ! array_key_exists($ii, $return) ){
				continue;
			}

			if( $return[$ii] == $what ){
				if( NULL === $replaceWith ){
					array_splice( $return, $ii, 1 );
				}
				else {
					array_splice( $return, $ii, 1, array($replaceWith) );
				}
			}
		}
		return $return;
	}

	static function tempCrudPrepareArgs( $args, $idField )
	{
		if( $args && (! is_array($args)) ){
			$args = array( $args );
		}

		if( isset($args['_PREPARED']) && $args['_PREPARED'] ){
			return $args;
		}

		$return = array(
			'_PREPARED'		=> TRUE,
			'LIMIT'			=> array(),
			'SORT'			=> array(),
			'WHERE'			=> array(),
			'SEARCH'		=> NULL,
			);

		$allowed_compares = array( '=', '<>', '>=', '<=', '>', '<', 'IN', 'NOTIN', 'LIKE', '&');
		$special = array( 'limit', 'sort', 'search');

		foreach( $args as $arg ){
			if( ! is_array($arg) ){
				$arg = array( $idField, '=', $arg );
			}

			$k = $arg[0];

			if( in_array($k, $special) ){
				array_shift( $arg );

				switch( $k ){
					case 'search':
						$v = array_shift( $arg );
						$return['SEARCH'] = $v;
						break;

					case 'limit':
						$v = array_shift( $arg );
						$offset = array_shift( $arg );
						$return['LIMIT'] = array( $v, $offset );
						break;

					case 'sort':
						if( is_array($return['SORT']) ){
							$sort_by = array_shift( $arg );
							$sort_how = strtolower( $arg ? array_shift( $arg ) : 'asc' );
							if( ! in_array($sort_how, array('asc', 'desc')) ){
								echo "SORTING '$sort_how' IS NOT ALLOWED, ONLY ASC OR DESC!<br>";
								$sort_how = 'asc';
							}
							$return['SORT'][] = array( $sort_by, $sort_how );
						}
						break;
				}

				continue;
			}

		// WHERE
			if( count($arg) < 3 ){
				echo "FOR WHERE ARGUMENTS REQUIRE 3 PARAMS: '$k'";
				_print_r( $args );
				_print_r( $arg );
				exit;
			}

			list( $k, $compare, $v ) = $arg;
			$compare = strtoupper( $compare );
			if( ! in_array($compare, $allowed_compares) ){
				echo "COMPARING BY '$compare' IS NOT ALLOWED!<br>";
				exit;
			}

			if( ($k == $idField) && ($idField == 'id') && ( ! is_array($v) ) ){
				$v = (int) $v;
			}

			if( in_array($compare, array('IN', 'NOTIN')) && (! is_array($v)) ){
				$v = strlen($v) ? $v : 0;
				$v = array($v);
			}
			if( $v == 'null' ){
				$v = NULL;
			}
			if( ($k == $idField) && ($compare == '=') ){
				$return['LIMIT'] = array(1, 0);
				$return['SORT'] = NULL;
			}

			$return['WHERE'][] = array( $k, $compare, $v );
		}

		return $return;
	}

	public static function listFiles( $dir, $type = 'file', $extension = '' )
	{
		if( ! is_array($dir) )
			$dir = array( $dir );

		$return = array();
		foreach( $dir as $this_dir ){
			if ( file_exists($this_dir) && ($handle = opendir($this_dir)) ){
				while ( false !== ($f = readdir($handle)) ){
					if( substr($f, 0, 1) == '.' )
						continue;

					if( 'file' == $type ){
						if( is_file( $this_dir . '/' . $f ) ){
							if( (! $extension ) || ( substr($f, - strlen($extension)) == $extension ) ){
								$return[] = $f;
							}
						}
					}
					else {
						if( is_dir( $this_dir . '/' . $f ) ){
							if( (! $extension ) || ( substr($f, - strlen($extension)) == $extension ) ){
								$return[] = $f;
							}
						}
					}
				}
				closedir($handle);
			}
		}

		sort( $return );
		return $return;
	}

	public static function makeCombos( $array )
	{
		$return = array();

		while( $thisOnes = array_shift($array) ){
			$thisCombos = array();
			foreach( $thisOnes as $r ){
				if( $return ){
					reset( $return );
					foreach( $return as $combo ){
						$combo[] = $r;
						$thisCombos[] = $combo;
					}
				}
				else {
					$thisCombos[] = array( $r );
				}
			}
			if( $thisCombos ){
				$return = $thisCombos;
			}
		}

		return $return;
	}
}