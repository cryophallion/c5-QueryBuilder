<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @author David Lasky <dave@teknoziz.com>
 * @package Models
 * @version 1.0
 * Class that can create Concrete5 specialized queries
 */
class QueryBuilderModel {

	/**
	 * Database Table Name
	 * @var string db table 
	 */
	protected $table;

	/**
	 * Array of fields to return
	 * @var array field array
	 */
	protected $fields = array();

	/**
	 * Internal value used to create unique names for joined tables
	 * @var int join counter
	 */
	protected $current = 1;

	/**
	 * Array of joined tables
	 * @var array table joins
	 */
	protected $joins = array();

	/**
	 * Array of Filters
	 * @var array filter array
	 */
	protected $filters = array();

	/**
	 * Array of Text Filters
	 * @var array text filter array
	 */
	protected $text_filters = array();

	/**
	 * Array of Filters with IN arrays
	 * @var array IN filter array
	 */
	protected $in_filters = array();

	/**
	 * Array of GROUP BY fields
	 * @var array group fields
	 */
	protected $groups = array();

	/**
	 * Preset Query (if you want to override fields/join array)
	 * @var string query
	 */
	protected $query;

	/**
	 * Array of ORDER BY fields
	 * @var array order fields
	 */
	protected $order = array();

	/**
	 * Database Object (from C5)
	 * @var object database 
	 */
	protected $db;

	/**
	 * Number for limiting returned rows
	 * @var int limit number
	 */
	protected $limit = 0;

	/**
	 * Number for offsetting returned rows
	 * @var int offset number
	 */
	protected $offset = 0;

	/**
	 * C5 standard attribute table list, for finding attribute values
	 * @var array attribute tables
	 */
	protected $attribute_tables = array('text' => 'atDefault', 'textarea' => 'atDefault', 'address' => 'atAddress', 'boolean' => 'atBoolean', 'date_time' => 'atDateTime', 'image_file' => 'atFile', 'number' => 'atNumber', 'rating' => 'atNumber', 'select' => 'atSelectOptionsSelected');

	public static function createFieldArray($array, $fieldName) {
		$fields = array();
		foreach ($array as $item) {
			$fields[] = $item[$fieldName];
		}
		return $fields;
	}

	/**
	 * initializes object and sets DB
	 */
	public function __construct() {
		$this->db = Loader::db();
	}

	/**
	 * Sets default query
	 * @param string $query default query
	 */
	public function setQuery($query) {
		$this->query = $query;
	}

	/**
	 * Sets database table
	 * @param string $table database table
	 */
	public function setTable($table) {
		$this->table = $table;
	}

	/**
	 * Adds a Field to query
	 * @param string $field Field Name
	 */
	public function addField($field) {
		$this->fields[] = $field;
	}

	/**
	 * Adds an custom attribute table to the attribute tables
	 * @param string $atHandle C5 attribute handle
	 * @param string $atTable table where attribute values are stored
	 */
	public function addAttributeTable($atHandle, $atTable) {
		$this->attribute_tables[$atHandle] = $atTable;
	}

	/**
	 * Creates Joins to get an attribute as part of the query
	 * @param string $attribute_key_handle C5 handle for attribute key
	 * @param string $field_as field used for unique field name
	 * @param string $join_as uniqye handle for join table
	 * @param string $attribute_type_handle C5 handle for attribute type
	 * @param string $attribute_category C5 attribute category (User,File,Collection)
	 * @param string $attribute_category_table used to set the shortened category table name
	 * @param string $attribute_field_name attribute field name
	 */
	public function addAttributeField($attribute_key_handle, $field_as, $join_as, $attribute_type_handle, $attribute_category, $attribute_category_table = null, $attribute_field_name = null) {

		switch ($attribute_category) {
			case 'User':
				$attribute_field_comparison = 'uID';
				if (empty($attribute_category_table)) {
					$attribute_category_table = 'Users';
				}
				break;

			case 'File':
				$attribute_field_comparison = 'fID';
				if (empty($attribute_category_table)) {
					$attribute_category_table = 'Files';
				}
				break;

			case 'Collection':
				$attribute_field_comparison = 'cID';
				if (empty($attribute_category_table)) {
					$attribute_category_table = 'Collections';
				}
				break;

			default:
				break;
		}
		$attribute_field_name = (empty($attribute_field_name) ? $attribute_field_comparison : $attribute_field_name);
		if (array_key_exists($attribute_type_handle, $this->attribute_tables)) {
			$avTable = $this->attribute_tables[$attribute_type_handle];
		} else {
			$avTable = 'atDefault';
		}

		$this->fields[] = $join_as . '.value AS ' . $field_as;
		$this->joins[] = 'LEFT JOIN ' . $attribute_category . 'AttributeValues AS ak' . $this->current . ' ON ak' . $this->current . '.avID=(SELECT MAX(avID) from ' . $attribute_category . 'AttributeValues av' . $this->current . ' where av' . $this->current . '.' . $attribute_field_comparison . '=' . $attribute_category_table . '.' . $attribute_field_name . ' and av' . $this->current . '.akID=(Select akID from AttributeKeys WHERE akHandle="' . $attribute_key_handle . '"))';
		$this->joins[] = 'LEFT JOIN ' . $avTable . ' ' . $join_as . ' on ' . $join_as . '.avID=ak' . $this->current . '.avID';
		$this->current++;
	}

