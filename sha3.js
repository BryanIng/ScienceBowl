
var KECCAFK_ROUNDS = 24;

var shr64 = function(x, y, index) {
    var r = [0,0,0,0,0,0,0];

    var s = y % 8;
    var S = y >> 3;
    var o = S + index;

    var iters = 8 - S;
    if (iters <= 0 ) {
        return r;
    }

    var i = 0;
    while (i < iters - 1) {
        r[i] = ((x[i+o] + (x[i+o+1] << 8)) >> s) & 0xFF;
        i++;
    }
    r[i] = (x[i+o] >> s) & 0xFF
    return r;
}

var shl64 = function(x, y, index) {
    var r = [0,0,0,0,0,0,0];

    var s = y % 8;
    var S = y >> 3;
    var o = index - S;

    var iters = 8 - S;
    if (iters <= 0 ) {
        return r;
    }

    var i = 7;
    while (i > S) {
        r[i] = ((((x[i+o] << 8) + x[i+o-1]) << s) & 0xFF00) >> 8;
        i--;
    }
    r[i] = (x[i+o] << s) & 0xFF
    return r;
}

var or64 = function(x,y,ix,iy) {
    var r = [0,0,0,0,0,0,0];

    var i = 0;
    while ( i < 8 ) {
        r[i] = x[i + ix] | y[i + iy];
        i++;
    }
    return r;
}

var and64 = function(x,y,ix,iy) {
    var r = [0,0,0,0,0,0,0];

    var i = 0;
    while ( i < 8 ) {
        r[i] = x[i + ix] & y[i + iy];
        i++;
    }
    return r;
}

var xor64 = function(x,y,ix,iy) {
    var r = [0,0,0,0,0,0,0];

    var i = 0;
    while ( i < 8 ) {
        r[i] = x[i + ix] ^ y[i + iy];
        i++;
    }
    return r;
}

var not64 = function(x,o) {
    var r = [0,0,0,0,0,0,0,0];

    var i = 0;
    while ( i < 8 ) {
        r[i] = ~x[i + o];
        i++;
    }
    return r;
}

var ROTL64 = function(x,y,i) {
    return or64(shl64(x,y,i), shr64(x,64-y,i), i, i);
}

var get64 = function(x, i) {
    var r = [ x[i], x[i+1], x[i+2], x[i+3], x[i+4], x[i+5], x[i+6], x[i+7] ];
    return r;
}
var flip64 = function(x, i) {
    var r = [ x[i+7], x[i+6], x[i+5], x[i+4], x[i+3], x[i+2], x[i+1], x[i] ];
    return r;
}

var set64 = function( x,i,r) {
    x[i] = r[0];
    x[i+1] = r[1];
    x[i+2] = r[2];
    x[i+3] = r[3];
    x[i+4] = r[4];
    x[i+5] = r[5];
    x[i+6] = r[6];
    x[i+7] = r[7];
}

