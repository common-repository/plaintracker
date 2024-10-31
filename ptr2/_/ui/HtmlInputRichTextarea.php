<?php
namespace Plainware;

class HtmlInputRichTextarea
{
	public $inputTextarea = HtmlInputTextarea::class;

	public function render( $name, $value = '', array $attr = [] )
	{
		return $this->inputTextarea->render( $name, $value, $attr );
	}
}