<?php

/**
 * 价格促销信息
 * @author auto create
 */
class PromotionInfoMapData
{
	
	/** 
	 * 货品展示标识列表
	 **/
	public $activity_tag_list;
	
	/** 
	 * 优惠促销列表
	 **/
	public $final_promotion_path_list;
	
	/** 
	 * 促销信息-预估到手价(元)。若属于预售商品，付定金时间内，预估到手价=预售尾款预估到手价
	 **/
	public $final_promotion_price;
	
	/** 
	 * 到手价类型，10表示直播间价格
	 **/
	public $final_promotion_target_type;
	
	/** 
	 * 暂不支持
	 **/
	public $future_activity_promotion_path_list;
	
	/** 
	 * 暂不支持
	 **/
	public $future_activity_promotion_price;
	
	/** 
	 * 国家补贴
	 **/
	public $gov_subsidy;
	
	/** 
	 * 更多活动优惠
	 **/
	public $more_promotion_list;
	
	/** 
	 * 促销信息-预估凑单价（元）。预估凑单叠加优惠后的商品单价
	 **/
	public $predict_rounding_up_price;
	
	/** 
	 * 促销信息-凑单价说明，描述凑单价的实现说明。如 “可凑单”或“需买X件”
	 **/
	public $predict_rounding_up_price_desc;
	
	/** 
	 * 标签信息列表
	 **/
	public $promotion_tag_list;
	
	/** 
	 * 商品一口价格
	 **/
	public $reserve_price;
	
	/** 
	 * 折扣价（元） 若属于预售商品，付定金时间内，折扣价=预售价
	 **/
	public $zk_final_price;	
}
?>