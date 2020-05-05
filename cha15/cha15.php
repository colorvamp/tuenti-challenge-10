#!/usr/bin/php
<?php
	/* Decompress tar with
		- tar -xf animals.tar.gz --strip-components 2
		- mkdir data
		- mv *0000 data/
		* https://stackoverflow.com/questions/29892626/how-to-implement-zlibs-crc32-combine-function-in-php/29937788#29937788
	*/
	define('GF2_DIM', 32);

	class _cha15{
		public $__crc32_table = [];
		public $checkpoints = [];
		public $h1000000 = 0x1279cb9e;
		public $a1000000 = 1000000;
		public $h1000000x1000 = 0x63f45742;
		public $a1000000x1000 = 1000000 * 1000;
		public $h1000000x10000 = 0x2af1cc17;
		public $a1000000x10000 = 1000000 * 10000;
		function start(){
			$this->lines  = file('php://stdin');
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
				//if ($file != 'anaconda0000') {continue;}

				$chunks = [];
				$max = count($this->modifs);
				do {
					$chunks[] = array_slice($this->modifs,0,$max);
				} while ($max--);
				$chunks = array_reverse($chunks);

				foreach ($chunks as $k=>$chunk) {
					//if ($k > 10) {exit;}
					//if (count($chunk) < 1) {continue;}
					if (empty($chunk)) {
						$this->modifs = [];
						$hash = $this->hashfile('data/'.$file);
					} else {
						$this->modifs = $this->order($chunk);
						$hash = $this->chunkfile('data/'.$file);
						//$this->__crc32_init_table();
						//$hash = $this->__crc32_file('data/'.$file);
					}

					echo $file.' '.($k).': '.str_pad(dechex($hash),8,'0',STR_PAD_LEFT).PHP_EOL;
				}
			}
		}
		function chunkfile($file = ''){
			$total = filesize($file) + count($this->modifs);
			if ($total < ($this->a1000000 + 1)) {
				$this->__crc32_init_table();
				$base = $this->__crc32_file($file);
				return $base;
			}
			$cr  = false; //hash de 1000000
			$leng  = 0;

			foreach ($this->modifs as $modif) {
				$gap  = $modif['seek'] - $leng;
				$len  = $gap + 1;
				$crtmp = false;
				[$crtmp,$gap] = $this->remove_a1000000x10000($crtmp,$gap);
				[$crtmp,$gap] = $this->remove_a1000000x1000($crtmp,$gap);
				[$crtmp,$gap] = $this->remove_a1000000($crtmp,$gap);

				$i = $gap;$str = '';while ($i --) {$str .= chr(0);}
				$str .= $modif['chr'];
				$crtmp = crc32_combine($crtmp,crc32($str),strlen($str));

				if (empty($cr)) {
					$cr = $crtmp;
					$leng = $len;
				} else {
					$cr = crc32_combine($cr,$crtmp,$len);
					$leng += $len;
				}
			}

			$total = $total - $leng;
			[$crtmp,$rest] = $this->remove_a1000000x10000(false,$total);
			[$crtmp,$rest] = $this->remove_a1000000x1000($crtmp,$rest);
			[$crtmp,$rest] = $this->remove_a1000000($crtmp,$rest);

			$i = $rest;$str = '';while ($i --) {$str .= chr(0);}
			$crtmp = crc32_combine($crtmp,crc32($str),$rest);
			$cr = crc32_combine($cr,$crtmp,$total);
			return $cr;
		}
		function hashfile($file = ''){
			$total = filesize($file);
			if ($total < ($this->a1000000 + 1)) {
				$this->__crc32_init_table();
				$base = $this->__crc32_file($file);
				return $base;
			}
			$cr = false;

			[$cr,$total] = $this->remove_a1000000x10000($cr,$total);
			[$cr,$total] = $this->remove_a1000000x1000($cr,$total);
			[$cr,$total] = $this->remove_a1000000($cr,$total);

			$i = $total;$str = '';while ($i --) {$str .= chr(0);}
			$base = crc32_combine($cr,crc32($str),$total);
			return $base;
		}
		function remove_a1000000($cr = false,$total = 0){
			/* INI-low hashes */
			$max = intval($total / $this->a1000000);
			if ($max < 1) {return [$cr,$total];}
			$rest = $total - ($max * $this->a1000000);
			if ($max > 0 && empty($cr)) {
				$cr = $this->h1000000; //hash de 1000000
				$max--;
			}
			while ($max--) {
				$cr = crc32_combine($cr,$this->h1000000,$this->a1000000);
			}
			/* END-low hashes */
			return [$cr,$rest];
		}
		function remove_a1000000x1000($cr = false,$total = 0){
			/* INI-high hashes */
			$max = intval($total / $this->a1000000x1000);
			if ($max < 1) {return [$cr,$total];}
			$rest = $total - ($max * $this->a1000000x1000);
			if ($max > 0 && empty($cr)) {
				$cr = $this->h1000000x1000;
				$max--;
			}
			while ($max--) {
				$cr = crc32_combine($cr,$this->h1000000x1000,$this->a1000000x1000);
			}
			/* END-high hashes */
			return [$cr,$rest];
		}
		function remove_a1000000x10000($cr = false,$total = 0){
			/* INI-higher hashes */
			$max = intval($total / $this->a1000000x10000);
			if ($max > 100) {fwrite(STDERR, 'añadir otro nivel'.PHP_EOL);}
			if ($max < 1) {return [$cr,$total];}
			$rest = $total - ($max * $this->a1000000x10000);
			if ($max > 0 && empty($cr)) {
				$cr = $this->h1000000x10000; //hash de 1000000
				$max--;
			}
			while ($max--) {
				$cr = crc32_combine($cr,$this->h1000000x10000,$this->a1000000x10000);
			}
			/* END-higher hashes */
			return [$cr,$rest];
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
			ksort($modifs);
			return $modifs;
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
		function __crc32_string($text) {
			$crc = 0xffffffff;
			$len = strlen($text);
			for ($i=0;$i < $len;++$i) {
				$crc = (($crc >> 8) & 0x00ffffff) ^ $this->__crc32_table[($crc & 0xFF) ^ ord($text[$i])];
			}
			return $crc ^ 0xffffffff;
		}
		function __crc32_file($name) {
			// Start out with all bits set high.
			$this->addlength = true;
			$crc = 0xffffffff;
			$total = filesize($name);
			$index = 0;

			if (($fp = fopen($name,'rb')) === false) {return false;}
			for (;;) {
				$buffer = fread($fp,1000000);
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
						$total++;
					}

					$crc = (($crc >> 8) & 0x00ffffff) ^ $this->__crc32_table[($crc & 0xFF) ^ ord($c)];

					$index++;
					$total--;
					if ($index % 10000000 == 0) {fwrite(STDERR, $total.PHP_EOL);}
				}
			}

			fclose($fp);
			// Exclusive OR the result with the beginning value.
			return $crc ^ 0xffffffff;
		}
	}

	(new _cha15())->start();


	function gf2_matrix_times($mat, $vec) {
		$i = 0;
		$sum = 0;
		while ($vec) {
			if ($vec & 1) {
				$sum ^= $mat[$i];
			}
			$vec >>= 1;
			$i++;
		}
		return $sum;
	}

	function gf2_matrix_square(&$square, &$mat) {
		for ($n = 0; $n < GF2_DIM; $n++) {
			$square[$n] = gf2_matrix_times($mat, $mat[$n]);
		}
	}

	function crc32_combine($crc1, $crc2, $len2) {
		$even = array_fill(0, GF2_DIM, 0);
		$odd = array_fill(0, GF2_DIM, 0);

		/* degenerate case (also disallow negative lengths) */
		if ($len2 <= 0) {
			return $crc1;
		}

		/* put operator for one zero bit in odd */
		$odd[0] = 0xedb88320;   /* CRC-32 polynomial */
		$row = 1;
		for ($n = 1; $n < GF2_DIM; $n++) {
			$odd[$n] = $row;
			$row <<= 1;
		}

		/* put operator for two zero bits in even */
		gf2_matrix_square($even, $odd);

		/* put operator for four zero bits in odd */
		gf2_matrix_square($odd, $even);

		/* apply len2 zeros to crc1 (first square will put the operator for one
		 zero byte, eight zero bits, in even) */
		do {
			/* apply zeros operator for this bit of len2 */
			gf2_matrix_square($even, $odd);
			if ($len2 & 1) {
				$crc1 = gf2_matrix_times($even, $crc1);
			}
			$len2 >>= 1;

			/* if no more bits set, then done */
			if ($len2 == 0) {
				break;
			}

			/* another iteration of the loop with odd and even swapped */
			gf2_matrix_square($odd, $even);
			if ($len2 & 1) {
				$crc1 = gf2_matrix_times($odd, $crc1);
			}
			$len2 >>= 1;

			/* if no more bits set, then done */
		} while ($len2 != 0);

		/* return combined crc */
		$crc1 ^= $crc2;
		return $crc1;
	}

