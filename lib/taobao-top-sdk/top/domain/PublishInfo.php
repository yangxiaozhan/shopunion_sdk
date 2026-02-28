<?php

/**
 * 淘客推广信息
 * @author auto create
 */
class PublishInfo
{
	
	/** 
	 * 额外奖励活动金额，活动奖励金额的类型与cpa_reward_type字段对应，如果一个商品有多个奖励类型，返回结果使用空格分割
	 **/
	public $cpa_reward_amount;
	
	/** 
	 * 额外奖励活动类型，如果一个商品有多个奖励类型，返回结果使用空格分割，0=预售单单奖励，1=618超级U选单单补
	 **/
	public $cpa_reward_type;
	
	/** 
	 * 当天推广销量。 非实时，约1小时更新
	 **/
	public $daily_promotion_sales;
	
	/** 
	 * 暂不支持
	 **/
	public $future_activity_commission_rate;
	
	/** 
	 * 暂不支持
	 **/
	public $future_activity_time;
	
	/** 
	 * 商品佣金信息
	 **/
	public $income_info;
	
	/** 
	 * 商品信息-收入比率(商品佣金比率+补贴比率)。15.5表示15.5%
	 **/
	public $income_rate;
	
	/** 
	 * 单品淘礼金今日剩余可创建个数
	 **/
	public $tlj_remain_num;
	
	/** 
	 * 前N件佣金信息-前N件佣金生效或预热时透出以下字段
	 **/
	public $topn_info;
	
	/** 
	 * 两小时推广销量。 非实时，约半小时更新
	 **/
	public $two_hour_promotion_sales;	
}
?>