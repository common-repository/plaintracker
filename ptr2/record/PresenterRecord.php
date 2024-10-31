<?php
namespace Plainware\PlainTracker;

class PresenterRecord
{
	public $self = __CLASS__;

	public function textState( $stateId )
	{
		$ret = [];
		$ret[ 'enter' ] = '__Entered__';
		$ret[ 'submit' ] = '__Submitted__';
		$ret[ 'approve' ] = '__Approved__';

		$ret = isset( $ret[$stateId] ) ? $ret[$stateId] : $stateId;
		return $ret;
	}

	public function htmlState( array $x, $stateId, $textLabel = '' )
	{
		$bgcolor = '#900';
		$color = '#fff';

		if( ! strlen($textLabel) ) $textLabel = $this->self->textState( $stateId );

		if( 'enter' == $stateId ){
			$bgcolor = '#666';
		}
		if( 'submit' == $stateId ){
			$bgcolor = '#c60';
		}
		if( 'approve' == $stateId ){
			$bgcolor = '#690';
		}

		if( 'enter' == $stateId ){
			$bgcolor = '#ccc';
			$color = null;
		}
		if( 'submit' == $stateId ){
			$bgcolor = '#ff9';
			$color = null;
			$color = '#000';
		}
		if( 'approve' == $stateId ){
			$bgcolor = '#cf9';
			$color = null;
			$color = '#000';
		}
?>
<span style="padding: 0 .25em; border-radius: .25em; background-color: <?php echo esc_attr($bgcolor); ?>;<?php if( $color ): ?> color: <?php echo esc_attr($color); ?>;<?php endif; ?>"><?php echo esc_html($textLabel); ?></span>
<?php
	}
}