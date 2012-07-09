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

function tickerTick(vars) {
	if ( vars.ticker.text().length < vars.ticker.width() * 0.15 && !vars.currentlyRequesting ) {
		vars.currentlyRequesting = true;
		$.getScript('ticker.php', function() {
			vars.currentlyRequesting = false;
		} );
	}
	vars.ticker.text(vars.ticker.text().substr(1));
}
setInterval( tickerTick, 750, {ticker: $('#ticker')} );

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
	message += ' (' + formatTickerMoney( data.vals[1] ) + ')    ';
	$('#ticker').append( message );
}
window['tickerCB'] = tickerCB;

