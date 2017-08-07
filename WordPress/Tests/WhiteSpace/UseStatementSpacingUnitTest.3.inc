<?php

/*
 * Use with traits.
 */
class ezcReflectionMethod extends ReflectionMethod {
	use ezcReflectionReturnInfo;
}

class MyHelloWorld {
	use \Hello, World;
}

class MyHelloWorld {
	use \Hello,
		World;
}


class MyClass1 {
	use \HelloWorld { sayHello as protected; }
}

class MyClass2 {
	use HelloWorld {
		sayHello as private myPrivateHello;
	}
}

class SomeThing {
	use Meta_Submodule_Trait, \Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}
}

class Aliased_Talker {
	use A, B {
		B::smallTalk insteadof A;
		A::bigTalk insteadof B;
		B::bigTalk as talk;
	}
}
class Foo {
	use A, B, C {
		C::bar insteadof A, B;
	}
}
