#!/usr/bin/php
<?php
	class _cha14{
		public $count = 1000;
		public $servers = [];
		public $myservers = [];
		public $whoami = '';
		function start(){
			$this->_conn = new _conn();
			$this->_conn->connect();
			$this->start = false;
			while (true) {
				$data = $this->_conn->read();
				usleep(8000);

				$data = preg_replace_callback('!SERVER ID: (?<me>[0-9]+)!',function($n){
					$this->whoami = $n['me'];
					$this->count = $this->whoami * 1000;
					$this->start = true;
					return $n[0];
				},$data);
				if (strpos($data,'AUTOJOIN CLUSTER ENABLED') !== false) {
					//$this->start = true;
				}
				if ($this->start) {
					$data = preg_replace_callback('!ROUND [0-9]+: [0-9]+ \-> LEARN \{servers: (?<servers>\[[0-9,]+\]), secret_owner: (?<master>[0-9]+)\} \(ROUND FINISHED\)!',function($n){
						$this->master = intval($n['master']);
						if ($this->master == 9) {$this->end = true;}
						$this->aservers = $n['servers'];

						$this->servers = json_decode($n['servers'],true);
						$diff = array_diff($this->myservers,$this->servers);
						if ($diff) {
							$this->sent = [];
							foreach ($this->servers as $server) {
								if ($server == 9) {continue;}
								if (isset($this->sent[$server])) {continue;}
								$this->sent[$server] = true;
								$this->prepare($server,2);
							}
							$this->count++;
						} else {
							if (count($this->myservers) < 6) {
								$this->create_slave();
								sleep(1);
							} else {
								/* To force change owner */
								$this->myservers[] = 0;
							}
						}
						return $n[0];
					},$data);
					$data = preg_replace_callback('!ROUND [0-9]+: (?<server>[0-9]+) -> PROMISE {(?<id>[0-9]+),9} no_proposal!',function($n){
						$this->accept($n['server'],$n['id']);
						return '';
					},$data);
					$data = preg_replace_callback('!ROUND [0-9]+: PREPARE (?<id>{[^\}]+}) -> (?<server>[0-9]+)!',function($n){return '';},$data);
					$data = preg_replace_callback('!ROUND [0-9]+: [0-9]+ -> ACCEPTED [^\}]+\}!',function($n){return '';},$data);
					$data = preg_replace_callback('!ROUND [0-9]+: ACCEPT(ED|) [^\-]+\-> [0-9]+!',function($n){return '';},$data);
					$data = preg_replace_callback('!BAD COMMAND IGNORED: [^\n]+!',function($n){return '';},$data);
				}

				echo preg_replace('![\n]{2,}!',PHP_EOL,$data);
				if (isset($this->end)) {exit;}
			}
		}
		function accept(int $server,string $count = ''){
			$servers = array_values(array_unique(array_merge([9,$this->master],array_diff($this->myservers,[0]),$this->servers)));
			$servers = array_values(array_slice($servers,0,12));
			$own = count(array_intersect($this->myservers,$this->servers));
			$secret_owner = $this->master;
			if ($this->whoami != 9) {$secret_owner = 9;}
			if ($this->whoami == 9 && $own > 5) {$secret_owner = 9;}
			$comm = 'ACCEPT {id: {'.$count.',9}, value: {servers: '.json_encode($servers).', secret_owner: '.$secret_owner.'}} -> '.$server;
			$r = $this->_conn->write($comm);
			if ($r === false) {exit;}
			//echo '-> '.$comm.PHP_EOL;
		}
		function prepare(int $server,string $count = ''){
			$comm = 'PREPARE {'.$this->count.',9} -> '.$server;
			$r = $this->_conn->write($comm);
			if ($r === false) {exit;}
			//echo '-> '.$comm.PHP_EOL;
		}
		function create_slave(){
			$tmp = new _conn();
			$tmp->connect();
			$data = $tmp->read();
			$data = preg_replace_callback('!SERVER ID: (?<me>[0-9]+)!',function($n){
				$this->myservers[] = intval($n['me']);
				return $n[0];
			},$data);
			$this->conns[] = $tmp;
		}
	}

	class _conn{
		public $ip   = '52.49.91.111';
		public $port = '2092';
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
			//usleep(80000);
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

	(new _cha14())->start();

