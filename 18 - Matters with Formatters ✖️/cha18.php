#!/usr/bin/php
<?php
	class _cha18{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));
//var_dump($this->countMinReversals('[[][]]][][][]][]'));exit;

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
if (false && $case == 15) {
	var_dump($this->cases);
	print_r(str_replace([PHP_EOL],['@'],$this->code));
	exit;
}


				$this->fix($this->code);
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
		function fix(string $code){
//return $this->direct($this->orig);
			$this->orig = $this->code = $code;
			$this->orig = preg_replace('![^\[\]\n\,\@]!','a',$this->orig);
			$this->code = preg_replace('![^\[\]\n\,\@]!','a',$this->code);

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

			$this->range = [','/*,'['*/,']'];
			//if (!$this->has_brackets) {$this->range = [','];}
var_dump(str_replace(PHP_EOL,'@',$this->orig));

			$this->mods = false;
			$this->candidate = '';
			$this->step($code[0],0,$code,[
				 'op'=>0
				,'eq'=>substr_count($code,'[') - substr_count($code,']')
				,'mx'=>substr_count($code,'[') + substr_count($code,']')
				,'comma'=>false
			]);

var_dump(str_replace(PHP_EOL,'@',$this->candidate));
var_dump($this->mods);//*/
			return $this->candidate;
		}
		function is_char_ok($pos,$code,$path){
			if (!$path['comma'] && strpos($code,',',$pos) === false) {return false;}

			if ($code[$pos] == ',') {return true;}
			if ($code[$pos] == PHP_EOL
			 || $code[$pos] == '@') {
				if (!$path['comma']) {return false;}
				if ($code[$pos - 1] == PHP_EOL
				 || $code[$pos + 1] == PHP_EOL
				 || $code[$pos - 1] == '@'
				 || $code[$pos + 1] == '@'
				 || $code[$pos - 1] == ']'
				 || $code[$pos + 1] == '[') {return false;}
				if (strpos($code,',',$pos) === false) {return false;}
				return true;
			}
			if ($code[$pos] == 'a') {
				if ($code[$pos + 1] == '['
				 || $code[$pos - 1] == ']') {return false;}
				return true;
			}
			if ($code[$pos] == '[') {
				if ($code[$pos - 1] == PHP_EOL
				 || $code[$pos - 1] == '@'
				 || $code[$pos - 1] == 'a'
				 || $code[$pos - 1] == ']') {return false;}
				if ($path['eq'] != 0) {return false;}
				return true;
			}
			if ($code[$pos] == ']') {
				if ($code[$pos + 1] == PHP_EOL
				 || $code[$pos + 1] == '@'
				 || $code[$pos + 1] == 'a'
				 || $code[$pos + 1] == '[') {return false;}
				if ($path['eq'] != 0) {return false;}
				if ($path['op'] < 2) {return false;}
				return true;
			}
		}
		function step(string $char,int $pos,string $code,array $path = ['op'=>0]){
			if ($char == '[') {$path['op']++;}
			if ($char == ']') {$path['op']--;}
			if ($path['op'] < 1) {return false;}
			if ($path['op'] > ($path['mx'] / 2)) {return false;}
			if ($pos > strlen($code) - $path['eq']) {
				/* Si el deficit es mayor que los caracteres que nos quedan 
				 * para compensar */
				return false;
			}

			if ($char == PHP_EOL) {$path['comma'] = false;}
			if ($char == ',') {$path['comma'] = true;}
			$code[$pos] = $char;

			if (!isset($code[$pos + 2])) {
				if ($this->is_valid_esc($code)
				 && $this->is_valid_lolmao($code,$this->has_brackets)) {
					if ($this->mods === false) {
						$this->mods = $this->changes($this->orig,$code);
//var_dump($this->mods);
//var_dump(str_replace(PHP_EOL,'@',$code));//*/
						$this->candidate = $code;
					} else {
						$test = $this->changes($this->orig,$code);
						if ($test < $this->mods) {
							$this->mods = $test;
//var_dump($this->mods);
							$this->candidate = $code;
						}
					}
				}
				return false;
			}

			if ($this->mods !== false) {
//$t1 = $this->changes(substr($this->orig,0,$pos),substr($code,0,$pos));
//$t2 = $this->changes(substr($this->orig,0,$pos),substr($this->candidate,0,$pos));
//if ($t1 > $t2) {return false;}
				$test = $this->changes($this->orig,$code);
				if ($test >= $this->mods) {
					return false;
				}
			}

			$pos++;
			$next = $code[$pos];
			$nnxt = $code[$pos + 1];

			$r = $this->is_char_ok($pos,$code,$path);
			if ($r === true) {return $this->step($next,$pos,$code,$path);}

			switch (true) {
				case ($next == ',' && $nnxt != PHP_EOL && ($pos < strlen($code) - $path['eq'])):
					return $this->step($next,$pos,$code,$path);
					break;
				case ($next == 'a' && $nnxt == '['):
					/* Este está mal, pero es una oportunidad fantástica 
					 * de compensar un futuro bracket for free */
					if ($path['eq'] < 0) {
						/* Si el equilibrio es menor se necesitan cierres */
						$next = '|';
						$range = ['['];
					} else {
						$range = ['[',','];
					}
					break;
				case ($char == ']'):
					if ($path['op'] < 2) {
						return $this->step(',',$pos,$code,$path);
					}
					$range = [']',','];
					break;
				case ($char == ','):
					$range = [',',']','['];
					break;
				case ($char == '['):
					$range = ['[',']',','];
					break;
				default:
					$range = [',',']'];
			}

			if ($next == PHP_EOL && $char != PHP_EOL && $char != ']' && $path['comma']) {
				/* Camino de 'dejar como está' */
				array_unshift($range,PHP_EOL);
			}
			if ($next == '@' && $char != '@' && $char != ']' && $path['comma']) {
				/* Camino de 'dejar como está' */
				array_unshift($range,'@');
			}
			if ($next == 'a') {
				array_unshift($range,'a');
			}


if (false) {
			$other = [];
			foreach ($range as $nchar) {
				$tmp = $code;
				$tmp[$pos] = $nchar;
				$d = $this->direct_cost($tmp) + $this->changes($this->orig,$tmp);
				if ($d == -1) {$d = 10;continue;}
				$other[$nchar] = $d;
			}

			asort($other);
			foreach ($other as $nchar=>$cost) {
				$npath = $path;
				if ($next == '[' && $nchar != '[') {$npath['eq']--;$npath['mx']--;}
				if ($next == ']' && $nchar != ']') {$npath['eq']++;$npath['mx']--;}
				if ($next != '[' && $nchar == '[') {$npath['eq']++;$npath['mx']++;}
				if ($next != ']' && $nchar == ']') {$npath['eq']--;$npath['mx']++;}
				$this->step($nchar,$pos,$code,$npath);
			}
return false;
}

			foreach ($range as $nchar) {
				$npath = $path;
				if ($next == '[' && $nchar != '[') {$npath['eq']--;$npath['mx']--;}
				if ($next == ']' && $nchar != ']') {$npath['eq']++;$npath['mx']--;}
				if ($next != '[' && $nchar == '[') {$npath['eq']++;$npath['mx']++;}
				if ($next != ']' && $nchar == ']') {$npath['eq']--;$npath['mx']++;}
				$this->step($nchar,$pos,$code,$npath);
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
		function direct_cost(string $code){
			$test = $this->countMinReversals($code);
			if ($test < 0) {
				$test = $this->countMinReversals($code.']');
			}
			return $test;
		}
		function direct(string $code){
			$this->orig = $this->code = $code;
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

			$this->test = $this->code;
			$mods = 1;
			for ($i = 1;$i < strlen($this->test) - 1;$i++) {
				$char = $this->test[$i];
				//if ($char == '[' || $char == ']') {continue;}
				$segment = strrpos($this->test,'@',$i);
				$segment = substr($this->test,$segment,$i);
				$comma = strrpos($segment,',') !== false;

				$op = substr_count($this->test,'[',0,$i) - substr_count($this->test,']',1,$i);
				$eq = substr_count($this->test,'[') + substr_count($this->test,']');
				if ($this->is_char_ok($i,$this->test,['op'=>$op,'eq'=>0,'comma'=>$comma])) {continue;}
				//$this->test[$i] = ',';
				//continue;

				$copy = $this->test;
				$rank = [];

				$test1 = $this->direct_cost($copy);
				$rank[$i.'-'.$copy[$i]] = $test1;

				$copy[$i] = $char == '[' ? ']' : '[';
				$test2 = $this->direct_cost($copy) * $this->changes($this->orig,$copy);
				$rank[$i.'-'.$copy[$i]] = $test2;

				$copy[$i] = ',';
				$test3 = $this->direct_cost($copy) * $this->changes($this->orig,$copy);
				$rank[$i.'-'.$copy[$i]] = $test3;

				$copy = $this->test;
				foreach (['[',']',','] as $try) {
					$copy[$i - 1] = $try;
					$test4 = $this->direct_cost($copy) * $this->changes($this->orig,$copy);
					$rank[($i - 1).'-'.$try] = $test3;
				}

				if ($i < strlen($this->test) - 1) {
					$copy = $this->test;
					foreach (['[',']',','] as $try) {
						$copy[$i + 1] = $try;
						$test4 = $this->direct_cost($copy) * $this->changes($this->orig,$copy);
						$rank[($i + 1).'-'.$try] = $test3;
					}
				}

if (false) {
print_r($rank);
var_dump(substr($this->test,0,$i + 2));
var_dump($i);
var_dump($char);
}

				asort($rank);
				reset($rank);
				[$pos,$key] = explode('-',key($rank));
				$copy[$pos] = $key;


				$this->test = $copy;

			}
//7
//Case #17: 9
//var_dump($this->test);
			//echo $this->countMinReversals($this->test).PHP_EOL;
$this->code = $this->test;
var_dump($this->orig);
var_dump($this->code);
exit;
			return $this->changes($this->orig,$this->test);
exit;
		}
		function test(){
return false;
			$this->direct(",@,[]@,,a[],],],@,a,[@,@][,@,]@,,,,@,[]a]aa[]");

			$this->orig = $this->code = "[\n\n]";
			$this->fix($this->code);
			if ($this->code != "[,,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[,[],[\n\n\n]]";
			$this->fix($this->code);
			if ($this->code != "[,[],[\n,,]]") {echo __LINE__.PHP_EOL;exit;}


			$this->orig = $this->code = "[[\n,[,],\n\n\n],,f]";
			$this->fix($this->code);
			if ($this->code != "[[,,[,],\n,\n],,a]") {echo __LINE__.PHP_EOL;exit;}
/*print_r($this->code);
print_r($this->changes($this->orig,$this->code));
exit;//*/

			$this->orig = $this->code = "]5[b3,]][]";
			$this->fix($this->code);
			if ($this->code != "[[[aa,]],]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "]x,[,]]]]r[n][";
			$this->fix($this->code);
			if ($this->code != "[[,[,[]]],[a]]"
			 && $this->code != "[a,[,,[]],[a]]") {echo __LINE__.PHP_EOL;exit;}


			$this->orig = $this->code = "[,,,,,6[,,,,]";
			$this->fix($this->code);
			if ($this->code != "[,,,,,a,,,,,]") {echo __LINE__.PHP_EOL;exit;}


			$this->orig = $this->code = ",,6[,,o";
			$this->fix($this->code);
			if ($this->code != "[,a,,,]") {echo __LINE__.PHP_EOL;exit;}


			$this->orig = $this->code = "al\n9h";
			$this->fix($this->code);
			if ($this->code != "[a,a]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[\n\n\n,,\n\n,]";
			$this->fix($this->code);
			//if ($this->code != "[,\n,,,\n,,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = ",]9f\n5,,\n,\n,[],";
			$this->fix($this->code);
			if ($this->code != "[,aa\na,,\n,\n,[]]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[][asd,]";
			$this->fix($this->code);
			if ($this->code != "[,,aaa,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[][]],[,][["; // [[[]],[,],] 
			$this->fix($this->code);
			if ($this->code != "[[[]],[,],]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "]][,],[[][]"; // [,[,],[[]]]
			$this->fix($this->code);
			if ($this->code != "[,[,],[[]]]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[m\n,]";
			$this->fix($this->code);
			if ($this->code != "[,\n,]"
			 && $this->code != "[a,,]") {echo __LINE__.PHP_EOL;exit;}

			$this->orig = $this->code = "[\n,]";
			$this->fix($this->code);
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
		function countMinReversals(string $expr) {
			$expr = preg_replace('![^\]\[]*!','',$expr);
			$len = strlen($expr);
  
			// length of expression must be even to make 
			// it balanced by using reversals. 
			if ($len % 2) {return -1;}
 
			// After this loop, stack contains unbalanced 
			// part of expression, i.e., expression of the 
			// form "}}..}{{..{" 
			$s = [];
			for ($i = 0; $i < $len; $i++) {
				if ($expr[$i] == ']' && !empty($s)) {
					if (reset($s) == '[') {
						array_pop($s);
					} else {
						$s[] = $expr[$i];
					}
				} else {
					$s[] = $expr[$i];
				}
    			} 
  
			// Length of the reduced expression 
			// red_len = (m+n) 
			$red_len = count($s);

			// count opening brackets at the end of 
			// stack 
			$n = 0;
			while (!empty($s) && reset($s) == '[') {
				array_pop($s);
				$n++;
			}
  
			// return ceil(m/2) + ceil(n/2) which is 
			// actually equal to (m+n)/2 + n%2 when 
			// m+n is even. 
			return intval($red_len / 2 + $n % 2);
		}
	}

	(new _cha18())->start();


