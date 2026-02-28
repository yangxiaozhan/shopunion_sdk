<?php

/**
 * 线报内容
 * @author auto create
 */
class MgcInfo
{
	
	/** 
	 * 价格
	 **/
	public $price;
	
	/** 
	 * 价格描述
	 **/
	public $price_desc;
	
	/** 
	 * 文案
	 **/
	public $promotion_summary;
	
	/** 
	 * 发布时间，13位毫秒时间戳
	 **/
	public $publish_time;
	
	/** 
	 * 生效时间，实时线报为0，未来线报为13位毫秒时间戳
	 **/
	public $valid_time;	
}
?>