<?php

function printArray($x) {
	$l = count($x);
	echo "[";
	for ($i = 0; $i < $l; $i++) {
		echo $x[$i] . ", ";
	}
	echo "]<br>";
}


function shr64($x, $y, $index) {
	$r = array_fill(0,8,0);
	$s = $y % 8;
	$S = $y >> 3;
	$o = $S + $index;
	
	$iters = 8 - $S;
	if ($iters <= 0) {
		return $r;
	}
	
	$i = 0;
	while ($i < $iters - 1) {
		$r[$i] = (($x[$i+$o] + ($x[$i+$o+1] << 8)) >> $s) & 0xFF;
		$i++;
	}
	$r[$i] = ($x[$i+$o] >> $s) & 0xFF;
	return $r;
}
function shl64($x, $y, $index) {
	$r = array_fill(0,8,0);
	$s = $y % 8;
	$S = $y >> 3;
	$o = $index - $S;
	
	$iters = 8 - $S;
	if ($iters <= 0) {
		return $r;
	}
	
	$i = 7;
	while ($i > $S) {
		$r[$i] = (((($x[$i+$o] << 8) + $x[$i+$o-1]) << $s) & 0xFF00) >> 8;
		$i--;
	}
	$r[$i] = ($x[$i+$o] << $s) & 0xFF;
	return $r;
}
function or64($x,$y,$ix,$iy) {
	$r = array_fill(0,8,0);
	
	for ($i = 0; $i < 8; $i++) {
		$r[$i] = $x[$i + $ix] | $y[$i + $iy];
	}
	return $r;
}
function and64($x,$y,$ix,$iy) {
	$r = array_fill(0,8,0);
	
	for ($i = 0; $i < 8; $i++) {
		$r[$i] = $x[$i + $ix] & $y[$i + $iy];
	}
	return $r;
}
function xor64($x,$y,$ix,$iy) {
	$r = array_fill(0,8,0);
	
	for ($i = 0; $i < 8; $i++) {
		$r[$i] = $x[$i + $ix] ^ $y[$i + $iy];
	}
	return $r;
}
function not64($x,$o) {
	$r = array_fill(0,8,0);
	
	for ($i = 0; $i < 8; $i++) {
		$r[$i] = ~$x[$i + $o];
	}
	return $r;
}
function ROTL64($x,$y,$i) {
	$r = or64( shl64($x,$y,$i), shr64($x,64-$y,$i), $i, $i);
	return $r;
}
function get64($x,$i) {
	$r = array($x[$i], $x[$i+1], $x[$i+2], $x[$i+3], $x[$i+4], $x[$i+5], $x[$i+6], $x[$i+7]);
	return $r;
}
function flip64($x,$i) {
	$r = array($x[$i+7], $x[$i+6], $x[$i+5], $x[$i+4], $x[$i+3], $x[$i+2], $x[$i+1], $x[$i]);
	return $r;
}
function set64(&$x,$i,$r) {
	$x[$i] = $r[0];
	$x[$i+1] = $r[1];
	$x[$i+2] = $r[2];
	$x[$i+3] = $r[3];
	$x[$i+4] = $r[4];
	$x[$i+5] = $r[5];
	$x[$i+6] = $r[6];
	$x[$i+7] = $r[7];
}
function Keccakf(&$x) {
	$rndc = array(
	        0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x01,
	        0x00,0x00,0x00,0x00,0x00,0x00,0x80,0x82,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x8a,
	        0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x00,
	        0x00,0x00,0x00,0x00,0x00,0x00,0x80,0x8b,
	        0x00,0x00,0x00,0x00,0x80,0x00,0x00,0x01,
	        0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x81,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x09,
	        0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x8a,
	        0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x88,
	        0x00,0x00,0x00,0x00,0x80,0x00,0x80,0x09,
	        0x00,0x00,0x00,0x00,0x80,0x00,0x00,0x0a,
	        0x00,0x00,0x00,0x00,0x80,0x00,0x80,0x8b,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x00,0x8b,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x89,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x03,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x02,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x00,0x80,
	        0x00,0x00,0x00,0x00,0x00,0x00,0x80,0x0a,
	        0x80,0x00,0x00,0x00,0x80,0x00,0x00,0x0a,
	        0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x81,
	        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x80,
	        0x00,0x00,0x00,0x00,0x80,0x00,0x00,0x01,
	        0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x08
	);
	$rotc = array(
        	1,  3,  6,  10, 15, 21, 28, 36, 45, 55, 2,  14,
        	27, 41, 56, 8,  25, 43, 62, 18, 39, 61, 20, 44
    	);
    	$piln = array(
        	10, 7,  11, 17, 18, 3, 5,  16, 8,  21, 24, 4,
        	15, 23, 19, 13, 12, 2, 20, 14, 22, 9,  6,  1
    	);
    	$b = array_fill(0,5,array_fill(0,8,0));
    	for ($round = 0; $round < 24; $round++) {
    		for ($i = 0; $i < 5; $i++) {
    			$b[$i] = xor64($x,xor64($x,xor64($x,xor64($x,$x,8*($i+20),8*($i+15)),8*($i+10),0),8*($i+5),0),8*$i,0);
    		}
    		for ($i = 0; $i < 5; $i++) {
    			$t = xor64( $b[($i+4) % 5], ROTL64($b[($i+1) % 5],1,0),0,0);

    			for ($j = 0; $j < 25; $j += 5) {
				set64($x, 8*($i+$j), xor64($x, $t, ($i+$j)*8, 0) );
    			}
    		}
    		
    		$t = get64($x,8);
    		
    		for ($i = 0; $i < 24; $i++) {
    			$j = $piln[$i];
    			$b[0] = get64($x, $j*8);
    			set64($x,$j*8,ROTL64($t, $rotc[$i], 0));
    			$t = $b[0];
    		}

		for ($j = 0; $j < 25; $j += 5) {
			for ($i = 0; $i < 5; $i++) {
				$b[$i] = get64($x, 8*($j+$i));
			}
			for ($i = 0; $i < 5; $i++) {
				set64($x,8*($i+$j),xor64($x,and64(not64($b[($i + 1) % 5],0),$b[($i + 2) % 5],0,0),8*($i+$j),0));
			}
		}
		
		set64($x, 0, xor64( $x, flip64($rndc,8*$round), 0, 0));
    	}
}

