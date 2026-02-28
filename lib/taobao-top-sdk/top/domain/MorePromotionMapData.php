<?php

/**
 * 更多活动优惠
 * @author auto create
 */
class MorePromotionMapData
{
	
	/** 
	 * 预热优惠利益点文案，如“1件7.92折”、“每200减20”等
	 **/
	public $promotion_desc;
	
	/** 
	 * 优惠结束时间
	 **/
	public $promotion_end_time;
	
	/** 
	 * 优惠ID
	 **/
	public $promotion_id;
	
	/** 
	 * 优惠开始时间
	 **/
	public $promotion_start_time;
	
	/** 
	 * 预热优惠名称，如“商品券”、“跨店满减”、“单品直降”等
	 **/
	public $promotion_title;
	
	/** 
	 * 当天优惠总库存【指定优惠透出，不对外开放】
	 **/
	public $promotion_total_count;
	
	/** 
	 * 优惠使用路径【指定优惠透出，不对外开放】
	 **/
	public $promotion_url;	
}
?>