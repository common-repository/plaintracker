<?php
namespace Plainware;

class Html
{
	public static $inputId = 1;

	public static function getNextId()
	{
 		return 'pw-input-' . static::$inputId++;
	}

	public static function attr( array $attr )
	{
		$ret = [];

		foreach( $attr as $k => $v ){
			if( is_bool($v) ){
				if( $v ){
					$ret[] = $k;
				}
			}
			else {
				if( is_array($v) ){
					$v = join( ' ', $v );
				}
				$ret[] = $k . '="' . esc_attr( $v ). '"';
			}
		}

		$ret = join( ' ', $ret );
		return $ret;
	}

	public static function renderInputError( $err )
	{
		if( ! $err ) return;
?>
<div><strong><?php echo esc_html( $err ); ?></strong></div>
<?php
	}

	public static function inputValue( $name, $default )
	{
		$ret = $default;
		$formValues = static::$formValues;

		$pos = strpos( $name, '[' );
		if( false === $pos ){
			if( array_key_exists($name, $formValues) ){
				$ret = $formValues[ $name ];
			}
		}
		else {
			$shortName = substr( $name, 0, $pos );
			$index = substr( $name, $pos + 1, -1 );

			if( array_key_exists($shortName, $formValues) && array_key_exists($index, $formValues[$shortName]) ){
				$ret = $formValues[ $shortName ][ $index ];
			}
		}

		return $ret;
	}

	public static function renderInput( array $attr )
	{
		$name = $attr['name'];
		$value = array_key_exists( 'value', $attr ) ? $attr['value'] : null;

		if( ! isset($attr['type']) ){
			$attr['type'] = 'text';
		}

		if( ! isset($attr['id']) ){
			$attr['id'] = static::getNextId();
		}

		$htmlAttr = static::attr( $attr );
		$ret = '<input ' .  $htmlAttr . '>';

		return $ret;
	}

	public static function renderInputHidden( $name, $value, array $attr = [] )
	{
		$attr['type'] = 'hidden';
		$attr['name'] = $name;
		$attr['value'] = $value;
		return static::renderInput( $attr );
	}

	public static function downloadFile( $file, $shortFile = null )
	{
		if( ob_get_contents() ){
			ob_end_clean();
		}

		if( null === $shortFile ){
			$shortFile = basename( $file );
		}

		$fileSize = filesize( $file );

		header("Type: application/force-download");
		header("Content-Type: application/force-download");
		header("Content-Length: $fileSize");
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename=\"$shortFile\"");
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Connection: close");
		readfile( $file );
		exit;
	}

	public static function downloadData( $filename, $data )
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

		exit( $data );
	}

	public static function adjustColorBrightness( $hex, $steps )
	{
	// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max( -255, min(255, $steps) );

	// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if( strlen($hex) == 3 ){
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$colorParts = str_split( $hex, 2 );
		$ret = '#';

		foreach( $colorParts as $color ){
			$color = hexdec( $color ); // Convert to decimal
			$color = max( 0, min(255,$color + $steps) ); // Adjust color
			$ret .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $ret;
	}
}