import binascii
import math

with open('testdata/plaintexts/test1.txt', 'rb') as content_file:
	m1 = int(binascii.hexlify(content_file.read()),16)

with open('testdata/plaintexts/test2.txt', 'rb') as content_file:
	m2 = int(binascii.hexlify(content_file.read()),16)

with open('testdata/ciphered/test1.txt', 'rb') as content_file:
	c1 = int(binascii.hexlify(content_file.read()),16)

with open('testdata/ciphered/test2.txt', 'rb') as content_file:
	c2 = int(binascii.hexlify(content_file.read()),16)

e = 65537
p1 = pow(m1,e) - c1
p2 = pow(m2,e) - c2
mod = math.gcd(p1, p2)
print(mod)
