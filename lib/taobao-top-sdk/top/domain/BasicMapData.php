<?php

/**
 * 商品基本信息
 * @author auto create
 */
class BasicMapData
{
	
	/** 
	 * 年销量
	 **/
	public $annual_vol;
	
	/** 
	 * 商品信息-品牌名称
	 **/
	public $brand_name;
	
	/** 
	 * 叶子类目名称
	 **/
	public $category_name;
	
	/** 
	 * 商品信息-是否包邮
	 **/
	public $free_shipment;
	
	/** 
	 * 好评率是否高于行业均值(字段等级SA)
	 **/
	public $h_good_rate;
	
	/** 
	 * 成交转化是否高于行业均值(字段等级SA)
	 **/
	public $h_pay_rate30;
	
	/** 
	 * 退款率是否低于行业均值(字段等级SA)
	 **/
	public $i_rfd_rate;
	
	/** 
	 * 是否加入消费者保障
	 **/
	public $is_prepay;
	
	/** 
	 * 商品链接
	 **/
	public $item_url;
	
	/** 
	 * 一级类目名称
	 **/
	public $level_one_category_name;
	
	/** 
	 * 商品库类型，支持多库类型输出，以英文逗号分隔“,”分隔，1:营销商品主推库，2:内容商品库，3:淘宝主推商品库，如果值为空则不属于1，2，3这三种商品类型
	 **/
	public $material_lib_type;
	
	/** 
	 * 商品主图
	 **/
	public $pict_url;
	
	/** 
	 * 商品所在地
	 **/
	public $provcity;
	
	/** 
	 * 卖家等级(字段等级SA)
	 **/
	public $ratesum;
	
	/** 
	 * 卖家id(字段等级C)
	 **/
	public $seller_id;
	
	/** 
	 * 店铺dsr 评分(字段等级SA)
	 **/
	public $shop_dsr;
	
	/** 
	 * 商品信息-店铺名称
	 **/
	public $shop_title;
	
	/** 
	 * 商品信息-商品短标题
	 **/
	public $short_title;
	
	/** 
	 * 商品小图列表
	 **/
	public $small_images;
	
	/** 
	 * 是否品牌精选，0不是，1是
	 **/
	public $superior_brand;
	
	/** 
	 * H5宝贝详情(字段等级S)
	 **/
	public $taobao_desc_url;
	
	/** 
	 * 商品标题
	 **/
	public $title;
	
	/** 
	 * pc宝贝详情(字段等级S)
	 **/
	public $tmall_desc_url;
	
	/** 
	 * 卖家类型，0表示集市，1表示商城，3表示特价版
	 **/
	public $user_type;
	
	/** 
	 * 30天销量；数据统计截止昨日非实时更新
	 **/
	public $volume;
	
	/** 
	 * 商品信息-商品白底图
	 **/
	public $white_image;	
}
?>