<?php
namespace Plainware;

class EmailWordpress
{
	public $self = __CLASS__;

	protected $emailHtml = 1;
	protected $emailFrom;
	protected $emailFromName;

	public function send( _Email $email )
	{
		$to = $email->to;
		$subj = $email->subject;
		$msg = $email->body;
		$headers = $email->headers;

		if( $this->emailHtml ){
			$msg = nl2br( $msg );
		}
		else {
			$msg = strip_tags( $msg );
		}

		add_filter( 'wp_mail_content_type', [$this, 'setHtmlMailContentType'] );
		add_filter( 'wp_mail_charset', [$this, 'setCharset'] ); 

		$headers = array();
		if( strlen($this->emailFrom) ){
			$headers[] = 'From: ' . $this->emailFromName . ' <' . $this->emailFrom . '>';
		}

		@wp_mail( $to, $subj, $msg, $headers );

		remove_filter( 'wp_mail_content_type', [$this, 'setHtmlMailContentType'] );
		remove_filter( 'wp_mail_charset', [$this, 'setCharset'] );
	}

	public function setHtmlMailContentType()
	{
		$ret = $this->emailHtml ? 'text/html' : 'text/plain';
		return $ret;
	}

	public function setCharset( $charset )
	{
		$ret = 'utf-8';
		return $ret;
	}
}