function formatTickerMoney(amt) {
	var f = Math.floor;
	amt = f( amt );
	if ( amt < 100 )
		return amt + 'c';
	amt = f( amt / 100 );
	if ( amt < 100 )
		return amt + 's';
	amt = f( amt / 100 );
	if ( amt < 1000 )
		return amt + 'g';
	return f( amt / 1000 ) + 'K';
}

function tickerCB( data ) {
	var message = data.name;
	var diff = Math.round( data.vals[1] - data.vals[0] );
	if ( diff >= 0 ) {
		message += ' \u25B2 ';
	} else {
		message += ' \u25BC ';
		diff = -diff;
	}
	message += formatTickerMoney( diff );
	message += ' (' + formatTickerMoney( data.vals[1] ) + ')';
	console.log( message );
}
window['tickerCB'] = tickerCB;

