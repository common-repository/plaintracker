<?php
namespace Plainware;

class X_Email_EmailWordpress
{
	public $emailWordpress = EmailWordpress::class;

	public function send( _Email $email )
	{
		return $this->emailWordpress->send( $email );
	}
}