<?php
namespace Plainware\PlainTracker;

class ModelAppInit
{
	public $self = __CLASS__;

	public $modelActivity = ModelActivity::class;
	public $modelProject = ModelProject::class;
	public $modelWorker = ModelWorker::class;

	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;
	public $modelApproverWorker = ModelApproverWorker::class;

	public $modelUser = ModelUser::class;
	public $auth = Auth::class;
	public $t = \Plainware\Time::class;

	public function migrate( array $ret )
	{
		$ret[ '99-init:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$ret = [];

	// activities
		$list = [ 'Consulting', 'Installation', 'Programming' ];
		$activityList = [];
		$id = 1;
		foreach( $list as $e ){
			$m = $this->modelActivity->construct();
			$m->id = $id++;
			$m->title = $e;
			$activityList[ $m->id ] = $m;
		}
		$this->modelActivity->createMany( $activityList );

	// projects
		$today = $this->t->getDate( $this->t->getNow() );

		$list = [ 'Project #1', 'Project #2' ];
		$projectList = [];
		$id = 1;
		foreach( $list as $e ){
			$m = $this->modelProject->construct();
			$m->id = $id++;
			$m->title = $e;
			$m->startDate = $this->t->getDate( $this->t->getStartMonth($today) );
			$m->endDate = $this->t->getDate( $this->t->getEndMonth( $this->t->modify($m->startDate, '+1 year') ) );
			$projectList[ $m->id ] = $m;
		}
		$this->modelProject->createMany( $projectList );

	// approvers
		$currentUserId = $this->auth->getCurrentUserId( [] );
		$listApproverWorker = [];

		$q = [];
		$listWorker = $this->modelWorker->find( $q );
		foreach( $listWorker as $worker ){
			$m = $this->modelApproverWorker->construct();
			$m->approverId = $currentUserId;
			$m->workerId = $worker->id;
			$listApproverWorker[] = $m;
		}
		$this->modelApproverWorker->createMany( $listApproverWorker );

	// workers
		// $currentUserId = $this->auth->getCurrentUserId( [] );
		// $currentUser = $this->modelUser->findById( $currentUserId );

		// $worker = $this->modelWorker->construct();
		// $worker->userId = $currentUserId;
		// $worker->title = $currentUser ? $currentUser->title : 'Worker';
		// $worker->email = $currentUser ? $currentUser->email : '';
		// $worker = $this->modelWorker->create( $worker );
		// $listWorker = [ $worker->id => $worker ];

	// activity/project/worker
		// $listActivityProjectWorker = [];
		// foreach( $activityList as $activity ){
			// foreach( $projectList as $project ){
				// foreach( $listWorker as $worker ){
					// $m = $this->modelActivityProjectWorker->construct();
					// $m->activityId = $activity->id;
					// $m->projectId = $project->id;
					// $m->workerId = $worker->id;
					// $listActivityProjectWorker[] = $m;
				// }
			// }
		// }
		// $this->modelActivityProjectWorker->createMany( $listActivityProjectWorker );

		return $ret;
	}

	public function down1()
	{
	}
}