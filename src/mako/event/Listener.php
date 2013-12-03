<?php

namespace mako\event;

use \Closure;

/**
 * Event listener.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Listener
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Array of events.
	 *
	 * @var array
	 */

	protected static $events = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Adds an event listener to the queue.
	 *
	 * @access  public
	 * @param   string    $name     Event name
	 * @param   \Closure  $closure  Event handler
	 */

	public static function register($name, Closure $closure)
	{
		static::$events[$name][] = $closure;
	}

	/**
	 * Returns TRUE if an event listener is registered for the event and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $name  Event name
	 * @return  boolean
	 */

	public static function registered($name)
	{
		return isset(static::$events[$name]);
	}

	/**
	 * Clears all events or just the events for a specific trigger.
	 *
	 * @access  public
	 * @param   string  $name  (optional) Event name
	 */

	public static function clear($name = null)
	{
		if($name === null)
		{
			static::$events = [];
		}
		else
		{
			unset(static::$events[$name]);
		}
	}

	/**
	 * Overrides an event.
	 * 
	 * @access  public
	 * @param   string    $name     Event name
	 * @param   \Closure  $closure  Event handler
	 */

	public static function override($name, Closure $closure)
	{
		static::clear($name);

		static::register($name, $closure);
	}

	/**
	 * Runs all closures for an event and returns an array 
	 * contaning the return values of each event handler.
	 *
	 * @access  public
	 * @param   string   $name    Event name
	 * @param   array    $params  (optional) Closure parameters
	 * @param   boolean  $break   (optional) Break if one of the closures returns false?
	 * @return  array
	 */

	public static function trigger($name, array $params = [], $break = false)
	{
		$values = [];

		if(isset(static::$events[$name]))
		{
			foreach(static::$events[$name] as $event)
			{
				$values[] = $last = call_user_func_array($event, $params);

				if($break && $last === false)
				{
					return $values;
				}
			}
		}

		return $values;
	}
}

/** -------------------- End of file -------------------- **/