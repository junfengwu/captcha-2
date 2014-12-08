<?php

return array(

	'id'        => 'captcha',
	'fontsize'  => 18,						// 验证码字体大小(px)
	'curve'     => true,					// 是否画混淆曲线
	'noise'     => false,					// 是否添加杂点	
	'height'    => 42,						// 验证码图片高度
	'width'     => 130,						// 验证码图片宽度
	'length'    => 4,						// 验证码位数
	'font'      => '',						// 验证码字体，不设置随机获取
	'bgcolor'   => array(57, 179, 215),		// 背景颜色
	'sensitive' => false,
	'quality'   => 80
	
);