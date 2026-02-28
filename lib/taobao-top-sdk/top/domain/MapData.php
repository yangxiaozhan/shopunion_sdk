<?php

/**
 * resultList
 * @author auto create
 */
class MapData
{
	
	/** 
	 * 选品库信息
	 **/
	public $favorites_info;
	
	/** 
	 * 商品基础信息
	 **/
	public $item_basic_info;
	
	/** 
	 * 商品信息-淘宝客新商品id；使用说明参考《淘宝客新商品ID升级》白皮书：https://www.yuque.com/taobaolianmengguanfangxiaoer/zmig94/tfyt0pahmlpzu2ud
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
	
	/** 
	 * 商品库范围信息
	 **/
	public $scope_info;
	
	/** 
	 * 天猫榜单信息
	 **/
	public $tmall_rank_info;
	
	/** 
	 * 前N件佣金信息-前N件佣金生效或预热时透出以下字段
	 **/
	public $topn_info;	
}
?>