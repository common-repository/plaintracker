<?php
namespace Plainware\PlainTracker;

class ModelWorker_User
{
	public $self = __CLASS__;
	public $modelUser = ModelUser::class;

// if connected to user then use title
	public function find( array $ret )
	{
		if( ! $ret ) return $ret;

		$listUserId = [];
		foreach( $ret as $m ){
			if( ! $m->userId ) continue;
			$listUserId[ $m->userId ] = $m->userId;
		}
		if( ! $listUserId ) return $ret;

		$q2 = [];
		$q2[] = [ 'id', '=', $listUserId ];
		$dictUser = $this->modelUser->find( $q2 );

		$toUpdate = [];
		foreach( $ret as $m ){
			if( ! $m->userId ) continue;
			$user = $dictUser[ $m->userId ] ?? null;
			if( ! $user ) continue;

			$m->title = $user->title;
			$m->email = $user->email;
		}

		return $ret;
	}
}