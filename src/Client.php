<?php

namespace FastElephant\Printer;

use GuzzleHttp\Client as HttpClient;

class Client
{
    /**
     * 请求值
     * @var array
     */
    protected $request = [];

    /**
     * 返回值
     * @var array
     */
    protected $response = [];

    /**
     * 设备类型
     * @var
     */
    protected $deviceType;

    /**
     * 映射编号
     * @var
     */
    protected $openId;

    /**
     * Client constructor.
     * @param $openId
     * @param $deviceType
     */
    public function __construct($openId, $deviceType)
    {
        $this->openId = $openId;
        $this->deviceType = $deviceType;
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     *  绑定/编辑
     * @param string $deviceId
     * @param string $deviceKey
     * @return mixed
     */
    public function bind(string $deviceId, string $deviceKey)
    {
        $param = [
            'device_id' => $deviceId,
            'device_key' => $deviceKey,
        ];
        return $this->call('device/printer/bind', $param);
    }

    /**
     * 解绑
     * @param string $deviceId
     * @return mixed
     */
    public function unbind(string $deviceId)
    {
        $param = [
            'device_id' => $deviceId,
        ];
        return $this->call('device/printer/unbind', $param);
    }

    /**
     * 相关信息概览
     * @param int $eventId
     * @param string $deviceId
     * @return array
     */
    public function overview(int $eventId = 0, string $deviceId = '')
    {
        $param = [
            'event_id' => $eventId,
            'device_id' => $deviceId,
        ];
        return $this->call('device/printer/overview', $param);
    }

    /**
     * 保存规则
     * @param string $deviceId
     * @param int $eventId
     * @param array $value
     * @return array
     */
    public function saveRule(string $deviceId, int $eventId, array $value)
    {
        $param = [
            'event_id' => $eventId,
            'device_id' => $deviceId,
            'value' => $value
        ];
        return $this->call('config/rule/save', $param);
    }

    /**
     * 测试打印
     * @param int $eventId
     * @return array
     */
    public function testPrint(int $eventId)
    {
        $param = [
            'event_id' => $eventId,
            'auto' => 0
        ];
        return $this->call('task/print/test', $param);
    }

    /**
     * 打印
     * @param int $eventId
     * @param array $data
     * @param int $auto
     * @return array
     */
    public function print(int $eventId, array $data, int $auto = 0)
    {
        $param = [
            'event_id' => $eventId,
            'data' => $data,
            'auto' => $auto
        ];
        return $this->call('task/print/submit', $param);
    }

    /**
     * @param $path
     * @param $param
     * @return array
     */
    protected function call($path, $param = [])
    {
        $apiUrl = config('printer.baseUrl') . $path;

        $param['business_id'] = config('printer.businessId');
        $param['version'] = config('printer.version');
        $param['device_type'] = $this->deviceType;
        $param['open_id'] = $this->openId;

        $client = new HttpClient(['verify' => false, 'timeout' => config('printer.timeout')]);

        $this->request = $param;

        $errMsg = '';

        $startTime = $this->millisecond();

        try {
            $strResponse = $client->post($apiUrl, ['json' => $this->request])->getBody()->getContents();
        } catch (\Exception $e) {
            $errMsg = $e->getMessage();
            $strResponse = '';
        }

        $expendTime = intval($this->millisecond() - $startTime);

        $this->monitorProcess($path, json_encode($this->request, JSON_UNESCAPED_UNICODE), $strResponse, $expendTime);

        if (!$strResponse) {
            return ['code' => 555, 'msg' => $errMsg ?: '响应值为空', 'request_id' => ''];
        }

        $arrResponse = json_decode($strResponse, true);
        if (!$arrResponse) {
            return ['code' => 555, 'msg' => '响应值格式错误', 'request_id' => ''];
        }

        $this->response = $arrResponse;
        if ($arrResponse['code'] != 0) {
            return ['code' => $arrResponse['code'], 'msg' => $arrResponse['msg'], 'request_id' => $arrResponse['request_id']];
        }

        return ['code' => 0, 'result' => $arrResponse['result'], 'request_id' => $arrResponse['request_id']];
    }

    /**
     * 监控请求过程（交给子类实现）
     * @param $path
     * @param $strRequest
     * @param $strResponse
     * @param $expendTime
     */
    public function monitorProcess($path, $strRequest, $strResponse, $expendTime)
    {
    }

    /**
     * 获取当前时间毫秒时间戳
     * @return float
     */
    protected function millisecond()
    {
        list($mSec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($mSec) + floatval($sec)) * 1000);
    }
}
