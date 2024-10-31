<?php
namespace Plainware\PlainTracker;

class X_PresenterRecord_Timesheet
{
	public $self = __CLASS__;
	public $presenterTimesheet = PresenterTimesheet::class;

	public function textState( $ret, $stateId )
	{
		return $this->presenterTimesheet->textState( $stateId );
	}

	public function htmlState( $ret, array $x, $stateId, $textLabel = '' )
	{
		return $this->presenterTimesheet->htmlState( $x, $stateId, $textLabel );
	}
}