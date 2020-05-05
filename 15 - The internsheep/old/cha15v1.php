#!/usr/bin/php
<?php
	class _cha15{
		public $__crc32_table = [];
		function start(){
			$this->lines  = file('php://stdin');
			//$this->cases  = trim(array_shift($this->lines));
			$this->count  = 0;

			$case = 0;
			while (count($this->lines)) {
				$tmp = trim(array_shift($this->lines));
				[$file,$samples] = explode(' ',$tmp);

				$this->modifs = [];
				while ($samples--) {
					[$seek,$chr] = explode(' ',trim(array_shift($this->lines)));
					$this->modifs[] = [
						 'case'=>count($this->modifs)
						,'seek'=>$seek
						,'chr'=>chr($chr)
					];
				}

				$chunks = [];
				$max = count($this->modifs);
				do {
					$chunks[] = array_slice($this->modifs,0,$max);
				} while ($max--);
				$chunks = array_reverse($chunks);

				foreach ($chunks as $k=>$chunk) {
//if (empty($chunk)) {continue;}
					$this->modifs = $this->order($chunk);
					$this->__crc32_init_table();
					$hash = $this->__crc32_file('data/'.$file);

					echo $file.' '.($k).': '.dechex($hash).PHP_EOL;
				}
			}
			//$this->stream('data/badger0000');
			//$this->__crc32_init_table();
			//$hash = $this->__crc32_file('data/badger0000');
			//var_dump(dechex($hash));
			//exit;

		}
		function test(){
			copy('data/antelope0000','antelope0000');
			$this->fp = fopen('antelope0000','w+');

			fseek($this->fp,336237519);
			fwrite($this->fp,chr(75));

			fseek($this->fp,0,SEEK_END);
			fwrite($this->fp,chr(0));

exit;
			//fclose($this->fp);
		}
		function test2(){
			copy('data/admiral0000','admiral0000');
			$this->fp = fopen('admiral0000','w+');

			fseek($this->fp,0);
			fwrite($this->fp,chr(169));

			fseek($this->fp,2);
			fwrite($this->fp,chr(227));

			fseek($this->fp,3);
			fwrite($this->fp,chr(46));

			fseek($this->fp,4);
			fwrite($this->fp,chr(232));

			fclose($this->fp);
		}
		function order(array $modifs = []): array{
			foreach ($modifs as $k=>$modif1) {
				foreach ($modifs as $j=>&$modif2) {
					if ($k <= $j) {continue;}
					if ($modif2['seek'] >= $modif1['seek']) {
						$modif2['seek']++;
					}
				}
				unset($modif2);
			}

			$tmp = [];
			foreach ($modifs as $modif) {$tmp[$modif['seek']] = $modif;}
			$modifs = $tmp;
			return $modifs;
		}
		function readFile($debug = false){
			if ($debug) {
				$name = fread($this->fp,1000);
				var_dump(substr($name,124,12));exit;
			}

			$name  = fread($this->fp,100);
			$mode  = fread($this->fp,8);
			$owner = fread($this->fp,8);
			$group = fread($this->fp,8);
			$size  = fread($this->fp,12);
			$mod   = fread($this->fp,12);
			$check = fread($this->fp,8);
			$type1 = fread($this->fp,1);
			$unlk  = fread($this->fp,100);
			$type2 = fread($this->fp,1);
			fread($this->fp,100);
			fread($this->fp,6); //ustar
			fread($this->fp,2);
			fread($this->fp,32);
			fread($this->fp,32);
			fread($this->fp,8);
			fread($this->fp,8);
			if ($type1 == 5) {
				$extra = fread($this->fp,66);
				return false;
			}
		}
		function dechex($dec) {
			$last = $dec % 16;
			$remain = ($dec - $last) / '16';

			if ($remain == 0) {
				return dechex($last);
			} else {
				return strtoupper($this->dechex($remain).dechex($last));
			}
		}
		function __crc32_init_table() {
			$this->__crc32_table = [];
			// This is the official polynomial used by
			// CRC-32 in PKZip, WinZip and Ethernet.
			$polynomial = 0x04c11db7;

			// 256 values representing ASCII character codes.
			for ($i = 0;$i <= 0xFF;++$i) {
				$this->__crc32_table[$i] = ($this->__crc32_reflect($i,8) << 24);
				for ($j = 0;$j < 8;++$j) {
					$this->__crc32_table[$i] = (($this->__crc32_table[$i] << 1) ^
					(($this->__crc32_table[$i] & (1 << 31))?$polynomial:0));
				}
				$this->__crc32_table[$i] = $this->__crc32_reflect($this->__crc32_table[$i], 32);
			}
		}
		function __crc32_reflect($ref, $ch) {
			$value = 0;

			// Swap bit 0 for bit 7, bit 1 for bit 6, etc.
			for ($i = 1;$i < ($ch + 1);++$i) {
				if ($ref & 1) $value |= (1 << ($ch-$i));
				$ref = (($ref >> 1) & 0x7fffffff);
			}
			return $value;
		}
		function __crc32_file($name) {
			// Start out with all bits set high.
			$this->addlength = true;
			$crc = 0xffffffff;
			$total = filesize($name);
			$index = 0;

			if (($fp = fopen($name,'rb')) === false) {return false;}
			// Perform the algorithm on each character in file
			for (;;) {
				$buffer = fread($fp,100000);
				$len = strlen($buffer);
				if ($len == 0 && !empty($this->modifs)) {
					$len = count($this->modifs);
					$this->addlength = false;
				}
				if ($len == 0) {break;}
				for ($j = 0;$j < $len;$j++) {
					$c = $buffer[$j] ?? '';
					if (isset($this->modifs[$index])) {
						$c = $this->modifs[$index]['chr'];
						if ($this->addlength) {$j--;}
						unset($this->modifs[$index]);
					}


					$index++;
					$total--;
					if ($total % 100000000 == 0) {fwrite(STDERR, $total.PHP_EOL);}
					$crc = (($crc >> 8) & 0x00ffffff) ^ $this->__crc32_table[($crc & 0xFF) ^ ord($c)];
				}
			}

			fclose($fp);

			// Exclusive OR the result with the beginning value.
			return $crc ^ 0xffffffff;
		}
	}

	(new _cha15())->start();


