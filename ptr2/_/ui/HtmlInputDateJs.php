<?php
namespace Plainware;

class HtmlInputDateJs
{
	public $self = __CLASS__;
	public $form = HtmlForm::class;
	public $t = Time::class;

	public function render( $name, $value = '' )
	{
		if( null === $value ) $value = '';
		if( strlen($value) < 8 ) $value = '';

		$nameY = $name . '_y';
		$nameM = $name . '_m';
		$nameD = $name . '_d';

		$submittedValue = $this->form->getValue( $name );
		if( null !== $submittedValue ) $value = $submittedValue;

		if( ! strlen($value) ){
			$value = $this->t->getDate( $this->t->getNow() );
		}

		$valueY = $this->t->getYear( $value );
		$valueM = $this->t->getMonth( $value );
		$valueD = $this->t->getDay( $value );

		$error = $this->form->getError( $name );
?>

<div>
<input name="<?php echo esc_attr($name); ?>" type="text" value="<?php echo $value; ?>" title="<?php echo esc_attr($this->t->formatDateFull($value) ); ?>" data-pw-input-date>
</div>

<?php echo $this->self->renderJs( $name, $value ); ?>

<?php
	}

	public function renderJs( $inputName )
	{
		static $alreadyShown = false;
		if( $alreadyShown ) return;
		$alreadyShown = true;

		$dictMonth = $this->t->getFormatMonths();
		$dictWkd = $this->t->getFormatWeekdays();
?>

<style>
#pw2 .pw-input-date td, #pw2 .pw-input-date th { text-align: center; vertical-align: middle; padding: 1px; }
#pw2 .pw-input-date button { display: block; width: 100%; text-align: center; }
#pw2 .pw-input-date tbody button { border: 0; border-radius: 0; }
#pw2 .pw-input-date thead th { border: 0; padding: 0 0 .5em 0; }
#pw2 .pw-input-date tfoot td { border: 0; padding: .5em 0 0 0; }
</style>

<script>
( function(){
const dateFormat = "<?php echo $this->t->getDateFormat(); ?>";
const monthLabel = ["<?php echo join( '","', $dictMonth ); ?>"];
const wkd = [<?php echo join( ',', array_keys($dictWkd) ); ?>];
const wkdLabel = {<?php foreach( $dictWkd as $wkd => $wkdLabel ): ?><?php echo $wkd; ?>:"<?php echo $wkdLabel; ?>",<?php endforeach; ?>};

document.querySelectorAll('[data-pw-input-date]').forEach( function(el){
	const container = el.parentNode;

	// replace default text input
	container.innerHTML = renderButton( el.value, el.name );
	// container.innerHTML = renderCalendar( el.value );

	container.addEventListener( 'click', function(ev){
		const trg = ev.target.closest( '[data-pw-input-date]' );
		if( ! trg ) return;

		ev.preventDefault();
		ev.stopImmediatePropagation();

		let route = '';
		let val = trg.getAttribute( 'data-pw-input-date' );
		if( val ){
			[ route, val ] = val.split( ':' );
		}
		else {
			route = 'calendar';
			const today = new Date();
			val = formatDateDb( today.getYear() + 1900, today.getMonth() + 1, today.getDate() );
		}

		if( 'calendar' == route ){
			container.innerHTML = renderCalendar( val );
			// container.scrollIntoView();
		}
		else if( 'set' == route ){
			container.innerHTML = renderButton( val, el.name );
		}
	});
});

function formatDate( valDb, strFormat )
{
	let ret = ( typeof strFormat !== 'undefined' ) ? strFormat : dateFormat;

	const d = parseDateDb( valDb );

	// j M Y n m d
	const listStrReplace = [
		[ 'j', d.d ],
		[ 'M', monthLabel[d.m-1] ],
		[ 'Y', d.y ],
		[ 'n', d.m ],
		[ 'm', (d.m < 10) ? ('0' + d.m) : d.m ],
		[ 'd', (d.d < 10) ? ('0' + d.d) : d.d ]
	];

	// find positions of keys to replace
	let listPosReplace = [];
	for( let ii = 0; ii < listStrReplace.length; ii++ ){
		const pos = ret.indexOf( listStrReplace[ii][0] );
		if( -1 == pos ) continue;
		listPosReplace.push( [pos, ii] );
	}

	// start replacing from end
	listPosReplace.sort( function(a, b){ return (b[0] - a[0]); } );
	for( let ii = 0; ii < listPosReplace.length; ii++ ){
		ret = ret.substring( 0, listPosReplace[ii][0] ) + listStrReplace[ listPosReplace[ii][1] ][1] + ret.substring( listPosReplace[ii][0] + 1 );
	}

	const jsDate = new Date( d.y, d.m - 1, d.d );
	let dayOfWeek = jsDate.getDay();
	if( 0 == dayOfWeek ) dayOfWeek = 7;
	ret = wkdLabel[ dayOfWeek ] + ', ' + ret;

	return ret;
}

function getDaysInMonth( year, month )
{
	return [31,((!(year % 4 ) && ( (year % 100 ) || !( year % 400 ) ))?29:28),31,30,31,30,31,31,30,31,30,31][month-1];
}

function formatDateDb( y, m, d )
{
	return '' + y + ( (m < 10) ? ('0' + m) : m ) + ( (d < 10) ? ('0' + d) : d );
}

function parseDateDb( val )
{
	return { y: parseInt( val.substr(0,4), 10 ), m: parseInt( val.substr(4,2), 10 ), d: parseInt( val.substr(6,2), 10 ) };
}

function renderButton( val, inputName )
{
	let ret = '<input type="hidden" name="' + inputName + '" value="' + val + '"><button type="button" data-pw-input-date="calendar:' + val + '">' + formatDate(val) + '</button>';
	return ret;
}

function renderCalendar( strVal )
{
	const val = parseDateDb( strVal );

	const y = val.y, m = val.m;
	const daysInMonth = getDaysInMonth( y, m );

// console.log( y + ':' + m );
	let ret = '';

	let nextY = ( 12 == m ) ? y + 1 : y;
	let nextM = ( 12 == m ) ? 1 : m + 1;
	let prevY = ( 1 == m ) ? y - 1 : y;
	let prevM = ( 1 == m ) ? 12 : m - 1;

	ret += '<table class="pw-input-date">';

	ret += '<thead>';
	ret += '<tr>';
	ret += '<th>' + '<button data-pw-input-date="calendar:' + formatDateDb(prevY, prevM, 1) + '">&laquo;&laquo;</button>' + '</th>';
	ret += '<th colspan="5"><b>' + monthLabel[m - 1] + ' ' + y + '</b></th>';
	ret += '<th>' + '<button data-pw-input-date="calendar:' + formatDateDb(nextY, nextM, 1) + '">&raquo;&raquo;</button>' + '</th>';
	ret += '</tr>';
	ret += '<tr>';
	for( let i = 0; i < wkd.length; i++ ){
		ret += '<th>' + wkdLabel[wkd[i]] + '</th>';
	}
	ret += '</tr>';
	ret += '</thead>';

	ret += '<tbody>';

	// find day of week for the 1st
	const jsDate = new Date( y, m - 1, 1 );
	let firstDayOfWeek = jsDate.getDay();
	if( 0 == firstDayOfWeek ) firstDayOfWeek = 7;

	let d = 0;
	for( let w = 0; w < 6; w++ ){
		ret += '<tr>';
		for( let ii = 0; ii < wkd.length; ii++ ){
			if( (! d) && (wkd[ii] == firstDayOfWeek) ){
				d = 1;
			}

			ret += '<td>';
			if( d ){
				if( d <= daysInMonth ){
					ret += '<button' + ' data-pw-input-date="set:' + formatDateDb(y, m, d) + '">' + d + '</button>';
				}
				d++;
			}
			ret += '</td>';
		}
		ret += '</tr>';
		if( d > daysInMonth ) break;
	}
	ret += '</tbody>';

	ret += '<tfoot>';
	ret += '<tr>';
	ret += '<td colspan="7">';
	ret += '<button data-pw-input-date="set:' + strVal + '">' + '&times; __Close__' + '</button>';
	ret += '</td>';
	ret += '</tr>';
	ret += '</tfoot>';

	ret += '</table>';

	return ret;
}

})();
</script>

<?php
	}

	public function grab( $name, array $post )
	{
		$ret = $post[ $name ] ?? null;
		return $ret;
	}
}