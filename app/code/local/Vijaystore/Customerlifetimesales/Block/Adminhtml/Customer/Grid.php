<?php
class Vijaystore_Customerlifetimesales_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{

    public function __construct()
    {
        parent::__construct();
		if(Mage::helper('customerlifetimesales')->customer_lifetime_sales_enable()) {
			$this->setDefaultSort('base_grand_total');
		}
	}
	
	protected function _prepareCollection()
	{
		$active = Mage::helper('customerlifetimesales')->customer_lifetime_sales_enable();
		if($active)
		{
		
			$collection = Mage::getResourceModel('customer/customer_collection')
				->addNameToSelect()
				->addAttributeToSelect('email')
				->addAttributeToSelect('created_at')
				//->addAttributeToSelect('entity_id')
				->addAttributeToSelect('group_id')
				->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
				->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
				->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
				->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
				->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
			$collection->getSelect()->joinLeft(array('sub' => $collection->getTable('sales/order')),'e.entity_id = sub.customer_id ', array('sub.total_qty_ordered' =>'sub.total_qty_ordered', 'sub.base_grand_total'=>'sub.base_grand_total'));
			
			//$collection->addFieldToFilter('sub.total_qty_ordered','250 ');
			$collection->getSelect()->columns(' CAST(SUM( total_qty_ordered )as UNSIGNED) AS qty, SUM( base_grand_total ) AS base_grand_total');
			
			$col = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
			$dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
			$collection->getSelect()->group(array('e.entity_id'));		
			$collection->getSelect()->order(array(" $col $dir"));
			
			//$collection->getSelect()->having($attribute)
			//$collection->getSelect()->where(" qty1 BETWEEN 10 and 200");		
			// echo $collection->getSelect();			
			$this->setCollection($collection);
			if ($this->getCollection())
			{
				$this->_preparePage();
				
				$columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
				$dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
				$filter   = $this->getParam($this->getVarNameFilter(), null);
				if (is_null($filter)) {
					$filter = $this->_defaultFilter;
				}
				if (is_string($filter)) {
					$data = $this->helper('adminhtml')->prepareFilterString($filter);
					$this->_setFilterValues($data);
				}
				else if ($filter && is_array($filter)) {
					$this->_setFilterValues($filter);
				}
				else if(0 !== sizeof($this->_defaultFilter)) {
					$this->_setFilterValues($this->_defaultFilter);
				}

				if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
					$dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
					$this->_columns[$columnId]->setDir($dir);
					$this->_setCollectionOrder($this->_columns[$columnId]);
				}

				if (!$this->_isExport) {
					$this->getCollection()->load();
					$this->_afterLoadCollection();
				}
			}
			return $this;
		}
		else {
			return parent::_prepareCollection();
		}
    }
	
	public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if(count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

	protected function _prepareColumns()
	{
		if(Mage::helper('customerlifetimesales')->customer_lifetime_sales_enable())
		{
			$this->addColumnAfter('qty', array(
				'header'    => Mage::helper('customer')->__('Total Qty'),
				'index'     => 'qty',
				'type'  	=> 'number',
				'default'  	=> '0',
				'width'  	=> '50px',
				'filter'    => false,
			),'email');
			
			$this->addColumnAfter('base_grand_total', array(
				'header'    => Mage::helper('customer')->__('Total Amount'),
				'index'     => 'base_grand_total',
				//'type'  => 'number',
				'default'  => '0',
				'width'  	=> '50px',
				'filter'    => false,
			),'qty');
					
		}
		
		parent::_prepareColumns();
		$this->removeColumn('group');
		return $this;
	}
}