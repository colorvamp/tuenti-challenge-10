import sys
from Crypto.PublicKey import RSA

def egcd(a, b):
	if a == 0:
		return (b, 0, 1)
	else:
		g, y, x = egcd(b % a, a)
		return (g, x - (b // a) * y, y)


def modinv(a, m):
	g, x, y = egcd(a, m)
	if g != 1:
		return False
	else:
		return x % m
 
n = long(sys.argv[1])
e = long(sys.argv[2]) #65537

key = RSA.construct((n, long(e)))
print(key.exportKey('PEM'))
