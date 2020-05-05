#!/usr/bin/php
<?php
	class _cha09{
		function start(){
			$msg = '3A3A333A333137393D39313C3C3634333431353A37363D';
			$dcrp = $this->decrypt('40614178165780923111223',$msg);
			echo $dcrp;
			exit;

			$crpt = $this->encrypt('40614178165780923111223','514;248;980;347;145;332');
			/* Obtain the key */
			$crpt = '3633363A33353B393038383C363236333635313A353336';
			$msg  = '514;248;980;347;145;332';
			$this->getKey($crpt,$msg);
		}
		function decrypt($key = '',$msg = ''){
			$msg_chunks = str_split($msg,2);
			$asc = '';
			foreach ($msg_chunks as $chunk) {
				$asc .= chr(hexdec($chunk));
			}

			$dcrp = $this->encrypt('40614178165780923111223',$asc);

			$msg_chunks = str_split($dcrp,2);
			$asc = '';
			foreach ($msg_chunks as $chunk) {
				$asc .= chr(hexdec($chunk));
			}
			return $asc;
		}
		function getKey($crpt = '',$msg = ''){
			$key = '00000000000000000000000';
			$nums = range(0,9);
			$crpt_chunks = str_split($crpt,2);

			$crpt_msg = '';
			for ($i = 0; $i < strlen($msg); $i++) {
				$c = $msg[$i]; // ${msg:$i:1}
				$asc_chr = ord($c); // $asc_chr = shell_exec('echo -ne "$c" | od -An -tuC');
				$key_pos = strlen($key) - 1 - $i; // key_pos=$((${#key} - 1 - ${i}))
				$found = false;
				foreach ($nums as $num) {
					$crpt_chr = $asc_chr ^ $num; // crpt_chr=$(( $asc_chr ^ ${key_char} ))
					$hx_crpt_chr = strtoupper(dechex($crpt_chr));
					if ($hx_crpt_chr != $crpt_chunks[$i]) {continue;}
					$key[$key_pos] = $num;
					$crpt_msg .= $hx_crpt_chr;
					$found = true;
					break;
				}
			}
			echo 'key: '.$key.PHP_EOL;
			echo 'msg: '.$crpt_msg.PHP_EOL;
			exit;
		}
		function encrypt($key = '',$msg = ''){
			$crpt_msg = '';
			for ($i = 0; $i < strlen($msg); $i++) {
				$c = $msg[$i]; // ${msg:$i:1}
				$asc_chr = ord($c); // $asc_chr = shell_exec('echo -ne "$c" | od -An -tuC');
				$key_pos = strlen($key) - 1 - $i; // key_pos=$((${#key} - 1 - ${i}))
				$key_char = $key[$key_pos]; // key_char=${key:$key_pos:1}
				$crpt_chr = $asc_chr ^ $key_char; // crpt_chr=$(( $asc_chr ^ ${key_char} ))
				$hx_crpt_chr = strtoupper(dechex($crpt_chr));
				$crpt_msg .= $hx_crpt_chr;
			}
			return $crpt_msg;
		}
	}

	(new _cha09())->start();

