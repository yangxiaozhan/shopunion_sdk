<?php

/**
 * 定向计划集合-仅支持联系商务或运营小二申请定向计划合集字段权限
 * @author auto create
 */
class SpCampaign
{
	
	/** 
	 * 定向计划申请链接
	 **/
	public $sp_apply_link;
	
	/** 
	 * 定向计划活动ID
	 **/
	public $sp_cid;
	
	/** 
	 * 定向是否锁佣，0=不锁佣 1=锁佣
	 **/
	public $sp_lock_status;
	
	/** 
	 * 定向计划名称
	 **/
	public $sp_name;
	
	/** 
	 * 定向佣金率
	 **/
	public $sp_rate;
	
	/** 
	 * 定向计划是否可用 1-可用 0-不可用
	 **/
	public $sp_status;	
}
?>