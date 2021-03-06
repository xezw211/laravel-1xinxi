<?php 
	namespace Hsky\XinXi;

	use Ixudra\Curl\CurlService;

	class XinXi{
		/**
		 * 发送 普通短信
		 * @param		string $verify  验证码
		 * @param       array  $mobile  手机号
		 * @param       array  $sendTime 发送时间
		 * @author		xezw211@12.com
		 * @date		2016-09-16 07:24:54
		 * @return		
		 */
		public function sendNormalInfo($verify, $mobile, $sendTime = '', $extno = ''){
			$returnData = [
				'result' => false,
				'message' => '数据错误'
			];
			
			$service = new CurlService();

			/*数组则拼接*/
			if(is_array($mobile)){
				$mobile = implode(',', $mobile);
			}

			$params = [
				'name' => config('xinxi.account'),     //必填参数。用户账号
				'pwd' => config('xinxi.api_pwd'),     //必填参数。（web平台：基本资料中的接口密码）
				'content' => sprintf(config('xinxi.normal_template'), $verify),   //必填参数。发送内容（1-500 个汉字）UTF-8编码
				'mobile' => $mobile,   //必填参数。手机号码。多个以英文逗号隔开
				'stime' => $sendTime,   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
				'sign' => config('xinxi.sign'),    //必填参数。用户签名。
				'type'=>'pt',  //必填参数。固定值 pt
				'extno' => $extno    //可选参数，扩展码，用户定义扩展码，只能为数字
			];

			$returnSendData = $this->send($params);
			return array_merge($returnData, $returnSendData, [
				'result' => true,
				'message' => '发送成功'
			]);
		}

		/**
		 * 发送 自定义短信
		 * @param		string $content  自定义发送内容
		 * @param       array  $mobile  手机号
		 * @param       array  $sendTime 发送时间
		 * @author		xezw211@12.com
		 * @date		2016-09-16 07:24:54
		 * @return		
		 */
		public function sendCustomInfo($content, $mobiles, $sendTime = '', $extno = ''){
			$returnData = [
				'result' => false,
				'message' => '数据错误'
			];

			if(empty($mobiles)){
				return $returnData;
			}

			/*短信发送内容拼接*/
			$sendContent = '';
			foreach($mobiles as $mobile){
				// 内容#@#号码#@@#内容#@#号码
				$sendContent .= $content . '#@#' . $mobile . '#@@#';
			}
			$sendContent = rtrim($sendContent, '#@@#');

			$params = [
				'name' => config('xinxi.account'),     //必填参数。用户账号
				'pwd' => config('xinxi.api_pwd'),     //必填参数。（web平台：基本资料中的接口密码）
				'content' => $sendContent,   //必填参数。发送内容（1-500 个汉字）UTF-8编码
				'stime' => $sendTime,   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
				'sign' => config('xinxi.sign'),    //必填参数。用户签名。
				'type'=>'gx',  //必填参数。固定值 pt
				'extno' => $extno    //可选参数，扩展码，用户定义扩展码，只能为数字
			];

			$returnSendData = $this->send($params);
			return array_merge($returnData, $returnSendData, [
				'result' => true,
				'message' => '发送成功'
			]);
		}

		/*发送请求*/
		private function send($params){
			$service = new CurlService();
			$response =  $service->to(config('xinxi.api_url'))->withData($params)->post();
			return $this->deal($response);
		}

		/*处理返回数据*/
		private function deal($response){
			$code = substr($response, 0, 1 );

			$returnData = [];

			if($code == '0'){
				list($code, $sendid, $invalidcount, $successcount, $blackcount, $msg) = explode(',', $response);
				$returnData = [
					'code' => $code,
					'sendid' => $sendid,
					'invalidcount' => $invalidcount,
					'successcount' => $successcount,
					'blackcount' => $blackcount,
					'msg' => $msg,
				];
			}else{
				list($code, $msg) = explode(',', $response);
				$returnData = [
					'code' => $code,
					'msg' => $msg,
				];
			}

			return $returnData;
		}
	}
