<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
/**
* Dependency Injector Container Class
*
*/
interface HC3_IDic
{
	public function bind( $entity, $interface = NULL, $singleton = TRUE );
	public function make( $interface, $wrap = TRUE );
}

class HC3_Dic implements HC3_IDic
{
	protected $bind = array();
	protected $singletons = array();

	public function __construct()
	{
		$this->bind( $this, get_class($this) );
	}

	/**
	* Binds a concrete implementation to the interface.
	* The below is not true as of 2017/03/21, this chain implementation appeared to be mind breaking, so hard to follow
	* If several implementations are bound to one interface, it will return the latest one added.
	* Then in implementations code they can refer to their own interface, but their own implementation will
	* be skipped to avoid circular reference. So it is used like a decorator chain, each implementation can
	* refer to an optional parent object of their own interface.
	*
	* @param	string|object	$entity		A class name or object that implements an interface.
	* @param	string			$interface	Optional. An interface to be implemented. If omitted,
	*										it will require loading the class file to analyze which
	*										may add an overhead.
	* @param	bool			$singleton	Optional. If true then makes the returning object a singleton.
	*
	* @return	object|NULL
	*/
	public function bind( $entity, $interface = NULL, $singleton = TRUE )
	{
		if( $interface === NULL ){
			$interface = is_object($entity) ? get_class($entity) : $entity;

			// $interfaces = class_implements( $interface );
// echo "'$interface' IMPLEMENTS";
// _print_r( $interfaces );
			// if( $interfaces ){
// echo "'$interface' IMPLEMENTS ";
				// $interface = array_shift( $interfaces );
// echo "'$interface'<br>";
			// }
		}

		$interface = strtolower( $interface );
// if( is_object($entity) )
// echo "BINDING '" . get_class($entity) . "' AS '" .  $interface . "'<br>";
// else
// echo "BINDING '" . $entity . "' AS '" .  $interface . "'<br>";

		// echo "int = '$interface'<br>";

		if( ! is_object($entity) ){
			$entity = strtolower( $entity );
		}

		$this->bind[ $interface ] = $entity;

		if( $singleton ){
			$this->singletons[ $interface ] = $interface;
		}

		return $this;
	}

	/**
	* Returns a concrete implementation of the interface with their dependencies injected in constructor.
	* Dependencies can be either type-hinted by class/interface name like HC3_Module_ClassName $obj
	* or var name hinted, the var name defines the class name, like this $HC3_Module_ClassName
	Returns a concrete implementation of the interface with their dependencies injected in constructor.
	*
	* @return object|NULL	A concrete implementation of the interface.
	*/
	public function make( $interface, $wrap = TRUE )
	{
		$hooks = NULL;
		if( array_key_exists('hc3_hooks', $this->bind) ){
			$hooks = $this->bind['hc3_hooks'];
		}
		else {
			$wrap = FALSE;
		}
		if( 'hc3_hooks' == $interface ){
			$wrap = FALSE;
		}

		$interface = strtolower( $interface );

// echo "WANT '$interface'<br>";
// echo 'GOTSKIP';
// _print_r( $skip );

// _print_r( array_keys($this->bind) );

		$bind_index = 0;
	// binded
		if( array_key_exists($interface, $this->bind) ){
// echo "INT '$interface' BOUND<br>";
			$thisone = $this->bind[$interface];

			if( is_object($thisone) ){
				if( $wrap ){
					$return = $hooks->wrap($thisone);
					return $return;
				}
				return $thisone;
			}
			else {
				if( ! $thisone ){
					if( class_exists($interface) ){
						$thisone = $interface;
					}
				}
				$classname = $thisone;
			}
		}
		else {
			$classname = $interface;

			// try to use a default implementation
			// if( substr($classname, -1) == '_' ){
				// $classname = substr($classname, 0, -1);
			// }

			$this->singletons[$interface] = $interface;
		}

// echo "REAL '$classname'<br>";
// _print_r( $classname );
		if( ! strlen($classname) ){
			$return = NULL;
			return $return;
		}

		$class = new ReflectionClass( $classname );
		$constructor = $class->getConstructor();

		$dependencies = array();

		if( isset($constructor) ){
			$need_args = $constructor->getParameters();
			foreach( $need_args as $need_arg ){
				$is_optional = $need_arg->isOptional();
				$wrap_this = FALSE;

				$arg_name = $need_arg->getName();
				$arg_name = strtolower( $arg_name );

				$need_classname = NULL;

				try {
					if( method_exists($need_arg, 'getType') ){
						// $need_class = $need_arg->getType() && !$need_arg->getType()->isBuiltin() ? new ReflectionClass($need_arg->getType()->getName()) : NULL;
						$argType = $need_arg->getType();
						if( (! $argType) OR $argType->isBuiltin() ){
							$argClass = NULL;
						}
						else {
							if( method_exists($argType, 'getName') ){
								$argClassName = $argType->getName();
							}
							else {
								$argClassName = '' . $argType;
							}
							$argClass = new ReflectionClass( $argClassName );
						}
						$need_class = $argClass;
					}
					else {
						$need_class = $need_arg->getClass();
					}
				}
				catch( ReflectionException $e ){
					$need_class = NULL;
				}

				if( $need_class ){
					$need_classname = $need_class->getName();
				}

				if( ! $need_class ){
					if( ! $is_optional ){
						$need_classname = $arg_name;
						$wrap_this = TRUE;
						// echo "NOW NEED CLASSNAME = '$need_classname'<br>";
					}
				}

				if( $is_optional && (! $need_classname) ){
					continue;
				}

				if( (! $need_classname) && (! $is_optional) ){
					echo "DIC: class/varname is unknown for '$arg_name' argument of '$classname'!<br>";
					exit;
				}

				if( $need_classname ){
					$need_classname = strtolower( $need_classname );

					if( ! array_key_exists($need_classname, $this->bind) ){
						// if( $is_optional ){
							// continue;
						// }
						// else {
							// echo "DIC: class '$need_classname' is not registered while trying to use it as a dependency for '$classname'<br>";
							// exit;
						// }
					}
					$dependencies[] = array( $need_classname, $wrap_this );
				}
			}
		}

		if( $dependencies ){
			$args = array();

			foreach( $dependencies as $dep ){
				list( $dep, $wrap_this ) = $dep;
				if( ! is_object($dep) ){
					$dep = $this->make($dep, $wrap_this);
				}
				$args[] = $dep;
			}
			$class = new ReflectionClass($classname);
			$return = $class->newInstanceArgs($args);
		}
		else {
			$return = new $classname;
		}

		if( isset($this->singletons[$interface]) ){
// echo 'REBIND ' . $interface . ' AS ' . get_class($return) . '<br>';
// echo 'BIND INDEX = ' . $bind_index . '<br>';
// _print_r( $this->bind[$interface] );
			// $this->bind( $return, $interface );
			$this->bind[ $interface ] = $return;
		}

		if( $wrap ){
			$return = $hooks->wrap($return);
		}
		else {
			// echo "NO WRAP FOR " . get_class($return) . "<br>\n";
		}

		return $return;
	}
}
