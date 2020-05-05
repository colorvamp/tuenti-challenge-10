#!/usr/bin/php
<?php
	class _cha18{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));
			$this->test();

			$case = 0;
			$jump = 0;
			while ($this->cases--) {
				$lines = trim(array_shift($this->lines));
				$code = '';
				while ($lines--) {
					$code .= array_shift($this->lines);
				}
				$this->code = substr($code,0,-1);
				$this->orig = $this->code;
if (false && $case == 29) {
	var_dump($this->cases);
	print_r($this->code);
	exit;
}


				$this->fix();
				if ($this->is_valid_esc()
				 && $this->is_valid_lolmao()) {
					$changes = $this->changes($this->orig,$this->code);
					//$changes = levenshtein($this->orig,$this->code);
					echo 'Case #'.++$case.': '.$changes.PHP_EOL;
					if (false && $case == 24) {
						var_dump($this->orig);
						var_dump($this->code);
						exit;//*/
					}
					continue;
				}
				echo 'Case #'.++$case.': IMPOSSIBLE'.PHP_EOL;
			}
		}
		function changes(string $orig,string $dest): int{
			if (strlen($orig) != strlen($dest)) {
				echo 'size discrepancy'.PHP_EOL;
				var_dump($orig);
				var_dump($dest);
				exit;
			}
			$len = strlen($dest);
			$changes = 0;
			for ($i = 0;$i < $len;$i++) {
				if ($orig[$i] != $dest[$i]) {$changes++;}
			}
			return $changes;
		}
		function fix(){
			$this->orig = preg_replace('![^\[\]\n\,]!','a',$this->orig);
			$this->code = preg_replace('![^\[\]\n\,]!','a',$this->code);

			$this->has_brackets = false;
			if (strpos(substr($this->code,1,-1),'[') !== false
			 || strpos(substr($this->code,1,-1),']') !== false) {
				$this->has_brackets = true;
			}
			if (strlen($this->code) < 3) {return false;}
			if (substr($this->code,-1) != ']') {$this->code[strlen($this->code) - 1] = ']';}
			if (substr($this->code,0,1) != '[') {$this->code[0] = '[';}

			/* []asd,]   -> [,asd,]
			 * [[]]asd,] -> [[],asd,]
			 * [[[]asd,] -> [[],asd,]
			 * []asd][,] -> [[asd],,] -> 2
			 * []asd],]  -> [[asd],]
			 */
			$this->code = $this->brackets_party($this->code);

			do {
				/* Solo los literales permiten saltos de linea
				 * ,[.*]\n, -> ,[.*],, */
				$this->code = preg_replace_callback('!\]\n(,|])!',function($m){
					return '],'.$m[1];
				},$this->code,1,$count);
			} while ($count);
		}
		function brackets_party(string $code){
			$pos = 0;
			$mods = $this->changes($this->orig,$code);

			$this->range = [','/*,'['*/,']'];
			//if (!$this->has_brackets) {$this->range = [','];}

			$this->mods = false;
			$this->candidate = '';
			$this->step($code[0],0,$code);
			return $this->candidate;
		}
		function step(string $char,int $pos,string $code,array $path = ['op'=>0]){
//if ($this->cases == 183) {var_dump($this->mods);}
			//$path[] = $char;
			if ($char == '[') {$path['op']++;}
			if ($char == ']') {$path['op']--;}
			if ($path['op'] < 1) {return false;}

			$code[$pos] = $char;

			if (!isset($code[$pos + 2])) {
				if ($this->is_valid_esc($code)
				 && $this->is_valid_lolmao($code,$this->has_brackets)) {
					if ($this->mods === false) {
						$this->mods = $this->changes($this->orig,$code);
						$this->candidate = $code;
					} else {
						$test = $this->changes($this->orig,$code);
						if ($test < $this->mods) {
							$this->mods = $test;
							$this->candidate = $code;
						}
					}
				}
				return false;
			}

			if ($this->mods !== false) {
				$test = $this->changes($this->orig,$code);
				if ($test >= $this->mods) {
					return false;
				}
			}

			$pos++;
			$next = $code[$pos];
			$nnxt = $code[$pos + 1];
			$is_special_next = in_array($next,[',','[',']',PHP_EOL]);

			switch (true) {
				case ($char == ']'):
					$range = [',',']'];
					break;
				case ($char == ','):
					$range = [',',']','['];
					break;
				case ($char == '['):
					$range = [',','[',']'];
					break;
				//case ():
					//break;
				default:
					$range = [',',']'];
			}

/* elseif (!$is_special_next && $nnxt == '[') {
echo 11;exit;
				$opens  = substr_count($code,'[');
				$closes = substr_count($code,']') - 1;
				if ($closes > $opens) {$range = ['['];}
				else {$range = [',','['];}
			} elseif (!$is_special_next) {
if (false) {
var_dump($next);
				$opens  = substr_count($code,'[',$pos);
				$closes = substr_count($code,']',$pos) - 1;
				if ($opens == $closes) {
					return $this->step($next,$pos,$code,$path);
				}
				if (false && $closes > $opens) {
					return $this->step('[',$pos,$code,$path);
var_dump($code);
var_dump($opens);
var_dump($closes);
exit;
}
}
			}*/
			if ($next == PHP_EOL && $char != PHP_EOL && $char != ']') {
				/* Camino de 'dejar como está' */
				$range[] = PHP_EOL;
			}

			foreach ($range as $nchar) {
				//if ($char == PHP_EOL && $nchar == PHP_EOL) {continue;}
				$this->step($nchar,$pos,$code,$path);
			}

			if ($next == 'a') {
				$this->step($next,$pos,$code,$path);
			}
		}
		function is_valid_esc(string $code = ''){
			if (empty($code)) {$code = $this->code;}

			$r = preg_match('!^([^,]{0,},)+[^,]+$!sm',$code);
			if (!$r) {return false;}

			if (strpos($code,PHP_EOL) !== false) {
				$r = preg_match('!^([^,\n]{0,},[^,\n]{0,})+'
					.'(\n([^,\n]{0,},[^,\n]{0,})+)+$!sm',$code,$m);

				$test = explode(PHP_EOL,$code);
				foreach ($test as $line) {
					if (strpos($line,',') === false) {return false;}
				}
			}

			return true;
		}
		function is_valid_lolmao(string $code = '',bool $has_brackets = true){
			if (empty($code)) {$code = $this->code;}

			if (!preg_match('!^\[([^,]{0,},)+[^,]{0,}\]$!sm',$code,$m)) {
				return false;
			}

			if (preg_match('!\][^,\]]{1}!',$code,$m)) {return false;}
			if (preg_match('![^,\[]{1}\[!',$code,$m)) {return false;}
			if (preg_match('!\]\[!sm',$code,$m)) {return false;}

			/* Brackets */
			if ($has_brackets) {
				$open = 0;
				$_open = '[';
				$_close = ']';
				$len = strlen($code);
				for ($i = 0;$i < $len;$i++) {
					if ($code[$i] == $_open) {$open++;}
					if ($code[$i] == $_close) {$open--;}
					if ($i < ($len - 1) && $open === 0) {
						//var_dump($open.' - '.$i.' - '.$len);
						return false;
					}
				}
				if ($open !== 0) {return false;}
			}

			return true;
		}

		function test(){
			$this->orig = $this->code = "]5[b3,]][]";
			$this->fix();
			if ($this->code != "[[[aa,]],]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "al\n9h";
			$this->fix();
			if ($this->code != "[a,a]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[\n\n\n,,\n\n,]";
			$this->fix();
			if ($this->code != "[,,\n,,,\n,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = ",]9f\n5,,\n,\n,[],";
			$this->fix();
			if ($this->code != "[,aa\na,,\n,\n,[]]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[][asd,]";
			$this->fix();
			if ($this->code != "[,,aaa,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[][]],[,][["; // [[[]],[,],] 
			$this->fix();
			if ($this->code != "[[[]],[,],]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "]][,],[[][]"; // [,[,],[[]]]
			$this->fix();
			if ($this->code != "[,[,],[[]]]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[m\n,]";
			$this->fix();
			if ($this->code != "[,\n,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[\n,]";
			$this->fix();
			if ($this->code != "[,,]") {echo __LINE__.PHP_EOL;exit;}

			$this->code = "[,asd\n,\n,]";
			if (!$this->is_valid_esc($this->code)) {echo __LINE__.PHP_EOL;exit;}
			$this->code = "[,asd\n\n,]";
			if ($this->is_valid_esc($this->code)) {echo __LINE__.PHP_EOL;exit;}

			if (!$this->is_valid_lolmao('[foo,bar]')) {echo __LINE__.PHP_EOL;exit;}
			if ($this->is_valid_lolmao('[[,u7[],],]')) {echo __LINE__.PHP_EOL;exit;}

			$this->code = '[[],foo,[[[,],[1,2],5,,],some0literal'.PHP_EOL
				.'with3multiple'.PHP_EOL
				.'lines]]';
			if (!$this->is_valid_lolmao($this->code)) {echo __LINE__.PHP_EOL;exit;}
		}
	}

	(new _cha18())->start();


