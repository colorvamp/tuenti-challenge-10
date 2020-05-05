#!/usr/bin/php
<?php
	class _cha10{
		function start(){
			//password: PetsAreNotAllowedInVillaFeristela
			//$this->zombiebrains();
		}
		function zombiebrains(){
			$num = 7;
			$found = false;
			do {
				if ($num % 7 == 0
				 && $num % 2 == 1
				 && $num % 3 == 2
				 && $num % 4 == 3
				 && $num % 5 == 4
				 && $num % 6 == 5) {
					$found = true;
					var_dump($num);
					break;
				}

				$num += 7;
			} while ($found === false);
		}
		
	}

	(new _cha10())->start();

