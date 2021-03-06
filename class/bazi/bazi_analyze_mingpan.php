<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 先天命盘分析
 *    装十神
 *    排大运流年
 **/
class bazi_analyze_mingpan
{
    public function analyze(&$baziCase)
    {   
		$bazi = &$baziCase->data;
        //1. 分析日元: 对照日元查其他天干对应的十神,十神对应的天干
        self::analyzeRiYuan($bazi);
        //2. 分析月令: 五行旺相休囚死
        self::analyzeYueLing($bazi);
        //3. 八字干支详情&统计五行,天干,十神在干支藏干中的出现次数
        self::analyzeGanZhi($bazi);
		//4. 排大运
		self::paiDaYun($bazi);
		//5. 排流年
		self::paiLiuNian($bazi);
		return;
    }

    // 日元分析: 对照日元查各天干及地支藏干对应的十神
    private static function analyzeRiYuan(&$bazi)
    {/*{{{*/
        $rigan = $bazi['gan'][2]['z'];  //!< 日元
        $bazi['riYuan'] = $rigan;
        $dictWuXingMap = &$bazi['dict']['wuxing'];
        $dictGanMap = &$bazi['dict']['tiangan'];
        $dictShiShenMap = &$bazi['dict']['shishen'];
        $dictDiZhiMap = &$bazi['dict']['dizhi'];
        // 天干十神
        foreach ($dictGanMap as $gan => &$item) {
            $shishen = bazi_base::$SHI_SHEN_TABLE_MAP[$rigan.$gan];
            $shiShenInfo = $dictShiShenMap[$shishen];
            $item['shishen'] = $shishen;
            $dictShiShenMap[$shishen]['gan'] = $gan;
            // 五行映射到十神
            if (!isset($dictWuXingMap[$item['wuxing']]['shishens'])) {
                $dictWuXingMap[$item['wuxing']]['shishens'] = array();
            }
            $dictWuXingMap[$item['wuxing']]['shishens'][] = $shishen;
        }
        // 地支藏干&地势
        foreach ($dictDiZhiMap as $zhi => &$item) {
            foreach ($item['canggan'] as &$canggan) {
			    $zhiganinfo = $dictGanMap[$canggan];
                $canggan = array (
                    'gan'     => $canggan,
                    'wuxing'  => $zhiganinfo['wuxing'],
                    'shishen' => $zhiganinfo['shishen'],
                );
		    }
            // 地支十神为主气藏干十神
			$item['shishen'] = $item['canggan'][0]['shishen'];
            // 日元地势
        }
    }/*}}}*/

    // 月令分析: 对照月令看五行的旺相休囚死
    private static function analyzeYueLing(&$bazi)
    {/*{{{*/
        $yueLing = $bazi['zhi'][1]['z'];  //!< 月令
        $bazi['yueLing'] = $yueLing;
        $dictWuXingMap = &$bazi['dict']['wuxing'];
        $dictDiZhiMap = &$bazi['dict']['dizhi'];
        $yueLingWuXing = $dictDiZhiMap[$yueLing]['wuxing'];
        $yueLingJiJie = $dictWuXingMap[$yueLingWuXing]['siji'];
        $map = array (
            '春天' => array('木','火','水','金','土'),
            '夏天' => array('火','土','木','水','金'),
            '秋天' => array('金','水','土','火','木'),
            '冬天' => array('水','木','金','土','火'),
            '四季' => array('土','金','火','木','水'),
        );
        $arr = $map[$yueLingJiJie];
        $ss = array('旺','相','休','囚','死');
        $i=0;
        foreach ($arr as $wuxing) {
            $dictWuXingMap[$wuxing]['state'] = $ss[$i];
            ++$i;
        }
    }/*}}}*/

