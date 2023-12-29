<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2021002124676575",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEogIBAAKCAQEAhOLBenvYoX3+fstuQYycCQ/cy3aqeBC4oA3nzvfBmFGTD9Bfa436fuXSfVrgrpH2jnG4ErCU5WZGHp07+cw4YYEdnxGahF1T0dqdtaPB77vgYcd6AqLkH9BTelBZMUH9O9RwqC1XOjwa4Ce+6nSavCp0QU8POd4QpjhH+1kLVZIbVYHDToHWRK8AXdHI9tsDPQewfLFJW4Jv57hxtVZi6Rd2vrzp51jvFwCkOnm6r7+OLfGm1B0Ez27EKlWjYKH43bDwUzn1Zy2KW3ZiGbxvfIegG4uffXS8/1Gi8bdRdAbHIcpESXlyp/7Su/HeoO8NxuU12Hwln4Mb87EXTRLNvwIDAQABAoIBAFdKROHafKEIjKZpp1FztgPV4andl3krMrwgpkc0RH10CHB45lVWfFfjS/OAQNsrkpRbaxkL2aMfHax6iK08U0TpdbXJ8IBGdgsB2ls46Oo7tddFG4ajzcqAJHXSVeSffmNQ13gB4KymUtkwAp8sXYCBOsCz2EKg9rrZeZ9IDMP2NGDQEMBKixrC1UlTWfHYAR/8+2saOdCjENWCpPvma5bFK21vxWKi78OK4QMRp4mc800SG6M6SIwfC89HpGYnxLmV5DVlJOMc72k8kMGDLT8ytc6wt3mQJqyaZ+x/UV8UbLgDIrVZbDn/M+79ThKR5tylWfScFKcznrNPpaKsyyECgYEAzCwXqhlkyWYSjOQRCgHi+H0K2ztnA0jdtRwyx06SoL+VuiSnOBgCHkpMyQeb/49NNL9Qau1bXqo7U2ZmnTc2t8ahQZxKeTwXIscj9xlRZm6/POnBHmy2i4NmvKErXw4rlalUvGtT677pPifRET223LRSJyUQ18DYIamABNVRhfcCgYEApp4wzMSh3VlD+kiSKjC4a3cO5vmvANNnAeOJmW056HN0MHMOYyVuhCtSFFJhBxe21ifMOTetif3ojLxxu066DZKjqIK3blK6erblPSo8HMU8HRybMTaQvHyWzNJ+5p8NNIM5KCYZbybD+161aaoOJBHxXdOBtA9bV32pxSL6ZHkCgYBOvB4wGXTVechjjrvHaSzW+JmMK58xFBNzeTlXxMQku10JGINnzVJbzflIeOe+qMt0MObZJUlb+ze/lLizngw62J1tCNFraTHe1n7XFmtengyfd+FiUzgeGwEuctSf5n4GS/OCab1GJywZLQ+hn3P6LouTzuZs3VA7wvq8Ymr6xwKBgGelgDXhG8/V97N58XLNW+t95IdKStL4rts98BrBF4TCTUTWTdzfLCCL0kNR+4mt9s7Bcwkk2Y89o6vojdc24gYPcj8hEXpPfDFaFMA3xa0gBIUXhnLWvrKBzk0bpFVSG6TO/H89NBUwyDlWcQOKBcHqZ3s2VzjrVKbYRQOc/qYBAoGAUiwFzh37Y/9zhNQGdQM9osjw+o4644+kJROX/phudfeSCvLQGTfcfbHhdQ8CEFAN4RHLd5X5d6txGQ1wsw4B7U57yEmZ3+qFhE88Txnn/iPjTt38zaMyyQbwZJp8jL9GWtTjMuoTNkfZ2Ppinb+ahYZayH67OUButbfmf9mW/xA=",//6bg4h9r8nqwio8xjhk2vph165byoqbi2 md5秘钥
		
		//异步通知地址
		'notify_url' => "ZfbPay/notify_url",
		
		//同步跳转
		'return_url' => "http://mitsein.com/alipay.trade.wap.pay-PHP-UTF-8/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "",
		
	
);