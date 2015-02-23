# oddqc

这是一个十分简洁的 QingCloud 的 SDK ，或者说，API 的封装。

它是 MIT 开源协议，这意味着你可以随意使用源代码而无需被传染开源协议（以 [LICENSE](LICENSE) 中所列为准）。

## 基础用法

```php
	require 'oddqc.class.php';
	$o = new oddqc('key', 'secret', 'pek2');
	$o->param('action', 'DescribeInstances');
	$o->param('instances.1', 'i-foobar');
	try{
		$result = $o->send_request();
	}catch(oddqc_exception $e){
		var_dump($e);
	}
```

## 示例

在 `example` 目录中有一个简单的示例。你需要将 `oddqc.class.php` 复制到该目录下才能使用。

`ol.class.php` 展示了对 SDK 的简单的拓展。
`test.php` 展示了如何用拓展后的 SDK 来操作青云实例。

示例功能场景：

1. 你已经有一台绑定了公网 ip 的青云机器。
2. 这台机器可能是 Minecraft 服务器，或者一些什么计算服务器。
3. 你不需要 24h 开机，但是你希望它能够方便的开启关闭。
4. 那么，`ol.class.php` 则告诉你如何停止这台机器、删除绑定的 ip ，或者重新获取一个 ip 并开启这台机器。

## 错误处理

oddqc 采用异常的方式来处理错误。

以下是所有已经定义的异常。

```php
	class oddqc_exception extends Exception{}
	// ===============================
	class oddqc_curl_exception extends oddqc_exception{}
	class oddqc_client_exception extends oddqc_exception{}
	class oddqc_server_exception extends oddqc_exception{}
	// ===============================
	class oddqc_format_exception extends oddqc_client_exception{}
	class oddqc_auth_failed_exception extends oddqc_client_exception{}
	class oddqc_message_expire_exception extends oddqc_client_exception{}
	class oddqc_forbidden_exception extends oddqc_client_exception{}
	class oddqc_not_found_exception extends oddqc_client_exception{}
	class oddqc_over_balance_exception extends oddqc_client_exception{}
	class oddqc_over_quota_exception extends oddqc_client_exception{}
	// ===============================
	class oddqc_internal_exception extends oddqc_server_exception{}
	class oddqc_server_is_busy_exception extends oddqc_server_exception{}
	class oddqc_no_avaliable_resource_exception extends oddqc_server_exception{}
	class oddqc_update_in_progress_exception extends oddqc_server_exception{}
```

## 常见问题

### 报 `oddqc_curl_exception` 如下

在大多数 PHP 版本下直接使用 oddqc ，你可能会遇到下面的报错：

	SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed'

这是由于你的 PHP 中的 cURL 内置的 CA 信任列表不够完整导致的。

**请一定不要设置 `CURLOPT_SSL_VERIFYPEER` 选项，除非你十分清楚你在干什么！**

正确的修正方法如下（这个方法来自 [php 手册下的一条用户评论](http://php.net/manual/en/function.curl-setopt.php#110457)）：

1. 在 curl 的 [CA Extract](http://curl.haxx.se/docs/caextract.html) 页面，下载最新的 CA bundle。
2. 修改你的 php.ini ，将 `curl.cainfo` 指向你刚刚下载的 CA bundle，如：`curl.cainfo=c:\php\cacert.pem`