    // 干支分析: 十神,五行,以及统计信息
    private static function analyzeGanZhi(&$bazi)
    {/*{{{*/
        $dictWuXingMap = &$bazi['dict']['wuxing'];
        $dictGanMap = &$bazi['dict']['tiangan'];
        $dictZhiMap = &$bazi['dict']['dizhi'];
        $dictShiShenMap = &$bazi['dict']['shishen'];
        //1. 初始化统计数据
        foreach ($dictWuXingMap as $wuxing => &$item) {
            $item['statInGan'] = 0;     //!< 天干中五行的个数
            $item['statInZhi'] = 0;     //!< 地支中五行的个数
            $item['statInZhiCang'] = 0; //!< 地支藏干中五行的个数
        }
        foreach ($dictGanMap as $gan => &$item) {
            $item['statInGan'] = 0;     //!< 天干中个数
            $item['statInZhiCang'] = 0; //!< 地支藏干中个数
        }
        foreach ($dictShiShenMap as $shishen => &$item) {
            $item['statInGan'] = 0;     //!< 天干中十神个数
            $item['statInZhiCang'] = 0; //!< 地支藏干中十神个数
        }
        //2. 干支分析
        for ($i=0;$i<4;++$i) {
            //2-1. 柱干
			$gan = &$bazi['gan'][$i];
            $ganz = $gan['z'];
			$ganInfo = &$dictGanMap[$ganz];
            $gan['wuxing'] = $ganInfo['wuxing'];    //!< 天干五行
            $gan['yy'] = $ganInfo['yy'];            //!< 天干阴阳
            ++$dictGanMap[$ganz]['statInGan'];      //!< 统计天干中五行的个数
            ++$dictWuXingMap[$ganInfo['wuxing']]['statInGan']; //!< 统计天干中个数
            if ($i!=2) {
                $gan['shishen'] = $ganInfo['shishen'];
                ++$dictShiShenMap[$ganInfo['shishen']]['statInGan'];  //!< 统计天干中十神个数
            }
            //2-2. 柱支
			$zhi = &$bazi['zhi'][$i];
            $zhiz = $zhi['z'];
            $zhiInfo = &$dictZhiMap[$zhiz];
            $zhi['shishen'] = $zhiInfo['shishen'];   //!< 十神
            $zhi['dishi'] = bazi_base::zhangsheng_state($bazi['riYuan'],$zhiz);  //!< 日元地势
            $zhi['wuxing'] = $zhiInfo['wuxing'];     //!< 地支五行
            $zhi['yy'] = $zhiInfo['yy'];             //!< 地支阴阳
            ++$dictWuXingMap[$zhiInfo['wuxing']]['statInZhi'];  //!< 统计地支中五行个数
            foreach ($zhiInfo['canggan'] as $canggan) {
                ++$dictWuXingMap[$canggan['wuxing']]['statInZhiCang']; //!< 统计地支藏干中五行个数
                ++$dictGanMap[$canggan['gan']]['statInZhiCang']; //!< 统计地支藏干中天干个数
                ++$dictShiShenMap[$canggan['shishen']]['statInZhiCang']; //!< 统计地支藏干中十神个数
            }
		}
    }/*}}}*/

