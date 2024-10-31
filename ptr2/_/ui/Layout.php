<?php
namespace Plainware;

class Layout
{
	public $self = __CLASS__;

	public $uri = Uri::class;
	public $asset = HtmlAsset::class;
	public $htmlMenu = HtmlMenu::class;
	public $app = App::class;
	public $handler = Handler::class;
	public $modelInstall = ModelInstall::class;
	public $t = Time::class;

	public function render( array $x, $ret )
	{
		$paramLayout = $this->uri->getLayoutParam();
		$layout = $x[$paramLayout] ?? null;

		$showPartial = false;
		if( null !== $layout ){
			if( 'none' == $layout ){
				$showPartial = true;
			}
			elseif( is_array($ret) && array_key_exists($layout, $ret) ){
				$showPartial = true;
			}
		}

		if( $showPartial ){
		// basic layout
			$thisPage = $x[ '$page' ];

			$nav = $this->app->methodExists( $thisPage, 'nav' ) ? $thisPage->nav( $x ) : [];
			$title = $this->app->methodExists( $thisPage, 'title' ) ? $thisPage->title( $x ) : [];
			$ret = $this->self->renderBody( $x, $ret, $nav, $title );

		// empty body? then render vertical nav as content
			if( ! $ret ){
				// $ret = $this->htmlMenu->render( $x, $nav, true );
				$ret = $this->htmlMenu->render( $x, $nav, false );
			}
			return $ret;
		}

		$ret = $this->self->renderMain( $x, $ret );
		if( null === $layout ){
			$ret = '<div id="pw2">' . $ret . '</div><!-- pw2: end -->';
		}

	// move script to end script
		$scriptPosList = [];
		$pos1 = strpos( $ret, '<script' );
		while( false !== $pos1 ){
			$pos2 = strpos( $ret, '</script>', $pos1 );
			if( false === $pos2 ){
				break;
			}
			$pos2 = $pos2 + strlen( '</script>' );
			$scriptPosList[] = [ $pos1, $pos2 ];
			$pos1 = strpos( $ret, '<script', $pos2 );
		}

		$retScript = '';
		foreach( $scriptPosList as $scriptPos ){
			$retScript .= substr( $ret, $scriptPos[0], $scriptPos[1] - $scriptPos[0] );
		}

		foreach( array_reverse($scriptPosList) as $scriptPos ){
			$ret = substr_replace( $ret, '', $scriptPos[0], $scriptPos[1] - $scriptPos[0] );
		}

		if( $retScript ){
			$ret .= $retScript;
		}

		return $ret;
	}

	public function navUser( array $x )
	{
		$ret = [];

		// $timeView = $this->t->formatFull( $this->t->getNow() );
		// $timeView = esc_html( $timeView );
		// $timeView = '<small>' . $timeView . '</small>';
		// $ret[ '85-time' ] = $timeView;

		return $ret;
	}

