<?php

class Host
{
	private $host = null;
	private $ip = null;
	private $parent = null;
	private $t_alias = array();
	private $is_alias = false;


	public function setHost( $v ) {
		$this->host = trim( $v );
	}
	public function getHost() {
		return $this->host;
	}


	public function getIp()
	{
		return $this->ip;
	}
	public function setIp( $v ) {
		$this->ip = trim( $v );
	}


	public function setParent( $v )
	{
		$this->parent = $v->getHost();
	}
	public function getParent()
	{
		return $this->parent;
	}


	public function setAlias( $v )
	{
		$this->is_alias = (bool)$v;
	}


	public function getAlias()
	{
		return $this->t_alias;
	}

	public function addAlias( $v )
	{
		$this->t_alias[] = $v->getHost();
	}


	public function hasAlias()
	{
		return count($this->t_alias);
	}
}

?>
