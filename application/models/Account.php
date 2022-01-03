<?php
class Account extends MY_Model {
	public function __construct()
	{
         parent::__construct();
        $this->table       = 'account';
        $this->primary_key = 'account_id';
	}
}
	?>