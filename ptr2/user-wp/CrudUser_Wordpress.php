<?php
namespace Plainware\PlainTracker;

class CrudUser_Wordpress extends \Plainware\CrudWordpressUser
{
	public $self = __CLASS__;

	public static $fields = [
		'ID'				=> [ 'alias' => 'id' ],
		'display_name'	=> [ 'alias' => 'title' ],
		'user_email'	=> [ 'alias' => 'email' ],
	];
}