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