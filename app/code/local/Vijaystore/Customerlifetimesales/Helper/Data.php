<?php
class Vijaystore_Customerlifetimesales_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_CLS_ENABLE   = 'customerlifetimesales_tab/customerlifetimesales_setting/customerlifetimesales_active';
	
	public function conf($code, $store = null) {
        return Mage::getStoreConfig($code, $store);
    }
	
	public function customer_lifetime_sales_enable() {
		return $this->conf(self::XML_PATH_CLS_ENABLE, $store);
	}
}

?>