#!/usr/bin/php
<?php
	$lines = file('php://stdin');
	$num = trim(array_shift($lines));

	$case = 0;
	while (($line = trim(array_shift($lines)))) {
		$types = explode(' ',$line);
		$result = '';
		if ($types[0] == $types[1]) {$result = '-';}
		else {
			if (in_array('R',$types) && in_array('S',$types)) {$result = 'R';}
			if (in_array('R',$types) && in_array('P',$types)) {$result = 'P';}
			if (in_array('S',$types) && in_array('P',$types)) {$result = 'S';}
		}

		echo 'Case #'.(++$case).': '.$result.PHP_EOL;
	}
