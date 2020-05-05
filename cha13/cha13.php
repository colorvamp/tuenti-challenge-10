#!/usr/bin/php
<?php
	class _cha13{
		public $map = [];
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$case = 0;
			while ($this->cases--) {
				$this->nodes = trim(array_shift($this->lines));
				//if ($this->nodes != 24283176608567660) {continue;}
				if ($this->nodes < 43) {
					echo 'Case #'.(++$case).': IMPOSSIBLE'.PHP_EOL;
					continue;
				}

				$this->canjump = true;
				$this->incr = 2;
				$this->step = 1;
				$this->max  = 0;
				do {
					$num = $this->ring();
				} while ($num < $this->nodes);

				if ($this->canjump) {
					while ($this->incr > 100) {
						$this->canjump = false;
						$this->incr /= 2;
						do {
							$num = $this->ring();
						} while ($num < $this->nodes);
					}

					$this->canjump = false;
					$this->incr = 2;
					do {
						$num = $this->ring();
					} while ($num < $this->nodes);
				}


				$this->canjump = false;
				$this->incr = 1;
				$this->size = 2;
				do {
					$num = $this->tower();
				} while ($num < $this->nodes);

				if ($this->canjump) {
					$this->canjump = false;
					$this->incr = 1;
					do {
						$num = $this->ring();
					} while ($num < $this->nodes);
				}

				echo 'Case #'.(++$case).': '.$this->height.' '.$this->max.PHP_EOL;
			}
		}
		function ring(){
			$tmp_step = $this->step + $this->incr;
			$tmp_height = 3 + ($tmp_step - 3) / 2;
			$ring   = 1;
			$step   = $tmp_step;
			$height = $tmp_height;
			$sum    = 0;

			$count = 1;
			$total = 0;
			while ($step--) {
				if (empty($sum)) {
					$sum += 1 * $height;
					continue;
				}

				$height = $height + ($step % 2 != 0 ? -2 : 1);

				$ring += 2;
				$sum += ($ring * 4 - 4) * $height;
			}

			if ($sum <= $this->nodes) {
				$this->step = $tmp_step;
				$this->height = $tmp_height;
				$this->max = $sum;
				if ($this->canjump) {$this->incr *= 2;}
			}
			return $sum;
		}
		function tower(){
			$tmp_size = $this->size + $this->incr;

			$ring   = 1;
			$size   = intval(floor($tmp_size / 2) * ceil($tmp_size / 2));
			$step   = $this->step;
			$height = $this->height;
			$incr   = intval((floor($tmp_size / 2) * 2) + (ceil($tmp_size / 2) * 2) - 4);
			$sum    = 0;

			$count = 1;
			$total = 0;
			while ($step--) {
				if (empty($sum)) {
					$sum += $size * $height;
					continue;
				}

				$height = $height + ($step % 2 != 0 ? -2 : 1);

				$ring += 2;
				$sum += (($ring * 4 - 4) + $incr) * $height;
			}

			if ($sum <= $this->nodes) {
				$this->size = $tmp_size;
				$this->max = $sum;
				if ($this->canjump) {$this->incr *= 2;}
			}
			return $sum;
		}
	}

	(new _cha13())->start();

