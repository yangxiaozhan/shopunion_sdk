<?php

/**
 * 数据结果
 * @author auto create
 */
class Results
{
	
	/** 
	 * 明细类型，1：预估明细，2：结算明细
	 **/
	public $calc_type;
	
	/** 
	 * 奖励明细数据，KV结构。字段释义见文档：https://www.yuque.com/docs/share/7ecf8cf1-7f99-4633-a2ed-f9b6f8116af5?#
	 **/
	public $field_detail;
	
	/** 
	 * 明细记录主键id
	 **/
	public $id;	
}
?>