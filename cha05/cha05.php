#!/usr/bin/php
<?php
	class _cha05{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));
			$this->range = range(20,29);

			/*$this->number = 1062394839287122816;
			$count = $this->init();
			var_dump($count);exit;//*/

			$case = 0;
			while ($this->cases--) {
				$this->number = trim(array_shift($this->lines));

				if ($this->number < 20) {echo 'Case #'.(++$case).': IMPOSSIBLE'.PHP_EOL;continue;}

				$count = $this->init();
				if ($count === false) {$count = 'IMPOSSIBLE';}
				echo 'Case #'.(++$case).': '.$count.PHP_EOL;
			}
		}
		function init(){
			$this->base = intval(bcdiv($this->number,20));
			$this->number -= 20 * $this->base;
			if ($this->number < 1) {return $this->base;}

			$this->maxpath = 0;
			$this->path = [];
			$found = false;
			do {
				foreach ($this->range as $seed) {
					$found = $this->step($this->number,$seed);
				}

				if ($this->maxpath > 0) {
					return $this->maxpath + $this->base;
				}

				$this->number += 20;
				$this->base--;
				if ($this->base < 0) {return false;}
			} while (!$found);
		}
		function step(int $num,int $test,$path = []){
			if ($num < 20 || $test > $num) {return false;}

			$path[] = $test;
			$num -= $test;

			if ($num === 0) {
				$count = count($path);
				if ($count > $this->maxpath) {
					$this->maxpath = $count;
					$this->path = $path;
				}
				return true;
			}

			foreach ($this->range as $tm) {
				$r = $this->step($num,$tm,$path);
				if ($r === true) {return true;}
			}
			return false;			
		}
	}

	(new _cha05())->start();

