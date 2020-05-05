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

				$_dwarfs = new _dwarfs();
				$_dwarfs->forbidden = $forbidden;
				$_dwarfs->target = $num;
				$_dwarfs->start();

				echo 'Case #'.(++$case).': '.$_dwarfs->count.PHP_EOL;
			}
		}
	}

	class _dwarfs{
		public $found = false;
		public $forbidden = [];
		public $target = 0;
		public $additions = [];
		public $count = 0;
		public $visited = [];
		function start(){
			$this->range = range(1,$this->target - 1);
			$this->range = array_diff($this->range,$this->forbidden);
			$this->range = array_reverse($this->range);

			$count = 0;
			$total = count($this->range);
			foreach ($this->range as $num) {
				fwrite(STDERR,$count.'/'.$total.PHP_EOL);
				$this->move($num);
				$count++;
				$this->range = array_diff($this->range,[$num]);
			}
		}
		function move(int $num,int $sum = 0,array $path = []){
			$sum += $num;
			if ($sum > $this->target) {
				return false;
			}
			if ($sum == $this->target) {
				$this->count++;
				return true;

				$path[] = $num;
				sort($path);
				$key = implode('.',$path);
				if (isset($this->additions[$key])) {return false;}

				$this->additions[$key] = true;
				return true;
			}

			$path[] = $num;
			sort($path);
			foreach ($this->range as $nm) {
				if ($nm > $num) {continue;}
				$this->move($nm,$sum,$path);
			}
		}
	}

	(new _cha11())->start();