function sha3INIT(&$c, &$cstat, $m) {
	$cstat[2] = $m;
	$cstat[1] = 200 - 2*$m;
	$cstat[0] = 0;
}

function sha3UPDATE(&$c, &$cstat, $d, $l) {
	$j = $cstat[0];
	for ($i = 0; $i < $l; $i++) {
		$c[$j] ^= $d[$i];
		$j++;
		if ($j >= $cstat[1]) {
			Keccakf($c);
			$j = 0;
		}
	}
	
	$cstat[0] = $j;
}
function sha3FINAL(&$m,&$c,&$cstat) {
	$c[$cstat[0]] ^= 0x06;
	$c[$cstat[1]-1] ^= 0x80;
	Keccakf($c);
	for ($i = 0; $i < $cstat[2]; $i++) {
		$m[$i] = $c[$i];
	}
}
function sha3FULL($i,$il,&$m,$ml) {
	$s = array_fill(0,200,0);
	$sT = array(0,0,0);
	sha3INIT($s,$sT,$ml);
	sha3UPDATE($s,$sT,$i,$il);
	sha3FINAL($m,$s,$sT);
}

$hexStringRef = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
function hashToString($b) {
	global $hexStringRef;
	$l = count($b);
	$s = "";
	for ( $i = 0; $i < $l; $i++) {
		$lo = $b[$i] & 0xF;
		$hi = ($b[$i] >> 4) & 0xF;
		$s .= $hexStringRef[$hi];
		$s .= $hexStringRef[$lo];
	}
	return $s;
}

function stringToData($s) {
	$l = strlen($s);
	$b = array();
	for ($i = 0; $i < $l; $i++) {
		array_push($b,ord($s[$i]));
	}
	return $b;
}
function hexStringToData($s) {
	$l = strlen($s);
	$b = array();
	$j = 0;
	$low = FALSE;
	for ($i = 0; $i < $l; $i++) {
		$v = 0;
		$c = ord($s[$i]);
		if ($c >= 48 && $c <= 57)  {
			$v = $c - 48;
		}
		else if ($c >= 65 && $c <= 70)  {
			$v = $c - 55;
		}
		else if ($c >= 97 && $c <= 102)  {
			$v = $c - 87;
		}
		
		if ($low) {
			$b[$j] += $v & 0xF;
			$low = FALSE;
		}
		else {
			array_push($b,$v << 4);
			$low = TRUE;
		}
	}
	return $b;
}

function sha3($input, $l) {
	if ($l != 224 && $l != 256 && $l != 384 && $l != 512) {
        	return;
    	}
	
	$s = array_fill(0,64,0);
	
	$sl = $l >> 3;
	$b = array_fill(0,$sl,0);
	
	$ml = count($input);
	if ($ml > 256) {
		$ml = 256;
	}
	
	sha3FULL( $input, $ml, $b, $sl);
	return $b;
}
?>