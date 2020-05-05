#!/usr/bin/php
<?php
	class _cha03{
		function order(){
			//mb_regex_encoding('UTF-8');
			$this->data = file_get_contents('pg17013.txt');
			$this->data = preg_replace('![^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\n]+!iu',' ',$this->data);
			$this->data = mb_strtolower($this->data);
			$this->data = preg_replace('![ \n]{2,}!',' ',$this->data);
			$this->data = preg_split('![ \n]!',$this->data);
			$this->data = array_count_values($this->data);
			foreach ($this->data as $k=>&$dummy) {
				if (mb_strlen($k) < 3) {unset($this->data[$k]);continue;}
				$dummy = ['w'=>$k,'c'=>$dummy];
			}
			unset($dummy);
			uasort($this->data,function($a,$b){
				if ($a['c'] == $b['c']) {
					return $a['w'] > $b['w'] ? 1 : -1;
				}
				return $b['c'] - $a['c'];
			});
		}
		function start(){
			$this->order();
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));
			$this->keys  = array_flip(array_keys($this->data));

			$case = 0;
			while ($this->cases--) {
				$this->word = trim(array_shift($this->lines));
				if (is_numeric($this->word)) {
					$key = array_search(($this->word - 1),$this->keys);
					echo 'Case #'.(++$case).': '.$key.' '.$this->data[$key]['c'].PHP_EOL;
					continue;
				}
				$pos = $this->keys[$this->word];
				echo 'Case #'.(++$case).': '.$this->data[$this->word]['c'].' #'.($pos + 1).PHP_EOL;
			}
		}
	}

	(new _cha03())->start();

