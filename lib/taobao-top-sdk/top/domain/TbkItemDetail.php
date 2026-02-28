<?php

/**
 * 仅淘宝客商品，字段值根据API赋权等级输出
 * @author auto create
 */
class TbkItemDetail
{
	
	/** 
	 * 输入的（新）商品ID
	 **/
	public $input_item_iid;
	
	/** 
	 * 商品基本信息
	 **/
	public $item_basic_info;
	
	/** 
	 * 商品ID
	 **/
	public $item_id;
	
	/** 
	 * 预售信息
	 **/
	public $presale_info;
	
	/** 
	 * 价格促销信息
	 **/
	public $price_promotion_info;
	
	/** 
	 * 淘客推广信息
	 **/
	public $publish_info;	
}
?>