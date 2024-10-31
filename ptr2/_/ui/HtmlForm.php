<?php
namespace Plainware;

class HtmlForm
{
	protected $values = [];
	protected $errors = [];

	public function getValue( $name )
	{
		return isset( $this->values[$name] ) ? $this->values[$name] : null;
	}

	public function setValue( $name, $value )
	{
		$this->values[ $name ] = $value;
	}

	public function getError( $name )
	{
		return isset( $this->errors[$name] ) ? $this->errors[$name] : '';
	}

	public function setError( $name, $error )
	{
		$this->errors[ $name ] = $error;
	}
}