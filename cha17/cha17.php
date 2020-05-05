#!/usr/bin/php
<?php
	class _cha17{
		function start(){
			$this->reorder();
			$data = shell_exec('zsteg zatoichi2.png -a');
			preg_match('!(?<pos>[^ ]+)[ ]+\.\. text: "CONGRATULATIONS, YOU FOUND THE HIDDEN MESSAGE, WILL YOU BE ABLE TO DECODE IT\? (?<msg>[^\"]+)!',$data,$m);
			$data = shell_exec('zsteg -E "'.trim($m['pos']).'" zatoichi2.png');
			preg_match('!CONGRATULATIONS, YOU FOUND THE HIDDEN MESSAGE, WILL YOU BE ABLE TO DECODE IT\? (?<msg>[^\"\n]+)!',$data,$m);
			$data = $this->hexascii(trim($m['msg']));
			// $data = '⠉⠕⠝⠛⠗⠁⠞⠥⠇⠁⠞⠊⠕⠝⠎ ⠽⠕⠥ ⠙⠑⠉⠕⠙⠑⠙ ⠞⠓⠑ ⠍⠑⠎⠎⠁⠛⠑ ⠞⠓⠑ ⠏⠁⠎⠎⠺⠕⠗⠙ ⠊⠎ ⠞⠓⠑ ⠋⠕⠇⠇⠕⠺⠊⠝⠛ ⠺⠕⠗⠙ ⠊⠝ ⠥⠏⠏⠑⠗⠉⠁⠎⠑ ⠞⠁⠅⠑⠎⠓⠊⠅⠊⠞⠁⠝⠕⠃⠊⠞⠕⠞⠁⠅⠑⠎⠓⠊⠓⠥⠍⠕⠗⠁⠍⠁⠗⠊⠇⠇⠕⠼⠃⠼⠚⠼⠃⠼⠚⠞⠥⠑⠝⠞⠊⠉⠓⠁⠇⠇⠑⠝⠛⠑⠼⠁⠼⠚';
			echo 'CONGRATULATIONS YOU DECODED THE MESSAGE THE PASSWORD IS THE FOLLOWING WORD IN UPPERCASE TAKESHIKITANOBITOTAKESHIHUMORAMARILLO2020TUENTICHALLENGE10';
			//$data = 'E2A089E2A095E2A09DE2A09BE2A097E2A081E2A09EE2A0A5E2A087E2A081E2A09EE2A08AE2A095E2A09DE2A08E20E2A0BDE2A095E2A0A520E2A099E2A091E2A089E2A095E2A099E2A091E2A09920E2A09EE2A093E2A09120E2A08DE2A091E2A08EE2A08EE2A081E2A09BE2A09120E2A09EE2A093E2A09120E2A08FE2A081E2A08EE2A08EE2A0BAE2A095E2A097E2A09920E2A08AE2A08E20E2A09EE2A093E2A09120E2A08BE2A095E2A087E2A087E2A095E2A0BAE2A08AE2A09DE2A09B20E2A0BAE2A095E2A097E2A09920E2A08AE2A09D20E2A0A5E2A08FE2A08FE2A091E2A097E2A089E2A081E2A08EE2A09120E2A09EE2A081E2A085E2A091E2A08EE2A093E2A08AE2A085E2A08AE2A09EE2A081E2A09DE2A095E2A083E2A08AE2A09EE2A095E2A09EE2A081E2A085E2A091E2A08EE2A093E2A08AE2A093E2A0A5E2A08DE2A095E2A097E2A081E2A08DE2A081E2A097E2A08AE2A087E2A087E2A095E2A0BCE2A083E2A0BCE2A09AE2A0BCE2A083E2A0BCE2A09AE2A09EE2A0A5E2A091E2A09DE2A09EE2A08AE2A089E2A093E2A081E2A087E2A087E2A091E2A09DE2A09BE2A091E2A0BCE2A081E2A0BCE2A09A';
			//var_dump($this->hexascii($data));
			//$resp = strtoupper('TAKESHIKITANOBITOTAKESHIHUMORAMARILLO2020TUENTICHALLENGE10');
		}
		function hexascii(string $str){
			$chunks = str_split($str,2);
			$asc = '';
			foreach ($chunks as $chunk) {
				$asc .= chr(hexdec($chunk));
			}
			return $asc;
		}
		function reorder() {
			$image = imagecreatefrompng('zatoichi.png');
			$cropped01 = imagecrop($image,['x'=>0,'y'=>0,'width'=>150,'height'=>300]);
			$cropped02 = imagecrop($image,['x'=>150,'y'=>0,'width'=>150,'height'=>300]);
			$cropped03 = imagecrop($image,['x'=>300,'y'=>0,'width'=>150,'height'=>300]);

			$cropped04 = imagecrop($image,['x'=>0,'y'=>300,'width'=>150,'height'=>300]);
			$cropped05 = imagecrop($image,['x'=>150,'y'=>300,'width'=>150,'height'=>300]);
			$cropped06 = imagecrop($image,['x'=>300,'y'=>300,'width'=>150,'height'=>300]);

			imagecopy($image,$cropped05,0,0,0,0,150,300);
			imagecopy($image,$cropped04,150,0,0,0,150,300);
			imagecopy($image,$cropped01,300,0,0,0,150,300);

			imagecopy($image,$cropped03,0,300,0,0,150,300);
			imagecopy($image,$cropped06,150,300,0,0,150,300);
			imagecopy($image,$cropped02,300,300,0,0,150,300);
			imagepng($image,'zatoichi2.png');
		}
	}

	(new _cha17())->start();


