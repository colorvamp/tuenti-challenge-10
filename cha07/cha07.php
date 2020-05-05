#!/usr/bin/php
<?php
	class _cha07{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$case = 0;
			while ($this->cases--) {
				$this->line = str_replace([PHP_EOL],[''],array_shift($this->lines));

				echo 'Case #'.(++$case).': '.$this->convertDvorakQwerty($this->line).PHP_EOL;
			}
		}
		function convertDvorakQwerty($strConvert){
			$qwerty = "-=qwertyuiop[]asdfghjkl;'zxcvbnm,./_+QWERTYUIOP{}ASDFGHJKL:\"ZXCVBNM<>?";
			$dvorak = "[]',.pyfgcrl/=aoeuidhtns-;qjkxbmwvz{}\"<>PYFGCRL?+AOEUIDHTNS_:QJKXBMWVZ";
			$conv = '';

			return $this->prc($qwerty,$dvorak,$strConvert);
		}
		function prc($strA,$strB,$strConvert) {
			$i = 0;
			$ret = '';

			for ($i = 0; $i < strlen($strConvert); $i++) {
				if (strpos($strB,$strConvert[$i]) !== false) {
					$ret .= $strA[strpos($strB,$strConvert[$i])];
				} else {
					$ret .= $strConvert[$i];
				}
			}
			return $ret;
		}
	}

	(new _cha07())->start();

