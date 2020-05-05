#!/usr/bin/php
<?php
	class _cha02{
		public $factor = 32;
		function start(){
			$this->lines  = file('php://stdin');
			$this->cases  = trim(array_shift($this->lines));
			$this->count  = 0;

			$case = 0;
			while ($this->cases--) {
				$this->nodes = trim(array_shift($this->lines));
				$this->factor = 32 / ($this->nodes - 1);

				$this->result = [];
				while ($this->nodes--) {
					if ($this->count++ % 1000 == 0) {
						fwrite(STDERR, ($this->count).PHP_EOL);
					}

					$this->line = trim(array_shift($this->lines));
					$tmp = explode(' ',$this->line);
					$pos = $tmp[2] == '0' ? '1' : '0';
					if (!isset($this->result[$tmp[0]])) {$this->result[$tmp[0]] = 1600;}
					if (!isset($this->result[$tmp[1]])) {$this->result[$tmp[1]] = 1600;}

					$score_a = $tmp[2] == '1' ? 1 : 0;
					$score_b = $tmp[2] == '0' ? 1 : 0;
					$this->elo($tmp[0], $tmp[1], $score_a, $score_b);
				}

				file_put_contents('resolve.txt',json_encode($this->result));
				arsort($this->result);
				reset($this->result);
				$player = key($this->result);
				fwrite(STDERR, 'Player '.$player.PHP_EOL);

				echo 'Case #'.(++$case).': '.$player.PHP_EOL;
			}
		}
		function elo(string $player_a,string $player_b,int $score_a,int $score_b){
			$this->rating_a = $this->result[$player_a];
			$this->rating_b = $this->result[$player_b];

			$this->expected_scores();

			$this->result[$player_a] = $this->rating_a + ($this->factor * ($score_a - $this->expected_a));
			$this->result[$player_b] = $this->rating_b + ($this->factor * ($score_b - $this->expected_b));
		}
		function expected_scores(){
			$this->expected_a = 1 / (1 + (pow(10, ($this->rating_b - $this->rating_a) / 400)));
			$this->expected_b = 1 / (1 + (pow(10, ($this->rating_a - $this->rating_b) / 400)));
		}
	}

	(new _cha02())->start();

