<?php
namespace Plainware;

class Setting
{
	public $self = __CLASS__;
	public $crud = CrudSetting::class;
	protected $data = null;

	public function getDefaults()
	{
		$ret = [];
		return $ret;
	}

	public function get( $name, $if = null )
	{
		$this->self->load();

		$fullName = ( null === $if ) ? $name : $name . ':' . $if;

		$ret = null;
		if( array_key_exists($fullName, $this->data) ){
			return $this->data[ $fullName ];
		}

		if( ! array_key_exists($name, $this->data) ){
			echo "SETTING '" . esc_html($name) . "' IS NOT REGISTERED<br>";
			return $ret;
		}

		$ret = $this->data[ $name ];
		return $ret;
	}

	public function set( $name, $value, $if = null )
	{
		$currentValue = $this->self->get( $name, $if );
		if( $currentValue == $value ) return;

		$fullName = ( null === $if ) ? $name : $name . ':' . $if;

		$q = [];
		$q[] = [ 'settingName', '=', $fullName ];

		$res = $this->crud->read( $q );
		if( $res ){
			$res = current( $res );
			$this->crud->update( ['settingValue' => $value], ['id' => $res['id']] );
		}
		else {
			$this->crud->create( ['settingName' => $fullName, 'settingValue' => $value] );
		}

		return $value;
	}

	public function reset( $name, $if = null )
	{
		$fullName = ( null === $if ) ? $name : $name . ':' . $if;

		$q = [];
		$q[] = [ 'settingName', '=', $fullName ];

		$this->crud->delete( $q );
		return $name;
	}

	public function load()
	{
		if( null !== $this->data ){
			return;
		}

		$this->data = $this->self->getDefaults();

		$q = [];
		// $q[] = [ 'order', 'id', 'DESC' ];
		$res = $this->crud->read( $q );

		foreach( $res as $e ){
			$name = $e['settingName'];
			$value = $e['settingValue'];
			$this->data[ $name ] = $value;
		}
	}
}