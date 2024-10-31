<?php
namespace Plainware\PlainTracker;

class PresenterTimesheet
{
	public $self = __CLASS__;

	public function textState( $stateId )
	{
		$ret = [];

		$ret[ 'draft' ] = '__Draft__';
		$ret[ 'submit' ] = '__Submitted__';
		$ret[ 'approve' ] = '__Approved__';
		$ret[ 'process' ] = '__Processed__';

		$ret = isset( $ret[$stateId] ) ? $ret[$stateId] : '__N/A__';
		return $ret;
	}

	public function htmlState( array $x, $stateId, $textLabel = '' )
	{
		$bgcolor = '#f99';
		$color = '#000';

		$stateTextLabel = $this->self->textState( $stateId );
		if( ! strlen($textLabel) ) $textLabel = $stateTextLabel;

		if( 'draft' == $stateId ){
			$bgcolor = '#ccc';
			// $color = null;
			$color = '#000';
		}
		if( 'submit' == $stateId ){
			$bgcolor = '#ff9';
			// $color = null;
			$color = '#000';
		}
		if( 'approve' == $stateId ){
			$bgcolor = '#cf9';
			// $color = null;
			$color = '#000';
		}
		if( 'process' == $stateId ){
			$bgcolor = '#c9f';
			// $color = null;
			$color = '#000';
		}
?>
<span title="<?php echo esc_attr($stateTextLabel); ?>" style="padding: 0 .25em; border-radius: .25em; background-color: <?= esc_attr($bgcolor); ?>;<?php if( $color ): ?> color: <?= esc_attr($color); ?>;<?php endif; ?>"><?php echo $textLabel; ?></span>
<?php
	}
}