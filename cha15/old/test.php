#!/usr/bin/php
<?php

	define('GF2_DIM', 32);
	
$test1 = crc32('00000000000000000000');
$test2 = crc32('00000000000000000000');
$test3 = crc32('0000000000000000000000000000000000000000');
var_dump(dechex($test1));
var_dump(dechex($test2));
var_dump(dechex($test3));
var_dump(dechex(crc32_combine(0xc4854223,0xc4854223,strlen('00000000000000000000'))));

function gf2_matrix_times($mat, $vec) {
    $i = 0;
    $sum = 0;
    while ($vec) {
        if ($vec & 1) {
            $sum ^= $mat[$i];
        }
        $vec >>= 1;
        $i++;
    }
    return $sum;
}

function gf2_matrix_square(&$square, &$mat) {
    for ($n = 0; $n < GF2_DIM; $n++) {
        $square[$n] = gf2_matrix_times($mat, $mat[$n]);
    }
}

function crc32_combine($crc1, $crc2, $len2) {
    $even = array_fill(0, GF2_DIM, 0);
    $odd = array_fill(0, GF2_DIM, 0);

    /* degenerate case (also disallow negative lengths) */
    if ($len2 <= 0) {
        return $crc1;
    }

    /* put operator for one zero bit in odd */
    $odd[0] = 0xedb88320;   /* CRC-32 polynomial */
    $row = 1;
    for ($n = 1; $n < GF2_DIM; $n++) {
        $odd[$n] = $row;
        $row <<= 1;
    }

    /* put operator for two zero bits in even */
    gf2_matrix_square($even, $odd);

    /* put operator for four zero bits in odd */
    gf2_matrix_square($odd, $even);

    /* apply len2 zeros to crc1 (first square will put the operator for one
     zero byte, eight zero bits, in even) */
    do {
        /* apply zeros operator for this bit of len2 */
        gf2_matrix_square($even, $odd);
        if ($len2 & 1) {
            $crc1 = gf2_matrix_times($even, $crc1);
        }
        $len2 >>= 1;

        /* if no more bits set, then done */
        if ($len2 == 0) {
            break;
        }

        /* another iteration of the loop with odd and even swapped */
        gf2_matrix_square($odd, $even);
        if ($len2 & 1) {
            $crc1 = gf2_matrix_times($odd, $crc1);
        }
        $len2 >>= 1;

        /* if no more bits set, then done */
    } while ($len2 != 0);

    /* return combined crc */
    $crc1 ^= $crc2;
    return $crc1;
}

