<?php

/**
 * 返回结果
 * @author auto create
 */
class MediaIssueDTO
{
	
	/** 
	 * 当前媒体(不在任何媒体组时)或所在的媒体组是否可直塞
	 **/
	public $can_issue;
	
	/** 
	 * 直塞红包数量较低时的提示信息
	 **/
	public $extra_msg;	
}
?>