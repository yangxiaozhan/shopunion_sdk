<?php

/**
 * 国补信息
 * @author auto create
 */
class StateSubsidyInfo
{
	
	/** 
	 * 最高优惠金额（元，如15代表15 元），入参具体IP时，仅展示该IP最高优惠金额
	 **/
	public $max_discount;
	
	/** 
	 * 最高优惠比例（%），入参具体IP时，仅展示该IP最高优惠比例。
	 **/
	public $max_rebate;
	
	/** 
	 * 最低优惠金额（元，如15代表15 元），入参具体IP时，仅展示该IP最低优惠金额
	 **/
	public $min_discount;
	
	/** 
	 * 最低优惠比例（%），入参具体IP时，仅展示该IP最低优惠比例。
	 **/
	public $min_rebate;
	
	/** 
	 * 国补生效区域（省份）。不入参IP时展示各可用省份；入参IP时，全国可用商品展示各可用省份，区域可用商品仅展示IP对应省份。
	 **/
	public $province_list;	
}
?>