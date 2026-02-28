<?php

/**
 * 数据列表
 * @author auto create
 */
class VegasCpaReportDTO
{
	
	/** 
	 * 统计日期（统计活动期内，截止 统计日期 的数据）
	 **/
	public $biz_date;
	
	/** 
	 * 活动相关数据信息
	 **/
	public $ext_info;
	
	/** 
	 * 媒体三段式id，当查询数据为pid维度时返回该字段
	 **/
	public $pid;
	
	/** 
	 * 数据类型:1预估 2结算
	 **/
	public $query_type;
	
	/** 
	 * rid，当查询数据为rid维度时返回该字段
	 **/
	public $relation_id;
	
	/** 
	 * 奖励金额；按入参是预估/结算，区分获得金额为预估or可结算结果；
	 **/
	public $reward_amount;
	
	/** 
	 * 符合奖励要求的累计用户数；按入参是预估/结算，区分用户数为预估or可结算结果；
	 **/
	public $union30d_lx_uv;	
}
?>