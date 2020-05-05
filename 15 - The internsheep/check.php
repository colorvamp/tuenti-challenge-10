#!/usr/bin/php
<?php

	class _check{
		public $file = [];
		function start(){
			$this->lines = file('submitInput.txt');
			$this->check = file('submitOutput');
			$this->count = 0;

			while (count($this->lines)) {
				$head = trim(array_shift($this->lines));
				[$file,$samples] = explode(' ',$head);

				$test = trim(array_shift($this->check));
				if (strpos($test,$file.' 0:') !== 0) {echo 'problem';exit;}

				while ($samples--) {
					$this->file[$file][] = trim(array_shift($this->lines));
				}

				foreach ($this->file[$file] as $k=>$dummy) {
					$test = trim(array_shift($this->check));
					if (strpos($test,$file.' '.($k + 1).':') !== 0) {echo 'problem';exit;}
				}
			}

			echo 'all good'.PHP_EOL;
			var_dump($this->check);
		}
	}

	(new _check())->start();

