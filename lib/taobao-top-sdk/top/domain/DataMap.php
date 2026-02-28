<?php

/**
 * x
 * @author auto create
 */
class DataMap
{
	
	/** 
	 * 当入参不传pid的时候返回，表示账号关联的pid
	 **/
	public $pid;
	
	/** 
	 * 当入参传入pid的时候返回，表示pid关联的relationId
	 **/
	public $rid;
	
	/** 
	 * 0表示预警，1表示拦截，如果名单中同一个淘客同时有拦截和预警信息，以拦截为准
	 **/
	public $status;	
}
?>