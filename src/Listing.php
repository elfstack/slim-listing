<?php

namespace Elfstack\SlimListing;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

/**
 * Listing Builder for Slim Framework
 *
 * @package Elfstack\SlimListing
 * @author Stanley Cao <cao.stanley@protonmail.com>
 */
class Listing
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @var array
     */
    protected $columns = ['*'];

    /**
     * @var array
     */
    protected $sortableColumns;

    /**
     * @var array
     */
    protected $filterableColumns;

    /**
     * @var array
     */
    protected $searchableColumns;


    private function __construct($model)
    {
        if (!is_a($model, Model::class)) {
            throw new \Exception('Slim Listing is only compatible with eloquent');
        }

        $this->model = $model;
        $this->query = $model->newQuery();
        return $this;
    }

    public static function create($model): self
    {
        return new Listing($model);
    }

    public function setColumns(array $columns) {
        $this->columns = $columns;
        return $this;
    }

    public function attachSorting(array $sortableColumns)
    {
        $this->sortableColumns = $sortableColumns;
        return $this;
    }

    public function attachFiltering(array $filterableColumns)
    {
        $this->filterableColumns = $filterableColumns;
        return $this;
    }

    public function attachSearching(array $serchableColumns)
    {
        $this->searchableColumns = $serchableColumns;
        return $this;
    }

    private function querySorting(string $column, string $direction): void
    {
        if (in_array($column, $this->sortableColumns) && in_array($direction, ['desc', 'asc'])) {
            $this->query->orderBy($column, $direction);
        }
    }

    private function queryFiltering(string $column, $value): void
    {
        if (in_array($column, $this->filterableColumns)) {
            if (is_array($value)) {
                $this->query->whereIn($column, $value);
            } else {
                $this->query->where($column, $value);
            }
        }
    }

    private function querySearch(string $value): void
    {
        foreach ($this->searchableColumns as $column) {
            $this->searchLike($this->query, $column, $value);
        }
    }

    private function paginate()
    {
        return $this->query->paginate($this->perPage);
    }

    public function getFilter(string $filter): array
    {
        $values = explode(';', $filter);

        return array_map(function ($value) {
            $delimiterPos = strpos($value, ':');
            $column = substr($value, 0, $delimiterPos);
            $values = explode(',', substr($value, $delimiterPos + 1));
            return [
                'column' => $column,
                'values' => $values
            ];
        }, $values);
    }

    /**
     * Process listing request
     * Ordering Query: orderBy=<column>&direction=<desc|asc>
     * Filtering Query: filter=<column1:val1,val2;column2:val1,val2;column3:val1,val2>
     * Pagination Query: perPage=<perPage>&page=<page>
     * Search Query: keyword=<keyword>
     *
     * @param Request $request
     * @param Response $response
     *
     * @return LengthAwarePaginator
     */
    public function get(Request $request, Response $response = null)
    {
        $this->processRequest($request);

        if (!isset($response)) {
            return $this->paginate();
        } else {
            return $response->withJson($this->paginate());
        }
    }

    public function processRequest(Request $request)
    {
        $params = $request->getQueryParams();

        if ($this->sortableColumns && !empty($params['orderBy'])) {
            $this->querySorting($params['orderBy'], $params['direction']);
        }

        if ($this->filterableColumns && !empty($params['filter'])) {
            foreach ($this->getFilter($params['filter']) as $filter) {
                $this->queryFiltering($filter['column'], $filter['values']);
            }
        }

        if ($this->searchableColumns && !empty($params['keyword'])) {
            $this->querySearch($params['keyword']);
        }

        if (!empty($params['perPage'])) {
            $this->perPage = $params['perPage'];
        }

        if (!empty($params['page'])) {
            $this->perPage = $params['page'];
        }
    }
    /**
     * Modify built query in any way
     *
     * @param callable $modifyQuery
     * @return $this
     */
    public function modifyQuery(callable $modifyQuery): self
    {
        $modifyQuery($this->query);

        return $this;
    }


    /**
     * @param $query
     * @param $column
     * @param $token
     */
    private function searchLike($query, $column, $token): void
    {

        // MySQL and SQLite uses 'like' pattern matching operator that is case insensitive
        $likeOperator = 'like';

        // but PostgreSQL uses 'ilike' pattern matching operator for this same functionality
        if ($this->model->getConnection()->getDriverName() == 'pgsql') {
            $likeOperator = 'ilike';
        }

        $query->orWhere($column, $likeOperator, '%'.$token.'%');
    }
}
