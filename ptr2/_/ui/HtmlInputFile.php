<?php
namespace Plainware;

class HtmlInputFile
{
	public $form = HtmlForm::class;

	public function render( $name, $value = '', array $attr = [] )
	{
		if( ! isset($attr['type']) ) $attr['type'] = 'file';
		$attr['name'] = $name;

		$submittedValue = $this->form->getValue( $name );
		if( null !== $submittedValue ) $value = $submittedValue;
		$attr['value'] = (string) $value;

		if( isset($attr['size']) ){
			$attr['style'] = 'width: ' . $attr['size'] . 'em;';
		}

		$error = $this->form->getError( $name );
		$ret = Html::renderInput( $attr );

		if( strlen($error) ){
			$ret = '<strong>' . $ret . '<div>' . $error . '</div>' . '</strong>';
		}

		return $ret;
	}

	public function grab( $name, array $post )
	{
		$ret = null;

		if( isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']) ){
			$ret = $_FILES[$name];
		}

		return $ret;

		$tmp_name = $this_form_values['userfile']['tmp_name'];

		$parse_error = FALSE;

	// to handle mac created files
		ini_set( 'auto_detect_line_endings', TRUE );

		$handle = fopen($tmp_name, "r");
		if( $handle === FALSE ){
			$errors = array(
				'userfile'	=> __('Can not open the uploaded file', 'locatoraid')
				);

			$session = $this->app->make('/session/lib');
			$session
				->set_flashdata('error', $errors)
				;
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}

		$line_no = 0;
		setlocale( LC_ALL, "en_US.UTF-8" );

	// first line
		$line = fgetcsv($handle, 10000, $separator);

		if( ! $line ){
			$errors = array(
				'userfile'	=> __('Empty file', 'locatoraid')
				);

			$session = $this->app->make('/session/lib');
			$session
				->set_flashdata('error', $errors)
				;
			return $this->app->make('/http/view/response')
				->set_redirect('-referrer-') 
				;
		}
	}
}