	public function renderMain( array $x, $ret )
	{
		$thisPage = $x[ '$page' ];

		$layoutParam = $this->uri->getLayoutParam();
		$isPrintView = isset($x[$layoutParam]) && ('print' == $x[$layoutParam]) ? true : false;

	// nav
		$nav = $this->app->methodExists( $thisPage, 'nav' ) ? $thisPage->nav( $x ) : [];
		$breadcrumb = $this->self->findBreadcrumb( $x );

	// has parent page - adjust nav and title
		$parentPage = $parentX = null;
		if( $this->app->methodExists($thisPage, 'isParent') && $thisPage->isParent($x) ){
			$parentPage = $thisPage;
			$parentX = $x;
			$nav = [];
		}
		else {
			$uri = $x[ '$uri' ];
			$testUri = $uri->parent;
			if( $testUri ){
				$coreX = [];
				foreach( array_keys($x) as $k ){
					if( '$' == substr($k, 0, 1) ) $coreX[$k] = $x[$k];
					if( '*' == substr($k, 0, 1) ) $coreX[$k] = $x[$k];
				}
				$testX = $testUri->params + $coreX;
				$testX['$uri'] = $testUri;
				$testPage = $this->handler->findPage( $testUri->slug, $testX );
				if( $testPage && $this->app->methodExists($testPage, 'isParent') && $testPage->isParent($testX) ){
					$parentPage = $testPage;
					$parentX = $testX;
				}
			}
		}

		if( $parentPage ){
			$parentNav = $parentPage->nav( $parentX );

		// adjust links to point to parent page
			foreach( array_keys($parentNav) as $k ){
				if( ! $parentNav[$k] ) continue;
				$parentNav[$k] = $this->htmlMenu->finalizeOne( $parentNav[$k], $parentX );
			}

			if( $parentPage !== $thisPage ){
		// trim breadcrumb
				array_pop( $breadcrumb );
			}

		// move parent's nav to tabs
			foreach( $parentNav as $k => $e ){
				$ks = explode( '-', $k );

				// if( ! in_array('aside', $ks) ){
					// array_splice( $ks, 1, 0, 'aside' );
					// $k = join( '-', $ks );
				// }

				if( ! in_array('tab', $ks) ){
					array_splice( $ks, 1, 0, 'tab' );
					$k = join( '-', $ks );
				}

				$nav[ $k ] = $e;
			}
		}

		$breadcrumbView = $isPrintView ? '' : $this->self->renderBreadcrumb( $breadcrumb, $x );

	// title
		$textTitle = $this->app->methodExists( $thisPage, 'title' ) ? $thisPage->title( $x ) : '';
		if( $parentPage && ($parentPage !== $thisPage) ){
			$textTitle2 = $textTitle;
			$textTitle = $this->app->methodExists( $parentPage, 'title' ) ? $parentPage->title( $parentX ) : '';
		}
		else {
			$textTitle = $this->app->methodExists( $thisPage, 'title' ) ? $thisPage->title( $x ) : '';
			$textTitle2 = '';
		}

		if( $parentPage && ($parentPage !== $thisPage) ){
			$htmlTitle = ( $this->app->methodExists($parentPage, 'renderTitle') ) ? $parentPage->renderTitle( $parentX ) : '<h1>' . $textTitle . '</h1>';
		}
		else {
			$htmlTitle = ( $this->app->methodExists($thisPage, 'renderTitle') ) ? $thisPage->renderTitle( $x ) : '<h1>' . $textTitle . '</h1>';
		}

		$htmlTitle2 = $textTitle2 ? '<h2>' . $textTitle2 . '</h2>' : '';

		// $bodyView = '';
		$bodyView = $this->self->renderBody( $x, $ret, $nav, $textTitle );
		if( $bodyView && $isPrintView ){
			// open details
			// $bodyView = str_replace( '<details>', '<details open>', $bodyView );
		}

	// empty body? then render vertical nav as content
		$navTab = [];
		if( ! $bodyView ){
			$bodyView = $this->htmlMenu->render( $x, $nav, ['vert' => true] );
			$bodyView = '<nav>' . $bodyView . '</nav>';
			$navView = '';
		}
		else {
			$navKeys = array_keys( $nav );
			foreach( $navKeys as $k ){
		// skip nav for aside
				if( false !== strpos($k, 'aside-') ){
					unset( $nav[$k] );
					continue;
				}

		// skip nav for tab
				if( false !== strpos($k, '-tab-') ){
					$navTab[$k] = $nav[$k];
					unset( $nav[$k] );
					continue;
				}
			}

			$navView = $this->htmlMenu->render( $x, $nav );
		}
		$navTabView = $navTab ? $this->htmlMenu->render( $x, $navTab ) : '';

		$installed = $this->modelInstall->isInstalled();

		$navUser = $this->self->navUser( $x );
		$navUserView = $navUser ? $this->htmlMenu->render( $x, $navUser ) : '';
?>

<header>

<?php if( $breadcrumbView && $navUserView ): ?>
	<div class="pw-grid-2 pw-valign-middle">
		<div>
			<?php echo $breadcrumbView; ?>
		</div>
		<div class="pw-col-align-end">
			<?php echo $navUserView; ?>
		</div>
	</div>
<?php elseif( $breadcrumbView ) : ?>
	<?php echo $breadcrumbView; ?>
<?php elseif( $navUserView ) : ?>
	<div class="pw-col-align-end">
		<?php echo $navUserView; ?>
	</div>
<?php endif; ?>

<?php if( $navView ): ?>
	<ul>
		<li><?php echo $htmlTitle; ?></li>
		<li>
			<nav><?php echo $navView; ?></nav>
		</li>
	</ul>
<?php else: ?>
	<?php echo $htmlTitle; ?>
<?php endif; ?>

<?php if( $navTabView ): ?>
<nav><?php echo $navTabView; ?></nav>
<?php endif; ?>

<?php if( $htmlTitle2 ): ?><?php echo $htmlTitle2; ?><?php endif; ?>

</header>

<?php echo $bodyView; ?>

<?php
	}