	/**
	 * Add a Join to the query
	 * @param string $join join string
	 */
	public function addJoin($join) {
		$this->joins[] = $join;
	}

	/**
	 * Adds a filter to query
	 * @param string $field field name
	 * @param string $filter value to be evaluated against
	 * @param string $comparison comparison type
	 * @param string $or_filter Whether the filter is to use OR comparison
	 */
	public function addFilter($field, $filter, $comparison = '=', $or_filter = false) {
		$this->filters[] = array('field' => $field, 'filter' => $filter, 'comparison' => $comparison, 'or' => $or_filter);
	}

	/**
	 * Adds a text filter
	 * @param array $field_array array of field names
	 * @param string $text text comparison
	 * @param string $or_filter Whether the filter is to use OR comparison
	 */
	public function addTextFilter($field_array, $text, $or_filter = false) {
		$this->text_filters[] = array('fields' => $field_array, 'text' => $text, 'or' => $or_filter);
	}

	/**
	 * Adds IN filter to query
	 * @param string $field field name
	 * @param string $in_array
	 * @param string $or_filter Whether the filter is to use OR comparison
	 */
	public function addINFilter($field, $in_array, $or_filter = false) {
		$this->in_filters[] = array('field' => $field, 'array' => $in_array, 'or' => $or_filter);
	}

	/**
	 *  Adds Group by field to query
	 * @param string $field group field
	 */
	public function addGroup($field) {
		$this->groups[] = $field;
	}

	/**
	 * Adds row limit to query
	 * @param int $num_results number of results
	 * @param int $offset offset results
	 */
	public function addLimit($num_results, $offset = 0) {
		$this->limit = $num_results;
		$this->offset = $offset;
	}

	/**
	 * Adds Order field to query
	 * @param string $field field name
	 * @param string $direction direction of order
	 */
	public function addOrder($field, $direction = "ASC") {
		$this->order[] = array('field' => $field, 'direction' => $direction);
	}

	/**
	 * returns query results
	 * @return array query results
	 */
	public function get() {
		$query_array = $this->buildQuery();
		//$this->db->debug = true;
		if ($query_array['limit'] != 1) {
			return $this->db->GetArray($query_array['query'], $query_array['values']);
		} else {
			return $this->db->GetRow($query_array['query'], $query_array['values']);
		}
	}

	/**
	 * Builds database query
	 * @return array query and values arrays
	 */
	protected function buildQuery() {
		$vals = array();
		if (!empty($this->query)) {
			$query = $this->query;
		} else {
			$query = 'SELECT ' . implode(', ', $this->fields);
			$query .= ' FROM ' . $this->table;
			if (!empty($this->joins)) {
				$query .= ' ' . implode(' ', $this->joins);
			}
		}
		if (!empty($this->filters) || !empty($this->in_filters) || !empty($this->text_filters)) {
			$filter_array = array();
			foreach ($this->filters as $filter) {
				if (strtolower($filter['filter']) != 'null') {
					$filter_array[] = array('filter' => $filter['field'] . $filter['comparison'] . '?', 'or' => $filter['or']);
					$vals[] = $filter['filter'];
				} else {
					$filter_array[] = array('filter' => $filter['field'] . $filter['comparison'] . ' NULL', 'or' => $filter['or']);
				}
			}
			foreach ($this->text_filters as $text_filter) {
				$filter_array[] = array('filter' => 'MATCH (' . implode(',', $text_filter['fields']) . ') AGAINST (? IN NATURAL LANGUAGE MODE)', 'or' => $text_filter['or']);
				$vals[] = $text_filter['text'];
			}
			foreach ($this->in_filters as $in_filter) {
				if (is_array($in_filter['array'])) {
					if (count($in_filter['array']) == 1) {
						$vals[] = $in_filter['array'][0];
					} else {
						$vals = array_merge($vals, $in_filter['array']);
					}
				} else {
					$vals[] = $in_filter['array'];
				}

				$filter_string = $in_filter['field'] . ' IN (';
				$count = count($in_filter['array']);
				for ($index = 1; $index <= $count; $index++) {
					$filter_string .= '?';
					if ($index != $count) {
						$filter_string .=',';
					}
				}
				$filter_array[] = array('filter' => $filter_string . ')', 'or' => $in_filter['or']);
			}
			$first_filter = true;
			foreach ($filter_array as $filter) {
				if ($first_filter == true) {
					$query .= ' WHERE ';
					$first_filter = false;
				} else {
					if ($filter['or'] == true) {
						$query .= ' OR  ';
					} else {
						$query .= ' AND ';
					}
				}
				$query .= $filter['filter'];
			}
		}
		if (!empty($this->groups)) {
			$query .= ' GROUP BY ';
			$query .= implode(',', $this->groups);
		}

		if (!empty($this->order)) {
			$order_array = array();
			$query .= ' ORDER BY ';
			foreach ($this->order as $order) {
				$order_array[] = $order['field'] . ' ' . $order['direction'];
			}
			$query .= implode(', ', $order_array);
		}
		if (!empty($this->limit)) {
			$query .= ' LIMIT ' . $this->offset . ',' . $this->limit;
			$limit = $this->limit;
		} else {
			$limit = 0;
		}
		return array('query' => $query, 'values' => $vals, 'limit' => $limit);
	}
}
?>
