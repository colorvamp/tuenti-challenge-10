#!/usr/bin/php
<?php
	class _cha04{
		function start(){
			// http://steam-origin.contest.tuenti.net:9876/games/free_shoot/get_key
			// http://pre.steam-origin.contest.tuenti.net:9876/games/free_shoot/get_key
			// http://pre.steam-origin.contest.tuenti.net:9876/games/cat_fight/get_key
			// -> ping steam-origin.contest.tuenti.net > 52.49.91.111
			// -> just nano /etc/hosts and add "52.49.91.111     pre.steam-origin.contest.tuenti.net"
		}
	}

	(new _cha04())->start();

