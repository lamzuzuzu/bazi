<?php
/**
 * 安装程序调用,地支表数据
 **/

$table = DB::table('bazi_dict_dizhi');
$cols = '(`name`,`num`,`yy`,`wuxing`,`shengxiao`,`canggan`)';
$sql = "INSERT IGNORE INTO $table $cols VALUES ".<<<EOF
('子',0, '阳','水','鼠','癸'),
('丑',1, '阴','土','牛','己,癸,辛'),
('寅',2, '阳','木','虎','甲,丙,戊'),
('卯',3, '阴','木','兔','乙'),
('辰',4, '阳','土','龙','戊,乙,癸'),
('巳',5, '阴','火','蛇','丙,庚,戊'),
('午',6, '阳','火','马','丁,己'),
('未',7, '阴','土','羊','己,丁,乙'),
('申',8, '阳','金','猴','庚,壬,戊'),
('酉',9, '阴','金','鸡','辛'),
('戌',10,'阳','土','狗','戊,辛,丁'),
('亥',11,'阴','水','猪','壬,甲')
EOF;
runquery($sql);
