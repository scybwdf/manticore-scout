<?php

namespace Scybwdf\ManticoreScout\Engine;

use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Scout\Builder;
use Hyperf\Scout\Engine\Engine;
use Manticoresearch\Client;
use Manticoresearch\Index;
use Hyperf\Paginator\LengthAwarePaginator;
use Manticoresearch\ResultSet;

// 需要引入分页类

class ManticoreSearchEngine extends Engine
{
    protected array $config; // ManticoreSearch 配置
    protected Client $client; // ManticoreSearch 客户端

    public function __construct($client,$config)
    {
        $this->config = $config;
        $this->client = $client;

    }

    // 更新操作
    public function update($models): void
    {
        foreach ($models as $model) {
            $index = $this->getIndex($model);
            // 使用模型的数据更新索引
            $updateData = $model->toSearchableArray();
            unset($updateData['id']); // 移除 'id' 属性

            $index->replaceDocument($updateData, $model->getScoutKey());
        }
    }

    // 删除操作
    public function delete($models): void
    {
        foreach ($models as $model) {
            $index = $this->getIndex($model);
            // 从索引中删除文档
            $index->deleteDocument($model->getScoutKey());
        }
    }

    // 搜索操作
    public function search(Builder $builder)
    {
        $query = $builder->query;
        $results = [];

        $manticoreSearchClient = new Index($this->client,$builder->model->searchableAs());
        $search = $manticoreSearchClient->search($query)->get();

        $totalHits = $search->getTotal();
        // 处理搜索结果并格式化为 Scout 结果格式
        foreach ($search as $result) {
            // 将 $result 转换为 Scout 期望的格式
            $results[] = [
                'model' => $builder->model,
                'index' => $builder->model->searchableAs(),
                'id' => $result->getId(),
                'score' => $result->getScore(),
                'data'=>$result->getData()
                // ... 其他字段
            ];

        }

        // 转换结果为 Collection
        $collection = ModelCollection::make($results);
        $collection->totalHits = $totalHits;

        return $collection;
    }

    // 映射操作
    public function map(Builder $builder, $results, $model): ModelCollection
    {
        return ModelCollection::make($results->map(function ($result) use ($model) {
            $modelKey = $model->getKeyName();
            $modelInstance = $model->newQuery()->where($modelKey, $result['id'])->first();
            if ($modelInstance) {
                $modelInstance->searchScore = $result['score'];
            }
            return $modelInstance;
        }));
    }

    // 获取搜索结果总数
    public function getTotalCount($results): int
    {


        if ($results instanceof ResultSet) {
            return $results->getTotal();
        }

        return $results->total();
    }

    /**
     * 对搜索接口分页
     * @param Builder $builder
     * @param $perPage
     * @param $page
     * @return LengthAwarePaginator
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $offset = ($page - 1) * $perPage;

        $manticoreSearchClient = new Index($this->client,$builder->model->searchableAs());
        $searchResults = $manticoreSearchClient->search($builder->query)->offset($offset)->limit($perPage)->get();
        // 根据结果类型来获取 hits 和总数量
        if ($searchResults instanceof \Manticoresearch\ResultSet) {

            $totalHits = $searchResults->getTotal();
        } else {

            $totalHits = count($searchResults);
        }
        $results=[];
        foreach ($searchResults as $result) {
            // 将 $result 转换为 Scout 期望的格式
            $results[] = [
                'model' => $builder->model,
                'index' => $builder->model->searchableAs(),
                'id' => $result->getId(),
                'score' => $result->getScore(),
                'data'=>$result->getData()
                // ... 其他字段
            ];

        }
        // 使用当前页的结果和总数量创建 LengthAwarePaginator 实例
        return new LengthAwarePaginator(
            $results,
            $totalHits,
            $perPage,
            $page
        );
    }


    // 映射搜索结果中的ID
    public function mapIds($results): \Hyperf\Utils\Collection
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    // 刷新操作
    public function flush($model): void
    {
        $index = $this->getIndex($model);
        $index->flush();
    }



    // 获取 ManticoreSearch 客户端
    public function getClient(): Client
    {
        return $this->client;
    }

    // 获取 ManticoreSearch 索引
    public function getIndex($model): Index
    {
        $indexName = $model->searchableAs();
        $autoIndexCreate=$this->config['auto_index_create']?? 1;
        if (!$this->indexExists($indexName)&&$autoIndexCreate) {

            $this->createIndex($indexName, $model);
        }

        return new Index($this->client, $indexName);
    }

    /**
     * 判断index是否存在
     * @param $indexName
     * @return bool
     */
    protected function indexExists($indexName): bool
    {
        try {
            $this->client->indices()->describe(['index' => $indexName]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 创建index
     * @param $indexName
     * @param $model
     * @return void
     */
    protected function createIndex($indexName, $model)
    {
        $settings = $this->getDefaultIndexSettings();
        $fields = $this->getModelIndexFields($model);
        $index = new Index($this->client, $indexName);
        $index->create($fields, $settings);
    }

    /**
     * 获取默认设置配置
     * @return array
     */
    protected function getDefaultIndexSettings()
    {

        $defaultConfig=[
            'charset_table' => 'chinese',
            'morphology' => 'icu_chinese',
            'stopwords' => 'zh',
            'min_infix_len' => 3,
            'min_prefix_len' => 2,
            'min_word_len' => 1,
        ];
        return $this->config['setting']??$defaultConfig;
    }


    protected function getModelIndexFields($model)
    {
        $fields = [];
        if($model->getFields()){
            $fields=$model->getFields();
        }else{
            $tableColumns = $model->getConnection()->getDoctrineSchemaManager()->listTableColumns($model->getConnection()->getTablePrefix().$model->getTable());
            foreach ($tableColumns as $column) {
                $fieldType = $this->mapDatabaseFieldType($column->getType()->getName());
                if($column->getName()!='id'){
                    $fields[$column->getName()] = ['type' => $fieldType];
                }
            }
        }

        return $fields;
    }

    protected function mapDatabaseFieldType($databaseFieldType)
    {
        $typeMap = [
            'string' => 'text',
            'integer' => 'integer',
            'datetime' => 'timestamp',
            // ... 其他映射关系
        ];

        return $typeMap[$databaseFieldType] ?? 'text';
    }


}
