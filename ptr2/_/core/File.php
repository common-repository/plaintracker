<?php
namespace Plainware;

class File
{
	public $self = __CLASS__;

	public static function countLines( $fullName )
	{
		$file = file( $fullName );
		$ret = count( $file );
		return $ret;
	}

	public static function findFile( $file, array $dirs )
	{
		$ret = null;

		reset( $dirs );
		foreach( $dirs as $d ){
			$f = $d . DIRECTORY_SEPARATOR . $file;
			if( file_exists($f) ){
				$ret = $f;
				break;
			}
		}

		return $ret;
	}

	public static function findFilesInDir( $dir, $includeSubdirs = true, $skipDirNames = true )
	{
		$ret = glob( $dir . DIRECTORY_SEPARATOR . '*', GLOB_NOSORT );
		$ret = array_combine( $ret, $ret );

		$subdirs = glob( $dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR|GLOB_NOSORT );

		foreach( $subdirs as $subdir ){
			if( $includeSubdirs ){
				$ret = array_merge( $ret, static::findFilesInDir($subdir, $includeSubdirs, $skipDirNames) );
			}
			if( $skipDirNames ){
				unset( $ret[$subdir] );
			}
		}

		$ret = array_values( $ret );
		return $ret;
	}

	public static function humanFilesize( $bytes, $decimals = 2 )
	{
		$sz = 'BKMGTP';
		$factor = floor( (strlen($bytes) - 1) / 3 );
		$ret = sprintf( "%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
		return $ret;
	}

	public static function makeZip( $fullName, $files, $wrapIn = '' )
	{
		if( strlen($wrapIn) ){
			$keys = array_keys( $files );
			foreach( $keys as $k ){
				$k2 = $wrapIn . '/' . $k;
				$files[ $k2 ] = $files[ $k ];
				unset( $files[$k] );
			}
		}

		$zip = new \ZipArchive();

		if( file_exists($fullName) ){
			$res = $zip->open( $fullName, \ZIPARCHIVE::OVERWRITE );
		}
		else {
			$res = $zip->open( $fullName, \ZIPARCHIVE::CREATE );
		}

		if( TRUE !== $res ){
			echo 'ERROR: ' . $fullName . '<br>';
			$errText = array(
				ZipArchive::ER_EXISTS	=> "File already exists.",
				ZipArchive::ER_INCONS	=> "Zip archive inconsistent.",
				ZipArchive::ER_INVAL		=> "Invalid argument.",
				ZipArchive::ER_MEMORY	=> "Malloc failure.",
				ZipArchive::ER_NOENT		=> "No such file.",
				ZipArchive::ER_NOZIP		=> "Not a zip archive.",
				ZipArchive::ER_OPEN		=> "Can't open file.",
				ZipArchive::ER_READ		=> "Read error.",
				ZipArchive::ER_SEEK		=> "Seek error.",
			);
			$errText = isset( $errText[$res] ) ? $errText[$res] : $res;
			echo 'ERROR: ' . $res . ': ' . $errText . '<br>';
			exit;
		}

		reset( $files );
		foreach( $files as $name => $fullName ){
		// echo "ADD '$fullName' AS '$name'<br>";
			$zip->addFile( $fullName, $name );
		}

		$zip->close();
	}

	public static function skip( array $ret, array $skip )
	{
		$fs = array_keys( $ret );
		foreach( $fs as $f ){
			$skipThis = false;
			reset( $skip );
			foreach( $skip as $s ){
				if( $s == substr($f, 0, strlen($s)) ){
					$skipThis = true;
					break;
				}
			}
			if( $skipThis ) unset( $ret[$f] );
		}
		return $ret;
	}

	public static function versionStringFromFile( $fileName )
	{
		$ret = '';
		$fileContents = file_get_contents( $fileName );
		if( preg_match('/version:[\s\t]+?([0-9.]+)/i', $fileContents, $v) ){
			$ret = $v[1];
		}
		return $ret;
	}

	public static function appNameFromFile( $fileName )
	{
		$ret = '';
		$fileContents = file_get_contents( $fileName );
		if( preg_match('/plugin name:[\s\t]+?(.+)/i', $fileContents, $v) ){
			$ret = $v[1];
		}
		elseif( preg_match('/application name:[\s\t]+?(.+)/i', $fileContents, $v) ){
			$ret = $v[1];
		}

		return $ret;
	}

	public static function versionNumFromString( $verString )
	{
		$ret = explode( '.', $verString );
		if( strlen($ret[2]) < 2 ) $ret[2] = '0' . $ret[2];
		$ret = join( '', $ret );
		$ret = (int) $ret;
		return $ret;
	}

	public function buildCsv( array $arrayOfArrays, $separator = ',' )
	{
		$header = [];

		reset( $arrayOfArrays );
		foreach( $arrayOfArrays as $a ){
			foreach( array_keys($a) as $k ){
				if( ! in_array($k, $header) ){
					$header[] = $k;
				}
			}
		}

		$ret = [];
		$ret[] = $this->self->buildCsvLine( $header, $separator );

		foreach( $arrayOfArrays as $a ){
			$a2 = [];
			reset( $header );
			foreach( $header as $k ){
				$a2[$k] = array_key_exists( $k, $a ) ? $a[$k] : null;
			}
			$ret[] = $this->self->buildCsvLine( $a2, $separator );
		}

		$ret = join( "\n", $ret );
		return $ret;
	}

	public function buildCsvLine( array $array, $separator = ',' )
	{
		$processed = [];

		reset( $array );
		foreach( $array as $a ){
			if( false !== strpos($a, '"') ){
				$a = str_replace( '"', '""', $a );
			}

			$wrap = false;

			if( false !== strpos($a, $separator) ){
				$wrap = true;
			}
			elseif( false !== strpos($a, "\n") ){
				$wrap = true;
			}

			if( $wrap ){
				$a = '"' . $a . '"';
			}

			$processed[] = $a;
		}

		$ret = join( $separator, $processed );
		return $ret;
	}
}