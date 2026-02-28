<?php

/**
 * 预售信息
 * @author auto create
 */
class PresaleInfo
{
	
	/** 
	 * 预售商品-定金（元）
	 **/
	public $presale_deposit;
	
	/** 
	 * 预售商品-优惠信息
	 **/
	public $presale_discount_fee_text;
	
	/** 
	 * 预售商品-付定金结束时间（毫秒）
	 **/
	public $presale_end_time;
	
	/** 
	 * 预售商品-付定金开始时间（毫秒）
	 **/
	public $presale_start_time;
	
	/** 
	 * 预售商品-付尾款结束时间（毫秒）
	 **/
	public $presale_tail_end_time;
	
	/** 
	 * 预售商品-付尾款开始时间（毫秒）
	 **/
	public $presale_tail_start_time;	
}
?>