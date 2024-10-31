<?php
namespace Plainware;

class DbWordpress extends Db
{
	public $conf = [];

	public function getPrefix()
	{
		global $wpdb;

		if( is_multisite() ){
			// $shareDatabase = get_site_option( 'locatoraid_share_database', 0 );
			$shareDatabase = false;
			$ret = $shareDatabase ? $wpdb->base_prefix : $wpdb->prefix;
		}
		else {
			$ret = $wpdb->prefix;
		}

		$ret .= isset( $this->conf['prefix'] ) ? $this->conf['prefix'] : '';

		return $ret;
	}

	public function prepareQuery( $sql, array $arg )
	{
		if( false === strpos($sql, '%') ) return $sql;

		global $wpdb;
		return $wpdb->prepare( $sql, $arg );
	}

	public function doQuery( $sql )
	{
		global $wpdb;

		$ret = true;

		$isSelect = false;
		$isInsert = false;
		if( preg_match( '/^\s*insert\s/i', $sql ) ){
			$isInsert = true;
		}
		elseif( preg_match( '/^\s*(select|show)\s/i', $sql ) ){
			$isSelect = true;
		}

		if( $isSelect ){
			$ret = $wpdb->get_results( $sql, ARRAY_A );
		}
		else {
			$ret = $wpdb->query( $sql );
			if( $isInsert && (1 == $ret) ){
				$ret = $wpdb->insert_id;
			}
		}

		if( $wpdb->last_error ){
			// exit( $wpdb->last_error . '<br/>' . $sql );
			$ret = false;
			return $ret;
		}

		return $ret;
	}
}