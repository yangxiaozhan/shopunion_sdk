<?php

/**
 * 商品佣金信息
 * @author auto create
 */
class FinalIncomeInfo
{
	
	/** 
	 * 商品佣金金额
	 **/
	public $commission_amount;
	
	/** 
	 * 商品佣金比率
	 **/
	public $commission_rate;
	
	/** 
	 * 补贴金额
	 **/
	public $subsidy_amount;
	
	/** 
	 * 补贴比率
	 **/
	public $subsidy_rate;
	
	/** 
	 * 补贴类型
	 **/
	public $subsidy_type;
	
	/** 
	 * 补贴上限；仅在单笔订单命中补贴上限时返回结果否则出参为空
	 **/
	public $subsidy_upper_limit;	
}
?>