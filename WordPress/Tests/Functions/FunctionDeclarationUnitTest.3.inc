<?php

class MyClass {

	function func() {}

	public function func() {}
	protected function func() {}
	private function func() {}

	final function func() {}
	public final function func() {}
	final protected function func() {}
	private final function func() {}

	static function func() {}
	public static function func() {}
	protected static function func() {}
	static private function func() {}

	static public final function funct() {}

	static     public       final    function funct() {}
}
