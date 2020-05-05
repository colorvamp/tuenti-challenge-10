#!/usr/bin/php
<?php

	class _split{
		public $file = [];
		function start(){
			$this->lines  = file('php://stdin');
			$this->count  = 0;

			while (count($this->lines)) {
				$head = trim(array_shift($this->lines));
				[$file,$samples] = explode(' ',$head);
				if (isset($this->file[$file])) {
					echo 'repetido';
					exit;
				}

				while ($samples--) {
					$this->file[$file][] = trim(array_shift($this->lines));
				}
			}

			$total = count($this->file);
			$chunk = intval(ceil($total / 8));
			$chunks = array_chunk($this->file,$chunk,true);
			foreach ($chunks as $k=>$chunk) {
				$blob = '';
				foreach ($chunk as $file=>$lines) {
					$blob .= $file.' '.count($lines).PHP_EOL;
					$blob .= implode(PHP_EOL,$lines).PHP_EOL;
				}
				file_put_contents('multi/chunk'.$k.'.txt',$blob);
			}
		}
	}

	(new _split())->start();

