<?php

namespace Stripe;

class ApiRequestorTest extends TestCase {

	public function testEncodeObjects()
	{
		$reflector = new \ReflectionClass('Stripe\\ApiRequestor');
		$method    = $reflector->getMethod('_encodeObjects');
		$method->setAccessible(TRUE);

		$a   = array('customer' => new Customer('abcd'));
		$enc = $method->invoke(NULL, $a);
		$this->assertSame($enc, array('customer' => 'abcd'));

		// Preserves UTF-8
		$v   = array('customer' => "☃");
		$enc = $method->invoke(NULL, $v);
		$this->assertSame($enc, $v);

		// Encodes latin-1 -> UTF-8
		$v   = array('customer' => "\xe9");
		$enc = $method->invoke(NULL, $v);
		$this->assertSame($enc, array('customer' => "\xc3\xa9"));
	}
}
