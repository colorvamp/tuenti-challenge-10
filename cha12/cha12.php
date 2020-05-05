#!/usr/bin/php
<?php
	class _cha12{
		public $n = '';
		public $e = '65537';
		function start(){
			$test1 = file_get_contents('testdata/ciphered/test1.txt');
			$test2 = file_get_contents('testdata/ciphered/test2.txt');
			$enc1 = $this->bchexdec($this->asciihex($test1));
			$enc2 = $this->bchexdec($this->asciihex($test2));

			$plain1 = file_get_contents('testdata/plaintexts/test1.txt');
			$plain2 = file_get_contents('testdata/plaintexts/test2.txt');
			$pla1 = $this->bchexdec($this->asciihex($plain1));
			$pla2 = $this->bchexdec($this->asciihex($plain2));
			$param1 = bcsub(bcpow($pla1,$this->e),$enc1);
			$param2 = bcsub(bcpow($pla2,$this->e),$enc2);

			$mod = $this->bcgcd($param1,$param2);
//var_dump($mod);
exit;
		}
		function bcgcd($a,$b){
			if ($b == 0) {return $a;}
			return $this->bcgcd($b, bcmod($a, $b));
		}
		function asciihex(string $str){
			$hex = '';
			for ($i = 0; $i < strlen($str); $i++) {
				$byte = strtoupper(dechex(ord($str[$i])));
				$byte = str_repeat('0', 2 - strlen($byte)).$byte;
				$hex .= $byte;
			}
			return $hex;
		}
		function hexascii(string $str){
			$chunks = str_split($str,2);
			$asc = '';
			foreach ($chunks as $chunk) {
				$asc .= chr(hexdec($chunk));
			}
			return $asc;
		}
		function bchexdec($hex){
			$dec = '0';
			$len = strlen($hex);
			for ($i = 1; $i <= $len; $i++) {
				$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
			}
			return $dec;
		}
		function bcdechex($dec) {
			$last = bcmod($dec, '16');
			$remain = bcdiv(bcsub($dec, $last), '16');

			if ($remain == 0) {
				return dechex($last);
			} else {
				return strtoupper($this->bcdechex($remain).dechex($last));
			}
		}
		function encrypt($data) {
			//openssl_public_decrypt($data,$enc,$this->key,OPENSSL_NO_PADDING);
			//var_dump($this->asciihex($enc));

			$tmp = $this->bchexdec($this->asciihex($data));
			$tmp = bcpowmod($tmp,$this->e,$this->n);
			return $tmp;
		}
		function getpkey($n,$e){
			$data = shell_exec('python key.py "'.$n.'" "'.$e.'"');
			$this->key = trim($data);
		}
	}

	(new _cha12())->start();

