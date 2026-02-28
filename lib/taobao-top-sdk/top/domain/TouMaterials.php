<?php

/**
 * 返回结果
 * @author auto create
 */
class TouMaterials
{
	
	/** 
	 * 结束时间，Unix时间戳
	 **/
	public $end_time;
	
	/** 
	 * 物料id
	 **/
	public $material_id;
	
	/** 
	 * 物料集合名称
	 **/
	public $material_name;
	
	/** 
	 * 物料类型，1: 商品；2:权益
	 **/
	public $material_type;
	
	/** 
	 * 开始时间，Unix时间戳
	 **/
	public $start_time;
	
	/** 
	 * 物料主题类型, 1促销活动;2热门主题;3精选榜单;4行业频道等;5其他
	 **/
	public $subject;	
}
?>