	public function renderBody( array $x, $ret, array $nav, $title )
	{
		// if( ! $ret ) return $ret;

		$currentUri = $x['$uri'];
		$page = $x[ '$page' ];
		$slug = $currentUri->slug;

		// $slug = $x['slug'];
		// if( ! $slug ){
			// $nav = [];
		// }

		$msgView = $this->self->renderMessages( $x );

		$navAside = [];
		$keys = array_keys( $nav );
		foreach( $keys as $k ){
			if( false !== strpos($k, 'aside-') ){
				$navAside[ $k ] = $nav[ $k ];
				unset( $nav[$k] );
			}
		}

		if( $navAside && (! is_array($ret)) ){
			$ret = [ 'main' => $ret ];
		}
		$navAsideView = $navAside ? $this->htmlMenu->render( $x, $navAside, ['vert' => true] ) : '';

		if( is_array($ret) && (! $ret) ){
			return;
		}
?>

<?php if( $msgView ): ?>
<section><?php echo $msgView; ?></section>
<?php endif; ?>

<section id="pw-<?php echo esc_attr($slug); ?>"><!-- start of page -->
<?php if( is_array($ret) ): ?>

	<?php if( $navAsideView ): ?>
		<div class="pw-grid-3-1">
			<div>
	<?php endif; ?>

	<?php foreach( $ret as $retK => $retV ) : ?>
		<?php
		$startSection = ( count($ret) > 1 ) ? true : false;
		$endSection = ( count($ret) > 1 ) ? true : false;
		?>
		<?php if( $startSection ): ?><section id="pw-<?php echo esc_attr($slug); ?>-<?php echo $retK; ?>"><?php endif; ?>
			<?php echo $retV; ?>
		<?php if( $endSection ): ?></section><?php endif; ?>
	<?php endforeach; ?>

	<?php if( $navAsideView ): ?>
			</div>
			<aside>
				<nav>
					<?php echo $navAsideView; ?>
				</nav>
			</aside>
		</div>

	<?php endif; ?>

<?php else : ?>

	<?php echo $ret; ?>

<?php endif; ?>

</section><!-- end of page -->

<?php
	}

	public function renderMessages( array $x )
	{
		$listMessage = [];

		foreach( $x as $k => $v ){
			if( '-msg-' != substr($k, -strlen('-msg-')) ) continue;

			$slug = substr( $k, 0, -strlen('-msg-') );
			$thisPage = $this->handler->findPage( $slug, $x );
			if( ! $thisPage ) continue;

			if( ! $this->app->methodExists($thisPage, 'msg') ) continue;

			$thisMessage = $thisPage->msg( $v, $x );
			if( false === strpos($thisMessage, '<strong') ){
				$thisMessage = '<em>' . $thisMessage . '</em>';
			}

			$listMessage[] = $thisMessage;
		}

		if( ! $listMessage ) return;
?>

<table>
<?php foreach( $listMessage as $msg ): ?>
	<tr>
		<td>
			<?php echo $msg; ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>

<?php
	}

	public function findBreadcrumb( array $x )
	{
		$ret = [];

		$currentUri = $x[ '$uri' ];
		if( ! $currentUri->slug ) return $ret;

		$coreX = [];
		foreach( array_keys($x) as $k ){
			if( '$' == substr($k, 0, 1) ) $coreX[ $k ] = $x[ $k ];
			if( '*' == substr($k, 0, 1) ) $coreX[ $k ] = $x[ $k ];
		}

		$uri = $currentUri;
		while( $uri = $uri->parent ){
			$thisX = $uri->params + $coreX;
			$thisX[ '$uri' ] = $uri;
			$thisPage = $this->handler->findPage( $uri->slug, $thisX );
			if( ! $thisPage ) continue;

			$thisTitle = $this->app->methodExists( $thisPage, 'title' ) ? $thisPage->title( $thisX ) : '';
			$thisTo = $this->uri->toString( $uri );
			$ret[] = [ $thisTo, $thisTitle, $thisPage, $thisX ];
		}

		// if( ! $breadcrumbList ) return;

	// index page
		$uri = $this->uri->construct();
		$thisX = $coreX;
		$thisX[ '$uri' ] = $uri;
		$thisPage = $this->handler->findPage( '', $thisX );
		if( $thisPage ){
			// $thisTitle = $this->app->methodExists( $thisPage, 'title' ) ? $thisPage->title( $thisX ) : '';
			$thisTitle = $this->app->methodExists( $thisPage, 'title' ) ? $thisPage->title( $x ) : '';
			if( null !== $thisTitle ){
				$ret[] = [ '', $thisTitle, $thisPage, $thisX ];
			}
		}

		$ret = array_reverse( $ret );
		return $ret;
	}

	public function renderBreadcrumb( array $breadcrumb, array $x )
	{
?>

<nav>
<ul>
<?php foreach( $breadcrumb as $e ) : ?>
<li>&raquo;</li>
<li><a href="URI:<?php echo esc_attr($e[0]); ?>"><?php echo esc_html($e[1]); ?></a></li>
<?php endforeach; ?>
</ul>
</nav>

<?php
	}

	public function renderCss( array $x )
	{
?>
/* custom css */
<?php
	}
}