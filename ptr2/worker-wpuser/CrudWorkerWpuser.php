<?php
namespace Plainware\PlainTracker;

class CrudWorkerWpuser extends \Plainware\CrudWordpressUser
{
	public $self = __CLASS__;

	public static $fields = [
		'ID'				=> [ 'alias' => ['id', 'userId'] ],
		'display_name'	=> [ 'alias' => 'title' ],
		'user_email'	=> [ 'alias' => 'email' ],
		// 'state_id'	=> [ 'alias' => 'email' ],
	];
}