    // 五行&天干&十神力量旺衰分析(!!!!旺衰核心算法!!!!)
    private static function analyzePower(&$bazi)
    {/*{{{*/
        $dictWuXingMap = &$bazi['dict']['wuxing'];
        $dictGanMap = &$bazi['dict']['tiangan'];
        $dictShiShenMap = &$bazi['dict']['shishen'];
        //1. 初始化力量值
        foreach ($dictWuXingMap as $wuxing => &$item) {
            $item['power'] = 0;
        }
        foreach ($dictGanMap as $gan => &$item) {
            $item['power'] = 0;
        }
        foreach ($dictShiShenMap as $shishen => &$item) {
            $item['power'] = 0;
        }
        //2. 五行力量
        $powerSum = 0;
        $lingPowerMap = array('旺'=>5,'相'=>4,'休'=>3,'囚'=>2,'死'=>1);
        $shengWoWuXingMap = array('木'=>'水','火'=>'木','土'=>'火','金'=>'土','水'=>'金');
        foreach ($dictWuXingMap as $wuxing => &$item) {
            $statAll = $item['statInGan']+$item['statInZhiCang'];  //!< 天干及地支藏干中的五行个数统计
            if ($statAll==0) continue;  //!< 五行未出现
            //2-1. 五行得令力量
            $lingPower = $lingPowerMap[$item['state']];
            //2-2. 五行得根力量
            $genPower = 0;
            if ($item['statInGan']>0) {  // 得根前提: 自己要透天干
                $genPower = $item['statInZhiCang'];
            }
            //2-3. 五行得生力量
            $shengPower = 0;
            if ($genPower>0) { // 得生力量前提: 自己要得根(注意:得令一定会得根)
                $shengWoXing = $shengWoWuXingMap[$wuxing];
                $shengWoXingInfo = $dictWuXingMap[$shengWoXing];
                $shengPower = $shengWoXingInfo['statInGan']+$shengWoXingInfo['statInZhi'];
            }
            //2-4. 五行得助力量
            $zhuPower = $item['statInGan']+$item['statInZhi']-1;
            // 力量综合算法
            $item['powerDis'] = array($lingPower,$genPower,$shengPower,$zhuPower);
            $item['power'] = $lingPower * 2 + $genPower * 1.5 + $shengPower * 1.2 + $zhuPower;
            $powerSum += $item['power'];
        }
        // 五行力量归一化(取百分比)
        foreach ($dictWuXingMap as $wuxing => &$item) {
            $item['power'] = round($item['power']*100 / $powerSum,2);
        }

        //3. 天干力量
        $powerSum = 0;
        foreach ($dictGanMap as $gan => &$item) {
            // 天干及地支藏干中均未出现
            if ($item['statInGan']+$item['statInZhiCang']==0) continue;
            //3-1. 得令力量
            $wuxing = $item['wuxing'];
            $wuxingInfo = $dictWuXingMap[$wuxing];
            $lingPower = $lingPowerMap[$wuxingInfo['state']];
            //3-2. 得根力量
            $genPower = 0;
            if ($item['statInGan']>0) { // 得根前提: 自己要透天干
                $genPower = $item['statInZhiCang'];
            }
            //3-4. 得生力量
            $shengPower = 0;
            if ($genPower>0) { // 得生力量前提: 自己要得根(注意:得令一定会得根)
                $shengWoXing = $shengWoWuXingMap[$wuxing];
                $shengWoXingInfo = $dictWuXingMap[$shengWoXing];
                $shengPower = $shengWoXingInfo['statInGan']+$shengWoXingInfo['statInZhi'];
            }
            //3-3. 得助力量
            $zhuPower = $wuxingInfo['statInGan']+$wuxingInfo['statInZhi']-1;

            // 力量综合算法
            $item['powerDis'] = array($lingPower,$genPower,$shengPower,$zhuPower);
            $item['power'] = $lingPower * 2 + $genPower * 1.5 + $shengPower * 1.2 + $zhuPower;
            $powerSum += $item['power'];
        }
        // 天干力量归一化(取百分比)
        foreach ($dictGanMap as $gan => &$item) {
            $item['power'] = round($item['power']*100 / $powerSum,2);
        }

        //4. 十神力量
        $powerSum = 0;
        foreach ($dictShiShenMap as $shishen => &$item) {
            // 天干及地支藏干中均未出现
            if ($item['statInGan']+$item['statInZhiCang']==0) continue;
            $gan = $item['gan'];
            $ganInfo = $dictGanMap[$gan];
            $item['power'] = $ganInfo['power'];   //!< 十神力量取对应天干的力量
            $powerSum += $item['power'];
        }
        // 十神力量归一化(取百分比)
        foreach ($dictShiShenMap as $shishen => &$item) {
            $item['power'] = round($item['power']*100 / $powerSum,2);
        }
    }/*}}}*/

