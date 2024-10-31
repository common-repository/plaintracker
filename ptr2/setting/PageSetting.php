<?php
namespace Plainware\PlainTracker;

class PageSetting
{
	public $self = __CLASS__;

	public function can( array $x )
	{
		$ret = false;

	// if any child is allowed then ok
		$nav = $this->self->nav( $x );
		if( ! $nav ){
			return $ret;
		}
		$ret = true;
		return $ret;
	}

	public function isParent( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function _renderTitle( array $x )
	{
		$ret = $this->self->title( $x );
		if( is_array($ret) ) $ret = current( $ret );
?>
<h1><?php echo $ret; ?></h1>
<?php
	}


	public function title( array $x )
	{
		$ret = '__Settings__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		$ret[ '50-time' ] = [ '.setting-time', '__Date and time__' ] ;
		return $ret;
	}
}