<?php
namespace app\index\controller;
use think\View;
use think\Db;
use think\Controller;
use think\Config;
use think\Cookie;
use think\Common;

define('RSA_PUBLIC', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCK/yg9ccAyKyJwC/3kiYYsFlHX/ZPLUoqYvly+UkkBUr5PzIYV02qUmEg4wweO+/eDsFlqPgaR33FQ5+fCNkTfrCX5mzw9A+qSZ0Rznub5e1Kmf8gjyPqVDnLmwKEa8jrEqF3xo5cab26AmRpwCiy1aB+S0o/CqmJDJxUykYJ7BQIDAQAB
-----END PUBLIC KEY-----');
 
 
define('RSA_PRIVATE','-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAIr/KD1xwDIrInAL/eSJhiwWUdf9k8tSipi+XL5SSQFSvk/MhhXTapSYSDjDB47794OwWWo+BpHfcVDn58I2RN+sJfmbPD0D6pJnRHOe5vl7UqZ/yCPI+pUOcubAoRryOsSoXfGjlxpvboCZGnAKLLVoH5LSj8KqYkMnFTKRgnsFAgMBAAECgYEAgNicJbEfV6ISj0keds5g2Ndr0MuYSD7giUzVTfua/yYDkpdlqC/NuaccM7nedNXvEFzV1h1fG7PEKBqBBNAnsM33gHjNLn5Xi1/DnPt1ccK0XF0A7BU2DQlqkFKavnBimOAOG9ksxewy3ZJsmtxks3UYu+UJWpNHqB6wAa56M2ECQQC+U6TDf37gndljqSwT/vh7oMC77cdtgzgrzo3x0pnOKxllVTYZpMUdoYZ535JF28Z8lPtIymPLQ0dbxyhYhOJ5AkEAuvVRWR1VQEuoZKDCNacRAcJcbGERHf0g6BaKhM5BcmbvG4MFYNMVGXc1NETx3PG0FyIzUtmRzonL4E52x5cZ7QJBAITL2bdqWv2gRZEK9a1SBtBDvpahdreLigLO0S18c0Jtwf95MBE+bSaakDiy7N1/VgOQ86+7P1wQqlZ4JEd3GIkCQAoUIX+JWkguC/Toya90wzDyFmNtVCvmsnhwhqUkLVkKfYdhJ9ARcQi/aWnY8aT0jr3UhSnJOtgEi64a7MJTvf0CQFgktaTvBWvLPjJbp1eNfgmPgoSe3xgviLthOlsIv0Y47+KX4VaS2fcqNP4eIUSJ3rllD9m3pE5Oh7jl3ZO+12I=
-----END PRIVATE KEY-----');

class Test extends Controller
{
		private $pubKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCK/yg9ccAyKyJwC/3kiYYsFlHX/ZPLUoqYvly+UkkBUr5PzIYV02qUmEg4wweO+/eDsFlqPgaR33FQ5+fCNkTfrCX5mzw9A+qSZ0Rznub5e1Kmf8gjyPqVDnLmwKEa8jrEqF3xo5cab26AmRpwCiy1aB+S0o/CqmJDJxUykYJ7BQIDAQAB';
        private $priKey = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAIr/KD1xwDIrInAL/eSJhiwWUdf9k8tSipi+XL5SSQFSvk/MhhXTapSYSDjDB47794OwWWo+BpHfcVDn58I2RN+sJfmbPD0D6pJnRHOe5vl7UqZ/yCPI+pUOcubAoRryOsSoXfGjlxpvboCZGnAKLLVoH5LSj8KqYkMnFTKRgnsFAgMBAAECgYEAgNicJbEfV6ISj0keds5g2Ndr0MuYSD7giUzVTfua/yYDkpdlqC/NuaccM7nedNXvEFzV1h1fG7PEKBqBBNAnsM33gHjNLn5Xi1/DnPt1ccK0XF0A7BU2DQlqkFKavnBimOAOG9ksxewy3ZJsmtxks3UYu+UJWpNHqB6wAa56M2ECQQC+U6TDf37gndljqSwT/vh7oMC77cdtgzgrzo3x0pnOKxllVTYZpMUdoYZ535JF28Z8lPtIymPLQ0dbxyhYhOJ5AkEAuvVRWR1VQEuoZKDCNacRAcJcbGERHf0g6BaKhM5BcmbvG4MFYNMVGXc1NETx3PG0FyIzUtmRzonL4E52x5cZ7QJBAITL2bdqWv2gRZEK9a1SBtBDvpahdreLigLO0S18c0Jtwf95MBE+bSaakDiy7N1/VgOQ86+7P1wQqlZ4JEd3GIkCQAoUIX+JWkguC/Toya90wzDyFmNtVCvmsnhwhqUkLVkKfYdhJ9ARcQi/aWnY8aT0jr3UhSnJOtgEi64a7MJTvf0CQFgktaTvBWvLPjJbp1eNfgmPgoSe3xgviLthOlsIv0Y47+KX4VaS2fcqNP4eIUSJ3rllD9m3pE5Oh7jl3ZO+12I=';
    
        /**
         * 构造函数
         *
         * @param string 公钥文件（验签和加密时传入）
         * @param string 私钥文件（签名和解密时传入）
         */
        public function __construct($public_key_file = '', $private_key_file = '')
        {
            if ($public_key_file) {
                $this->_getPublicKey($public_key_file);
            }
            if ($private_key_file) {
                $this->_getPrivateKey($private_key_file);
            }
        }
    
        // 私有方法
        /**
         * 自定义错误处理
         */
        private function _error($msg)
        {
            die('RSA Error:' . $msg); //TODO
        }
    
        /**
         * 检测填充类型
         * 加密只支持PKCS1_PADDING
         * 解密支持PKCS1_PADDING和NO_PADDING
         *
         * @param int 填充模式
         * @param string 加密en/解密de
         * @return bool
         */
        private function _checkPadding($padding, $type)
        {
            if ($type == 'en') {
                switch ($padding) {
                    case OPENSSL_PKCS1_PADDING:
                        $ret = true;
                        break;
                    default:
                        $ret = false;
                }
            } else {
                switch ($padding) {
                    case OPENSSL_PKCS1_PADDING:
                    case OPENSSL_NO_PADDING:
                        $ret = true;
                        break;
                    default:
                        $ret = false;
                }
            }
            return $ret;
        }
    
        private function _encode($data, $code)
        {
            switch (strtolower($code)) {
                case 'base64':
                    $data = base64_encode('' . $data);
                    break;
                case 'hex':
                    $data = bin2hex($data);
                    break;
                case 'bin':
                default:
            }
            return $data;
        }
    
        private function _decode($data, $code)
        {
            switch (strtolower($code)) {
                case 'base64':
                    $data = base64_decode($data);
                    break;
                case 'hex':
                    $data = $this->_hex2bin($data);
                    break;
                case 'bin':
                default:
            }
            return $data;
        }
    
        private function _getPublicKey($file)
        {
            $key_content = $this->_readFile($file);
            if ($key_content) {
                $this->pubKey = openssl_get_publickey($key_content);
            }
        }
    
        private function _getPrivateKey($file)
        {
            $key_content = $this->_readFile($file);
            if ($key_content) {
                $this->priKey = openssl_get_privatekey($key_content);
            }
        }
    
        private function _readFile($file)
        {
            $ret = false;
            if (!file_exists($file)) {
                $this->_error("The file {$file} is not exists");
            } else {
                $ret = file_get_contents($file);
            }
            return $ret;
        }
    
        private function _hex2bin($hex = false)
        {
            $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
            return $ret;
        }
    
        /**
         * 生成签名
         *
         * @param string 签名材料
         * @param string 签名编码（base64/hex/bin）
         * @return 签名值
         */
        public function sign($data, $code = 'base64')
        {
            $ret = false;
            if (openssl_sign($data, $ret, $this->priKey)) {
                $ret = $this->_encode($ret, $code);
            }
            return $ret;
        }
    
        /**
         * 验证签名
         *
         * @param string 签名材料
         * @param string 签名值
         * @param string 签名编码（base64/hex/bin）
         * @return bool
         */
        public function verify($data, $sign, $code = 'base64')
        {
            $ret = false;
            $sign = $this->_decode($sign, $code);
            if ($sign !== false) {
                switch (openssl_verify($data, $sign, $this->pubKey)) {
                    case 1:
                        $ret = true;
                        break;
                    case 0:
                    case -1:
                    default:
                        $ret = false;
                }
            }
            return $ret;
        }
    
        /**
         * 加密
         *
         * @param string 明文
         * @param string 密文编码（base64/hex/bin）
         * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
         * @return string 密文
         */
        public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
        {
            $ret = false;
            if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');
            if (openssl_public_encrypt($data, $result, $this->pubKey, $padding)) {
                $ret = $this->_encode($result, $code);
            }
            return $ret;
        }
    
        /**
         * 解密
         *
         * @param string 密文
         * @param string 密文编码（base64/hex/bin）
         * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
         * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
         * @return string 明文
         */
        public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false)
        {
            $ret = false;
            $data = $this->_decode($data, $code);
            if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');
            if ($data !== false) {
                if (openssl_private_decrypt($data, $result, $this->priKey, $padding)) {
                    $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
                }
            }
            return $ret;
        }
}