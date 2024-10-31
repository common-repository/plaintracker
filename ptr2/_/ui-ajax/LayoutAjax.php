<?php
namespace Plainware;

class LayoutAjax
{
	public $self = __CLASS__;

	public $parent = Layout::class;
	public $uri = Uri::class;

	public function wpRenderJs()
	{
		echo $this->self->renderJs();
	}

	public function renderJs()
	{
		$jsFile = __DIR__ . '/ajax.js';
?>
<script>
<?php echo file_get_contents( $jsFile ); ?>
</script>
<?php
	}

	public function render( $ret, array $x )
	{
		$listSkip = [ 'install' ];

		$slug = $x['slug'] ?? '';
		if( in_array($slug, $listSkip) ){
			return $ret;
		}

		$layoutParam = $this->uri->getLayoutParam();
		$layout = isset( $x[$layoutParam] ) ? $x[$layoutParam] : null;

		if( null === $layout ){
			$search = '<div id="pw2"';
			$replace = '<div id="pw2" class="pw-ajax-container" data-pw-slug-param-name="' . $this->uri->getSlugParam() . '" data-pw-layout-param-name="' . $this->uri->getParamPrefix() . $this->uri->getLayoutParam() . '"';

			$ret = str_replace( $search, $replace, $ret );
			if( defined('WPINC') ){
				if( is_admin() ){
					add_action( 'admin_footer', [$this, 'renderJs'] );
				}
				else {
					add_action( 'wp_footer', [$this, 'renderJs'] );
				}
			}
			else {
				$ret .= $this->self->renderJs();
			}
		}
		elseif( 'ajax' == $layout ){
			// $pos = strpos( $ret, '<div id="pw2"' );
			// if( false !== $pos ){
				// $search = '<div id="pw2">';
				// $replace = '';
				// $ret = str_replace( $search, $replace, $ret );

				// $search = '</div>';
				// $replace = '';
				// $pos = strrpos( $ret, $search );

				// $ret = substr( $ret, 0, $pos );
				// $ret = trim( $ret );
			// }
		}

		return $ret;
	}
}