var Keccakf = function(x) {
    var rndc = [
        0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x01, 0x00,0x00,0x00,0x00,0x00,0x00,0x80,0x82, 0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x8a,
        0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x00, 0x00,0x00,0x00,0x00,0x00,0x00,0x80,0x8b, 0x00,0x00,0x00,0x00,0x80,0x00,0x00,0x01,
        0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x81, 0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x09, 0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x8a,
        0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x88, 0x00,0x00,0x00,0x00,0x80,0x00,0x80,0x09, 0x00,0x00,0x00,0x00,0x80,0x00,0x00,0x0a,
        0x00,0x00,0x00,0x00,0x80,0x00,0x80,0x8b, 0x80,0x00,0x00,0x00,0x00,0x00,0x00,0x8b, 0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x89,
        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x03, 0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x02, 0x80,0x00,0x00,0x00,0x00,0x00,0x00,0x80,
        0x00,0x00,0x00,0x00,0x00,0x00,0x80,0x0a, 0x80,0x00,0x00,0x00,0x80,0x00,0x00,0x0a, 0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x81,
        0x80,0x00,0x00,0x00,0x00,0x00,0x80,0x80, 0x00,0x00,0x00,0x00,0x80,0x00,0x00,0x01, 0x80,0x00,0x00,0x00,0x80,0x00,0x80,0x08
    ];
    var rotc = [
        1,  3,  6,  10, 15, 21, 28, 36, 45, 55, 2,  14,
        27, 41, 56, 8,  25, 43, 62, 18, 39, 61, 20, 44
    ];
    var piln = [
        10, 7,  11, 17, 18, 3, 5,  16, 8,  21, 24, 4,
        15, 23, 19, 13, 12, 2, 20, 14, 22, 9,  6,  1
    ];
    var b = [[],[],[],[],[]];

    var round = 0;
    while (round < 24) {
        var i = 0;
        while (i < 5) {
            b[i] = xor64(x,xor64(x,xor64(x,xor64(x,x,8*(i+20),8*(i+15)),8*(i+10),0),8*(i+5),0),8*i,0);
            i++;
        }

        i = 0;
        while (i < 5) {
            var t = xor64( b[(i+4) % 5], ROTL64(b[(i+1) % 5],1,0),0,0);
            var j = 0;
            while (j < 25) {
                set64(x, 8*(i+j), xor64(x, t, (i+j)*8, 0) );
                j += 5;
            }
            i++;
        }

        var t = get64(x,8);

        i = 0;
        while (i < 24) {
            var j = piln[i];
            b[0] = get64(x,j * 8);
            set64(x,j*8,ROTL64(t, rotc[i], 0))
            t = b[0];
            i++;
        }

        var j = 0;
        while (j < 25) {
            i = 0;
            while ( i < 5 ) {
                b[i] = get64(x, 8*(j+i));
                //console.log(b[i]);
                i++;
            }
            i = 0;
            while (i < 5 ) {
                set64(x,8*(i+j),xor64(x,and64(not64(b[(i + 1) % 5],0),b[(i + 2) % 5],0,0),8*(i+j),0));
                //console.log(get64(x, 8*(i+j)));
                i++;
            }
            j += 5;
        }

        set64(x, 0, xor64( x, flip64(rndc,8*round), 0, 0));
        //console.log(get64(x,0));

        //console.log(x);
        //return;
        round++;
    }

}

var sha3INIT = function(c,m) {
    var i = 0;
    while ( i < 200) {
        c[0].push(0);
        i++;
    }
    c[3] = m;
    c[2] = 200 - 2*m;
    c[1] = 0;
    //console.log(c);
    //console.log(c[0]);
}

var sha3UPDATE = function(c,d,l) {
    var j = c[1];
    var i = 0;
    //console.log(l);
    while (i < l) {
        c[0][j] ^= d[i];
        //console.log(d[i]);
        j++;
        if (j >= c[2]) {
            Keccakf(c[0]);
            j=0;
        }
        i++;
    }
    //console.log(c);
    c[1] = j;
}

var sha3FINAL = function(m,c) {
    c[0][c[1]] ^= 0x06;
    c[0][c[2]-1] ^= 0x80;
    Keccakf(c[0]);
    var i = 0;
    while (i < c[3]) {
        m[i] = c[0][i];
        //console.log(m[i]);
        i++;
    }
    //console.log(c[0]);
}

var sha3FULL = function(i,il,m,ml) {
    var s = [[],0,0,0];
    sha3INIT(s,ml);
    sha3UPDATE(s,i,il);
    sha3FINAL(m,s);
}

var hexStringRef = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F'];
var hashToString = function(b) {
    l = b.length;
    var s = "";
    var i = 0;
    while (i < l) {
        var lo = b[i] & 0xF;
        var hi = (b[i] >> 4) & 0xF;
        s += hexStringRef[hi];
        s += hexStringRef[lo];
        i++;
    }
    return s;
}
var stringToData = function(s) {
    var l = s.length;
    var b = [];
    var i = 0;
    while (i < l) {
        b.push(s.charCodeAt(i));
        i++;
    }
    return b;
}
var hexStringToData = function(s) {
    var l = s.length;
    var b = [];
    var j = 0;
    var low = false;
    var i = 0;
    while (i < l) {
        var v;
        var c = s.charCodeAt(i);
        if (c >= 48 && c <= 57) {
            v = c - 48;
        }
        else if (c >= 65 && c <= 70) {
            v = c - 55;
        }
        else if (c >= 97 && c <= 102) {
            v = c - 87;
        }

        if (low) {
            b[j] += v & 0xF;
            low = false;
            j++;
        }
        else {
            b.push(v << 4);
            low = true;
        }
        i++;
    }
    return b;
}

var sha3 = function(input, l) {
    if (l != 224 && l != 256 && l != 384 && l != 512) {
        return "blah";
    }

    var s = [];
    var i = 0;
    while (i < 64) {
        s.push(0);
        i++;
    }

    var sl = l >> 3;
    var b = [];
    i = 0;
    while (i < sl) {
        b.push(0);
        i++;
    }
    var ml = input.length;
    if (ml > 256) {
        ml = 256;
    }

    sha3FULL( input, ml, b, sl);
    return b;
}