#!/usr/bin/php
<?php
	class _cha06{
		function start(){
			$data = [];
			$this->_map  = new _map();
			$this->_conn = new _conn();

			if (file_exists('map.json')) {
				$this->_map->map = json_decode(file_get_contents('map.json'),true);
				foreach ($this->_map->map as $y=>$xs) {
					if ($y < $this->_map->bounds['t']) {$this->_map->bounds['t'] = $y;}
					if ($y > $this->_map->bounds['b']) {$this->_map->bounds['b'] = $y;}
					foreach ($xs as $x=>$loc) {
						if ($x < $this->_map->bounds['l']) {$this->_map->bounds['l'] = $x;}
						if ($x > $this->_map->bounds['r']) {$this->_map->bounds['r'] = $x;}
						if ($loc == 'K') {$this->_map->map[$y][$x] = '.';}
					}
				}
				$this->_map->map[2][2] = 'K';
			}

			$this->_conn->connect();
			$data = $this->_conn->read();
			$this->_map->parse($data);

			$this->search();
		}
		function command(string $comm){
			echo 'command: '.$comm.PHP_EOL;

			$padx = 0;
			$pady = 0;
			if (strpos($comm,'2U') !== false) {$pady = -2;}
			if (strpos($comm,'2D') !== false) {$pady = +2;}
			if (strpos($comm,'1U') !== false) {$pady = -1;}
			if (strpos($comm,'1D') !== false) {$pady = +1;}

			if (strpos($comm,'2L') !== false) {$padx = -2;}
			if (strpos($comm,'2R') !== false) {$padx = +2;}
			if (strpos($comm,'1L') !== false) {$padx = -1;}
			if (strpos($comm,'1R') !== false) {$padx = +1;}

			file_put_contents('map.json',json_encode($this->_map->map));
			$r = $this->_conn->write($comm);
			if ($r === false) {exit;}
			$data = $this->_conn->read();
			print_r($data);
			if (strpos($data,'Oops! Invalid command') !== false) {
				echo 'Oops! Invalid command'.PHP_EOL;
				return false;
			}
			$this->_map->parse($data,$padx,$pady);
		}
		function search(){
			$map = $this->_map->map;
			foreach ($map as $y=>$xs) {
				foreach ($xs as $x=>$loc) {
					if ($this->_map->canExplore($map,$x,$y)) {
						$map[$y][$x] = 'Z';
					}
				}
			}

			$this->_map->paint($map,true);

			$_dwarfs = new _dwarfs();
			$_dwarfs->matrix = $map;
			$_dwarfs->target = 'Z';
			$test = $_dwarfs->start();

			$startX = $_dwarfs->startX;
			$startY = $_dwarfs->startY;
			$convY = ['-1'=>'1U','1'=>'1D','-2'=>'2U','2'=>'2D'];
			$convX = ['-1'=>'1L','1'=>'1R','-2'=>'2L','2'=>'2R'];
			$command = '';
			foreach ($test as $move) {
				$moveX = $move['x'] - $startX;
				$moveY = $move['y'] - $startY;

				$comm = $convY[$moveY].$convX[$moveX];
				$this->command($comm);

				$startX = $move['x'];
				$startY = $move['y'];
			}

			$_dwarfs = new _dwarfs();
			$_dwarfs->matrix = $this->_map->map;
			$_dwarfs->target = 'P';
			$test = $_dwarfs->start();
			if (!$_dwarfs->found) {
				return $this->search();
			}

			foreach ($test as $move) {
				$moveX = $move['x'] - $startX;
				$moveY = $move['y'] - $startY;

				$comm = $convY[$moveY].$convX[$moveX];
				$this->command($comm);

				$startX = $move['x'];
				$startY = $move['y'];
			}
			echo 'END';exit;
		}
	}

	class _map{
		public $map = [];
		public $visited = [];
		public $bounds = ['l'=>0,'r'=>0,'t'=>0,'b'=>0];
		public $padx = 0;
		public $pady = 0;
		function parse($lines = '',int $x = 0,int $y = 0) {
			if (is_string($lines)) {
				$lines = preg_replace('![\n]+$!','',$lines);
				$lines = explode(PHP_EOL,$lines);
			}

			foreach ($lines as $k=>$line) {
				if (empty($line)) {continue;}
				if ($line[0] == '-') {continue;}
				$line = str_split(trim($line));
				foreach ($line as $j=>$value) {
					$ay = $k + $y + $this->pady;
					$ax = $j + $x + $this->padx;
					if ($ax < $this->bounds['l']) {$this->bounds['l'] = $ax;}
					if ($ax > $this->bounds['r']) {$this->bounds['r'] = $ax;}
					if ($ay < $this->bounds['t']) {$this->bounds['t'] = $ay;}
					if ($ay > $this->bounds['b']) {$this->bounds['b'] = $ay;}
					$this->map[$ay][$ax] = $value;
				}
			}

			$this->pady += $y;
			$this->padx += $x;
		}
		function canExplore($map = [],int $x,int $y){
			if ($map[$y][$x] != '.') {return false;}

			if (!isset($map[$y - 2][$x - 1])
			 || !isset($map[$y - 2][$x + 1])
			 || !isset($map[$y - 1][$x + 2])
			 || !isset($map[$y + 1][$x + 2])
			 || !isset($map[$y + 2][$x + 1])
			 || !isset($map[$y + 2][$x - 1])
			 || !isset($map[$y + 1][$x - 2])
			 || !isset($map[$y - 1][$x - 2])) {
				return true;
			}
			return false;
		}
		function paint($map = [],$render = false){
			if ($render) {ob_start();}
			if (empty($map)) {$map = $this->map;}
			$yrange = range($this->bounds['t'],$this->bounds['b']);
			$xrange = range($this->bounds['l'],$this->bounds['r']);
			echo '┼';
			foreach ($xrange as $x) {
				echo '───┼';
			}
			echo PHP_EOL;
			foreach ($yrange as $y) {
				echo '|';
				foreach ($xrange as $x) {
					echo ' '.($map[$y][$x] ?? ' ').' |';
				}
				echo PHP_EOL;
				echo '|';
				foreach ($xrange as $x) {
					echo '───┼';
				}
				echo PHP_EOL;
			}

			if ($render) {
				$render = ob_get_contents();
				ob_end_clean();
				file_put_contents('map.txt',$render);
				file_put_contents('map.json',json_encode($this->map));
			}
		}
	}

	class _dwarfs{
		public $matrix = [];
		public $places = [];
		public $found  = false;
		public $path   = [];
		public $startX = false;
		public $startY = false;
		public $start  = 'K';
		public $target = 'P';
		function findPos(){
			$this->startX = $this->startY = false;
			foreach ($this->matrix as $y=>$row) {
				if (($x = array_search($this->start,$row)) !== false) {
					$this->startX = $x;
					$this->startY = $y;
					return true;
				}
			}
			return false;
		}
		function findTarget(){
			$this->targetX = $this->targetY = false;
			foreach ($this->matrix as $y=>$row) {
				if (($x = array_search($this->target,$row)) !== false) {
					$this->targetX = $x;
					$this->targetY = $y;
					return true;
				}
			}
			return false;
		}
		function getPos(){
			return ['x'=>$this->startX,'y'=>$this->startY];
		}
		function start(){
			$this->found  = false;
			$this->path   = [];
			$this->places = [];
			$r = $this->findPos();
			if (!$r) {echo 'Player not found';exit;}
			$this->findTarget();
			$this->move($this->startX + 2,$this->startY + 1);
			$this->move($this->startX - 2,$this->startY + 1);

			$this->move($this->startX + 2,$this->startY - 1);
			$this->move($this->startX - 2,$this->startY - 1);

			$this->move($this->startX + 1,$this->startY + 2);
			$this->move($this->startX + 1,$this->startY - 2);

			$this->move($this->startX - 1,$this->startY + 2);
			$this->move($this->startX - 1,$this->startY - 2);
			return $this->path;
		}
		function move($x,$y,$mov = 0,$path = []){
			$mov++;
			if ($this->found && ($this->found <= $mov)) {return false;}
			if (!isset($this->matrix[$y][$x])) {return false;}
			if (isset($this->places[$y][$x]) && ($this->places[$y][$x] <= $mov)) {return false;}
			$this->places[$y][$x] = $mov;
			$path[] = ['x'=>$x,'y'=>$y];

			if ($this->matrix[$y][$x] == '#') {return false;}
			if ($this->matrix[$y][$x] == $this->start) {return false;}
			if ($this->matrix[$y][$x] == $this->target) {
				$this->path  = $path;
				//echo 'found: '.$mov.PHP_EOL;
				$this->found = $mov;
				return false;
			}

			if (!$this->found) {
				$this->movements = [];
				$this->movements[] = [$x + 2,$y + 1];
				$this->movements[] = [$x - 2,$y + 1];
				$this->movements[] = [$x + 2,$y - 1];
				$this->movements[] = [$x - 2,$y - 1];
				$this->movements[] = [$x + 1,$y + 2];
				$this->movements[] = [$x + 1,$y - 2];
				$this->movements[] = [$x - 1,$y + 2];
				$this->movements[] = [$x - 1,$y - 2];
				//$this->heuristic();

				foreach ($this->movements as $m) {
					$this->move($m[0],$m[1],$mov,$path);
				}
			} else {
				$this->move($x + 2,$y + 1,$mov,$path);
				$this->move($x - 2,$y + 1,$mov,$path);

				$this->move($x + 2,$y - 1,$mov,$path);
				$this->move($x - 2,$y - 1,$mov,$path);

				$this->move($x + 1,$y + 2,$mov,$path);
				$this->move($x + 1,$y - 2,$mov,$path);

				$this->move($x - 1,$y + 2,$mov,$path);
				$this->move($x - 1,$y - 2,$mov,$path);
			}
		}
		function heuristic(){
			foreach ($this->movements as &$mov) {
				$dx = abs($this->targetX - $mov[0]);
    				$dy = abs($this->targetY - $mov[1]);
				//$w  = $dx + $dy;
				$w = sqrt($dx * $dx + $dy * $dy);
				$mov[] = $w;
			}
			unset($mov);
			usort($this->movements,function($a,$b){
				if ($a[2] == $b[2]) {return 0;}
				return ($a[2] < $b[2]) ? -1 : 1;
			});
		}
	}

	class _conn{
		public $ip   = '52.49.91.111';
		public $port = '2003';
		public $fp   = false;
		public $cr   = "\n";
		function connect(){
			$this->fp = fsockopen($this->ip, $this->port, $errno, $errstr, 30);
			if (!$this->fp) {echo "$errstr ($errno)<br />\n";exit;}
			stream_set_blocking($this->fp,0);
        		stream_set_blocking(STDIN,0);
			usleep(150000);
		}
		function write($text = ''){
			$r = fwrite($this->fp,$text.$this->cr);
			usleep(180000);
			return $r;
		}
		function read(){
			//echo 'READ -----'.PHP_EOL;
			$blob = '';
			while( ($buffer = fgets($this->fp,128)) ){
				$blob .= $buffer;
			}
			return $blob;
		}
	}

	(new _cha06())->start();

