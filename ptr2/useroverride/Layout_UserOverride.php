<?php
namespace Plainware\PlainTracker;

class Layout_UserOverride
{
	public $self = __CLASS__;

	public $conf = ConfUserOverride::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $auth = Auth::class;

	public function render( $ret, array $x )
	{
		$p = $this->conf->paramName();

		$overridenUserId = $this->auth->getCurrentUserId( $x ); 
		$originalUserId = $this->auth->getCurrentUserId( [$p => null] + $x );
		if( $overridenUserId == $originalUserId ) return;

		$thisRet = '<div style="margin-bottom: 1rem;">' . $this->self->renderOverrideMessage( $x, $overridenUserId ) . '</div>';

		$pos = strpos( $ret, '<div id="pw2"' );
		if( false !== $pos ){
			$pos2 = strpos( $ret, '>', $pos );
			$pos2 = $pos2 + strlen( '>' );
			$ret = substr( $ret, 0, $pos2 ) . $thisRet . substr( $ret, $pos2 );
		}
		else {
			$ret = $thisRet . $ret;
		}

		return $ret;
	}

	public function renderOverrideMessage( array $x, $overridenUserId )
	{
		$p = $this->conf->paramName();
		$newUser = $this->modelUser->findById( $overridenUserId );
?>

<table>
<caption><strong>__User override in effect__</strong></caption>
<tbody>
	<tr>
		<th scope="row">__Viewing as__</th>
		<td>
			<a href="URI:user--user-id?id=<?php echo esc_attr($overridenUserId); ?>&<?php echo esc_attr($p); ?>=null">
				<?php if( $newUser ): ?><?= esc_html($newUser->title); ?><?php else : ?>__N/A__ (id: <?php echo esc_html($overridenUserId); ?>)<?php endif; ?>
			</a>
		</td>
		<th role="menu">
			<nav>
				<a href="URI:?<?php echo esc_attr($p); ?>=null"><i>&times;</i><span>__Stop override__</span></a>
			</nav>
		</th>
	</tr>
</tbody>
</table>

<?php
	}
}