#!/usr/bin/php
<?php
	class _cha16{
		public $groups = [];
		public $floors = [];
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$case = 0;
			while ($this->cases--) {
				$this->groups = [];
				$this->s = [];
				$this->x = [];
				[$totalfloors,$groups] = explode(' ',trim(array_shift($this->lines)));

				$emplo_counter = 0;
				while ($groups--) {
					[$emplo,$floors] = explode(' ',trim(array_shift($this->lines)));
					$access = explode(' ',trim(array_shift($this->lines)));
					$emplos_group = range($emplo_counter,$emplo_counter + $emplo - 1);
					$emplo_counter += $emplo;

					foreach ($access as $acc) {
						if (!isset($this->s[$acc])) {$this->s[$acc] = [];}
						$this->s[$acc] = array_merge($this->s[$acc],$emplos_group);
					}
				}

				$count = $this->firstStrategy();
				echo 'Case #'.(++$case).': '.$count.PHP_EOL;
			}
		}
		function firstStrategy(){
			$this->matrix = [];
			$this->emplos = [];
			$this->floors = count($this->s);
			$keys = array_keys($this->s);
			foreach ($keys as $key) {
				foreach ($this->s[$key] as $pos) {
					$this->matrix[$key][$pos] = true;
					$this->emplos[$pos][$key] = true;
				}
				$this->s[$key] = null;
				unset($this->s[$key]);
			}

			$global = false;
			$index = [];
			for ($k = 0;$k < count($this->matrix);$k++) {
				$line = $this->matrix[$k];
				if ($global === false) {
					$global = $line;
					continue;
				}
				foreach ($line as $i=>$v) {
					if (!isset($global[$i])) {
						$global[$i] = true;
						continue;
					}
					unset($this->matrix[$k][$i]);
				}
			}

			$idx = [];
			for ($f = 0;$f < count($this->matrix);$f++) {
				$idx[$f] = count($this->matrix[$f]);
			}

			//echo 'init: '.max($idx).PHP_EOL;
			$this->max = max($idx);
			$this->samecount = 0;

			do {
				$sust = false;

				arsort($idx);
				foreach ($idx as $k=>$c) {
					foreach ($idx as $j=>$c) {
						if ($idx[$k] <= $idx[$j]) {continue;}
						/* Vamos a pasar de k a j, cogemos
						 * empleados de la linea que más tiene */
						$canpass = false;
						foreach ($this->matrix[$k] as $emplo=>$dummy) {
							/* Recorremos todos estos empleados buscando 
							 * uno que podamos pasar a la linea que mas tiene */
							if (isset($this->emplos[$emplo][$j])) {
								unset($this->matrix[$k][$emplo]);
								$this->matrix[$j][$emplo] = true;
								$idx[$k]--;
								$idx[$j]++;
								$sust = true;

								break;
								if ($idx[$k] <= $idx[$j]) {break;}
							}
						}
					}
				}

				$max = max($idx);
				if ($this->max == $max) {
					$this->samecount++;
					//var_dump($this->samecount);
				} else {
					$this->samecount = 0;
					$this->max = $max;
				}

				if ($this->samecount > 100) {break;}
			} while ($sust);

			return max($idx);
		}
	}

	(new _cha16())->start();


