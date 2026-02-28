<?php
/**
 * TOP API: taobao.tbk.dg.rta.sr.issue.match request
 * 
 * @author auto create
 * @since 1.0, 2025.12.01
 */
class TbkDgRtaSrIssueMatchRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "taobao.tbk.dg.rta.sr.issue.match";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
