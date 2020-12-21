<?php
namespace C_Trax_Integration\Models;

/**
 * Model base Class.
 */
class Model {


	public function __construct(array $attrs = []) {
		$this->set_properties($attrs);
	}

	private function set_properties(array $attrs) {
		if(!empty($attrs))
		{
			foreach($attrs as $attr => $value)
			{
				$this->{$attr} = $value;
			}
		}
	}
}
?>