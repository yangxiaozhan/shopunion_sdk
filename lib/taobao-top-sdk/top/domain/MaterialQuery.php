<?php

/**
 * 请求结构
 * @author auto create
 */
class MaterialQuery
{
	
	/** 
	 * 物料类型，1: 商品；2:权益
	 **/
	public $material_type;
	
	/** 
	 * 页码，默认1，取值范围1~100
	 **/
	public $page_no;
	
	/** 
	 * 每页物料id数量，默认20，取值范围1~100
	 **/
	public $page_size;
	
	/** 
	 * 物料主题类型, 1促销活动;2热门主题;3精选榜单;4行业频道等;5其他
	 **/
	public $subject;	
}
?>