    // 力量排序
    private static function analyzePowerSort(&$bazi)
    {/*{{{*/
        $bazi['powerSort'] = array (
            'wuxing'  => array(),
            'tiangan' => array(),
            'shishen' => array(),
        );
        $dictWuXingMap = &$bazi['dict']['wuxing'];
        $dictGanMap = &$bazi['dict']['tiangan'];
        $dictShiShenMap = &$bazi['dict']['shishen'];
        //1.
        foreach ($dictWuXingMap as $wuxing => &$item) {
            $bazi['powerSort']['wuxing'][] = array (
                'wuxing' => $wuxing,
                'power'  => $item['power'],
            );
        }
        foreach ($dictGanMap as $gan => &$item) {
            $bazi['powerSort']['tiangan'][] = array (
                'gan'   => $gan,
                'power' => $item['power'],
            );
        }
        foreach ($dictShiShenMap as $shishen => &$item) {
            $bazi['powerSort']['shishen'][] = array (
                'shishen' => $shishen,
                'power'   => $item['power'],
            );
        }
        //2. 排序
        foreach ($bazi['powerSort'] as &$arr) {
            bazi_utils::array_sort_by($arr,'power','DESC');
        }
    }/*}}}*/

	// 排大运
	public function paiDaYun(&$bazi)
	{/*{{{*/
		$rigan   = $bazi["gan"][2]['z']; 	//!< 日元
		$yue_gan = $bazi['gan'][1]['z'];	//!< 月干
		$yue_zhi = $bazi['zhi'][1]['z'];	//!< 月支
		$gender  = $bazi['gender'];
		$nian_gan_yy = $bazi['gan'][0]['yy'];    //!< 年干阴阳属性
		$sort = 1;                                       //!< 阳男阴女顺排
		if (($nian_gan_yy=='阴' && $gender=='男') ||     //!< 阴男阳女逆排
		    ($nian_gan_yy=='阳' && $gender=='女')) {
			$sort = -1;
		}
		// 计算大运起始年份
		$sheng_nian = $bazi['birthYear'];    //!< 命主出生年份
		$qiyun_nian = C::t('#bazi#bazi_calendar')->get_qiyun_nian($bazi['birthDay'],$sort);
		$bazi['qiYunNian'] = $qiyun_nian;
		$bazi['qiYunSui']  = $qiyun_nian-$sheng_nian;
		// 排大运
		$bazi['dayun']=C::m('#bazi#bazi_theory')->get_ganzhi_seq($yue_gan,$yue_zhi,$sort,9);
		$i = 0;
		foreach ($bazi['dayun'] as &$row) {
			$row['nian'] = $qiyun_nian + $i*10;        //!< 10年换运
			$row['age']  = $bazi['qiYunSui'] + $i*10;  //!< 岁数
            $row['gan']  = $row['gan'];
            $row['zhi']  = $row['zhi'];
			$row['liunian'] = array();
			for ($offset=0;$offset<10;++$offset) {
				$row['liunian'][] = $row['nian']+$offset;
			}
			++$i;
		}
	}/*}}}*/

	// 排流年
	public function paiLiuNian(&$bazi)
	{/*{{{*/
		$liunian = array();					//!< 流年map,key为年份，如2000
		$qiyun_nian = $bazi['qiYunNian'];  	//!< 起运年(从起运年开始排)
		$qiyun_sui = $bazi['qiYunSui'];    	//!< 起运岁
		$rigan = $bazi["riYuan"];           //!< 日元
		// 获取出生那年的干支
		$theory = C::m('#bazi#bazi_theory');
		$sheng_nian_gan_zhi = $theory->get_gan_zhi_of_year($bazi['birthYear']);
		$gan = $sheng_nian_gan_zhi['gan'];	//!< 出生那年的天干（不一定是八字中的年干）
		$zhi = $sheng_nian_gan_zhi['zhi'];  //!< 出生那边的地支（不一定是八字中的年支）
		// 从起大运年开始排，直到大运排完
		for ($i=0;$i<10;++$i) {
			$k=0;
			foreach ($bazi['dayun'] as $row) {
				$idx = $qiyun_sui + $i + ($k*10);
				$nian = $row['nian'] + $i;
				// 流年信息
				$ganzhi = $theory->get_gan_zhi($gan,$zhi,$idx);
				$ganzhi['nian'] = $nian;
				$ganzhi['age'] = $nian-$bazi['birthYear'];      //!< 岁数
				$ganzhi['dayun_idx'] = $i;			//!< 大运索引
				$liunian[$nian] = $ganzhi;
				++$k;
			}
		}
		$bazi['liunian'] = $liunian;
	}/*}}}*/

}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
