<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
/**
 * 插件设置 
 * C::m('#bazi#bazi_setting')->get()
 **/
class model_bazi_setting
{
	// 获取默认配置
    public function getDefault()
    {
		$setting = array (
			// 屏蔽所有discuz页面
			'disable_discuz' => 0,
			// 页面风格
			'page_style' => 'default',
            // 页面标题
            'page_title' => '掐指一算',
			// 版权信息
			'page_copyright' => '掐指一算 | 沪ICP备17014477号',
            // 预测页&合婚页loading加载延迟
            'loading_ms' => 1500,
		);
		return $setting;
    }

    // 获取配置
	public function get()
	{
		$setting = $this->getDefault();
		global $_G;
		if (isset($_G['setting']['bazi_config'])){
			$config = unserialize($_G['setting']['bazi_config']);
			foreach ($setting as $key => &$item) {
				if (isset($config[$key])) $item = $config[$key];
			}
		}
		return $setting;
	}
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
