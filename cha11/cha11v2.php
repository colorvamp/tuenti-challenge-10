#!/usr/bin/php
<?php declare(strict_types=1);
	class _cha11{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$case = 0;
			while ($this->cases--) {
				$this->line = trim(array_shift($this->lines));

				$forbidden = explode(' ',$this->line);
				$num = array_shift($forbidden);

				$n = 90;
$this->total = 0;
$this->findCombinations($n);
var_dump($this->total);
exit;

				echo 'Case #'.(++$case).': '.count($_dwarfs->additions).PHP_EOL;
			}
		}
		function additions($arr,$index,$num,$reducedNum) {
			if ($reducedNum < 0) {return;}
			if ($reducedNum == 0) {
				echo implode('',$arr).PHP_EOL;
				//echo "\n";
				$this->total++;
				return;
			} 
  
			$prev = ($index == 0) ? 1 : $arr[$index - 1];
  
			for ($k = $prev; $k <= $num ; $k++) {
				$arr[$index] = $k;
				$this->additions($arr, $index + 1, $num, $reducedNum - $k);
			}
		}
		function findCombinations($n) {
			$arr = []; 

			//find all combinations 
			$this->additions($arr, 0, $n, $n); 
		}
	}

	(new _cha11())->start();

