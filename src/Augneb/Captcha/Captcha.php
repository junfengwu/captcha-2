<?php namespace Augneb\Captcha;

use Config, Str, Session, Hash, URL;

/**
 *
 * A Simple Captcha Package for Laravel 4 
 * 
 * @version 1.0.0
 * @link http://github.com/augneb/captcha
 *
 */

class Captcha
{
	/**
     * @var  Captcha  singleton instance of the Useragent object
     */
    protected static $singleton;

    /**
     * @var  Captcha config instance of the Captcha::$config object
     */
    private static $config;

	public static function instance()
    {
    	if ( ! Captcha::$singleton)
    	{
    		self::$config = Config::get('captcha::config');
    		self::$config['assets'] = __DIR__ . '/../../../fonts/';

    		Captcha::$singleton = new Captcha();
    	}

    	return Captcha::$singleton;
    }

    /**
     * Generates a captcha image, writing it to the output
     *
     * @access	public
     * @return	img
     */
    public static function create($id = null)
    {
    	$captchaChar = Str::random(static::$config['length']);

    	Session::put('captchaHash', Hash::make(static::$config['sensitive'] === true ? $captchaChar : Str::lower($captchaChar)));

    	$captchaId = $id ?: static::$config['id'];

    	// 图片宽(px)
		$captchaWidth = static::$config['width'] 
						?: (static::$config['length'] * static::$config['fontsize'] * 1.5 + static::$config['length'] * static::$config['fontsize'] / 2);
		// 图片高(px)
		$captchaHeight = static::$config['height'] ?: static::$config['fontsize'] * 2.5;

		// 字体
		$fonts = [];
		foreach (glob(static::$config['assets'] . '*.ttf') as $file)
			$fonts[] = end(explode('/', str_replace('\\', '/', $file)));

		// 未设置字体则随机
		if (static::$config['font'] and in_array(static::$config['font'], $fonts)) 
			$captchaFont = static::$config['assets'] . static::$config['font'];
		else
			$captchaFont = static::$config['assets'] . $fonts[array_rand($fonts)];

		// 建立一幅 static::$config['widht'] x static::$config['height'] 的图像
		$captchaImage = imagecreate($captchaWidth, $captchaHeight);

		// 设置背景	  
		imagecolorallocate(
			$captchaImage, 
			static::$config['bgcolor'][0], 
			static::$config['bgcolor'][1], 
			static::$config['bgcolor'][2]
		); 

		// 验证码字体随机颜色
		$captchaColor = imagecolorallocate($captchaImage, mt_rand(255, 255), mt_rand(255, 255), mt_rand(255, 255));
		
		// 绘杂点
		if (static::$config['noise']) 
			self::createNoise($captchaImage);

		// 绘干扰线
		if (static::$config['curve']) 
			self::createCurve($captchaImage, $captchaColor);

		// 验证码第N个字符的左边距
		$leftMargin = 0;
		for ($i = 0; $i < static::$config['length']; $i++) 
		{
			$leftMargin += mt_rand(static::$config['fontsize'] * 1.2, static::$config['fontsize'] * 1.6);
			imagettftext(
				$captchaImage, 						// img
				static::$config['fontsize'], 		// size
				mt_rand(-40, 40), 					// angle
				$leftMargin, 						// x
				static::$config['fontsize'] * 1.6, 	// y
				$captchaColor, 						// color
				$captchaFont, 						// fontfile
				$captchaChar[$i] 					// text
			);
		}

		imagealphablending($captchaImage, false);

		header('Cache-Control:private, no-cache, no-store, max-age=0, must-revalidate');
		header('Pragma: no-cache');
		header("Content-type: image/jpg");
		header('Content-Disposition: inline; filename=' . $captchaId . '.jpg');
		imagejpeg($captchaImage, null, static::$config['quality']);
		imagedestroy($captchaImage);
    }
	
	/** 
	 * create curve
	 */
	private static function createCurve($captchaImage, $captchaColor) 
	{
		$px = $py = 0;
		
		// 曲线前部分
		$A = mt_rand(1, static::$config['height']/2);				   			 // 振幅
		$b = mt_rand(-static::$config['height']/4, static::$config['height']/4); // Y轴方向偏移量
		$f = mt_rand(-static::$config['height']/4, static::$config['height']/4); // X轴方向偏移量
		$T = mt_rand(static::$config['height'], static::$config['widht']*2);     // 周期
		$w = (2* M_PI)/$T;
						
		$px1 = 0; // 曲线横坐标起始位置
		$px2 = mt_rand(static::$config['widht']/2, static::$config['widht'] * 0.8); // 曲线横坐标结束位置

		for ($px = $px1; $px <= $px2; $px ++) 
		{
			if ($w != 0) 
			{
				$py = $A * sin($w*$px + $f)+ $b + static::$config['height']/2; // y = Asin(ωx+φ) + b
				$i = (int) (static::$config['fontsize']/5);
				while ($i > 0) 
				{
					// 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多		
					imagesetpixel($captchaImage, $px + $i , $py + $i, $captchaColor);		
					$i--;
				}
			}
		}
		
		// 曲线后部分
		$A = mt_rand(1, static::$config['height']/2);				  // 振幅		
		$f = mt_rand(-static::$config['height']/4, static::$config['height']/4);   // X轴方向偏移量
		$T = mt_rand(static::$config['height'], static::$config['widht']*2);  // 周期
		$w = (2* M_PI)/$T;		
		$b = $py - $A * sin($w*$px + $f) - static::$config['height']/2;
		$px1 = $px2;
		$px2 = static::$config['widht'];

		for ($px = $px1; $px <= $px2; $px ++) 
		{
			if ($w != 0) 
			{
				$py = $A * sin($w*$px + $f)+ $b + static::$config['height']/2;  // y = Asin(ωx+φ) + b
				$i = (int) (static::$config['fontsize']/5);
				while ($i > 0) 
				{
					imagesetpixel($captchaImage, $px + $i, $py + $i, $captchaColor);	
					$i--;
				}
			}
		}
	}

	/**
	 * create noise
	 */
	private static function createNoise($captchaImage) 
	{
		for ($i = 0; $i < 10; $i++)
		{
			$noisecolor = imagecolorallocate($captchaImage, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
			for ($j = 0; $j < 5; $j++) 
				imagestring($captchaImage, 5, mt_rand(-10, static::$config['width']),  mt_rand(-10, static::$config['height']), Str::random(1), $noisecolor);
		}
	}

	/**
     * Checks if the supplied captcha test value matches the stored one
     * 
     * @param	string	$value
     * @access	public
     * @return	bool
     */
    public static function check($value)
    {

		$captchaHash = Session::get('captchaHash');

        return $value != null && $captchaHash != null && Hash::check(static::$config['sensitive'] === true ? $value : Str::lower($value), $captchaHash);
    }

    /**
     * Returns an URL to the captcha image
     * For example, you can use in your view something like
     * <img src="<?php echo Captcha::img(); ?>" alt="" />
     *
     * @access	public
     * @return	string
     */
    public static function img() 
    {
		return URL::to('captcha?' . mt_rand(100000, 999999));
    }
}
