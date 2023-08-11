# 基于hyperf/scout manticoresearch全文检索引擎

## 使用方法

### 第一步（已安装请忽略）

```composer require hyperf/scout```

### 第二步

```composer require scybwdf/manticore-scout```


### 第三步

```php bin/hyperf.php vendor:publish hyperf/scout```


或者手动添加配置文件scout.php配置示例如下
```
return [
'default' => env('SCOUT_ENGINE', 'manticoresearch'),
'chunk' => [
'searchable' => 500,
'unsearchable' => 500,
],
'prefix' => env('APP_ENV', ''),
'soft_delete' => false,
'concurrency' => 100,
'engine' => [
'manticoresearch'=>[
'driver' =>Scybwdf\ManticoreScout\Provider\ManticoreSearchProvider::class,
'host' => env('MANTICORE_HOST','localhost'), // Manticore Search 主机
'port' => env('MANTICORE_PORT',9308),        // Manticore Search 端口
'setting'=>[],
'auto_index_create'=>true //自动创建index
   ]
  ],
];
```


使用方式可以查看hyperf官网
https://www.hyperf.wiki/3.0/#/zh-cn/scout?id=%e6%a8%a1%e5%9e%8b%e5%85%a8%e6%96%87%e6%a3%80%e7%b4%a2


Manticore Search介绍

Manticore Search 是一个使用 C++ 开发的高性能搜索引擎，创建于 2017 年，其前身是 Sphinx Search 。Manticore Search 充分利用了 Sphinx，显着改进了它的功能，修复了数百个错误，几乎完全重写了代码并保持开源。这一切使 Manticore Search 成为一个现代，快速，轻量级和功能齐全的数据库，具有出色的全文搜索功能。

Manticore Search目前在GitHub收获3.7k star，拥有大批忠实用户。同时开源者在GitHub介绍中明确说明了该项目是是Elasticsearch的良好替代品，在不久的将来就会取代ELK中的E。



同时，来自 MS 官方的测试表明 Manticore Search 性能比 ElasticSearch 有质的提升：



在一定的场景中，Manticore 比 Elasticsearch 快 15 倍！完整的测评结果，可以参考:

https://manticoresearch.com/blog/manticore-alternative-to-elasticsearch/
优势
它与其他解决方案的区别在于：

它非常快，因此比其他替代方案更具成本效益。例如，Manticore:

对于小型数据，比MySQL快182倍（可重现）

对于日志分析，比 快29倍（可重现）

对于小型数据集，比Elasticsearch快15倍（可重现）

对于中等大小的数据，比Elasticsearch快5倍（可重现）

对于大型数据，比Elasticsearch快4倍（可重现）

在单个服务器上进行数据导入时，最大吞吐量比Elasticsearch快最多2倍（可重现）

由于其现代的多线程架构和高效的查询并行化能力，Manticore能够充分利用所有CPU核心，以实现最快的响应时间。

强大而快速的全文搜索功能能够无缝地处理小型和大型数据集。

针对小、中、大型数据集提供逐行存储。

对于更大的数据集，Manticore通过Manticore Columnar Library提供列存储支持，可以处理无法适合内存的数据集。

自动创建高效的二级索引，节省时间和精力。

成本优化的查询优化器可优化搜索查询以实现最佳性能。

Manticore是基于SQL的，使用SQL作为其本机语法，并与MySQL协议兼容，使您可以使用首选的MySQL客户端。

通过PHP、Python、JavaScript、Java、Elixir和Go等客户端，与Manticore Search的集成变得简单。

Manticore还提供了一种编程HTTP JSON协议，用于更多样化的数据和模式管理。

Manticore Search使用C++构建，启动快速，内存使用最少，低级别优化有助于其卓越性能。

实时插入，新添加的文档立即可访问。

提供互动课程，使学习轻松愉快。

Manticore还拥有内置的复制和负载均衡功能，增加了可靠性。

可以轻松地从 和csv等来源同步数据。

虽然不完全符合ACID，但Manticore仍支持事务和binlog以确保安全写入。

内置工具和SQL命令可轻松备份和恢复数据。


Craigslist、Socialgist、PubChem、Rozetka和许多其他公司使用 Manticore 进行高效搜索和流过滤。

