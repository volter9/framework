<?php

namespace {{namespace}};

use \mako\reactor\tasks\migrate\Migration;

class Migration_{{version}} extends Migration
{
	/**
	 * Description.
	 * 
	 * @var string
	 */

	protected $description = '{{description}}';

	/**
	 * Makes changes to the database structure.
	 *
	 * @access  public
	 */

	public function up()
	{

	}

	/**
	 * Reverts the database changes.
	 *
	 * @access  public
	 */

	public function down()
	{

	}
}