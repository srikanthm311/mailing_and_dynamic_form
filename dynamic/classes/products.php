<?php 
class products{
	
	public $productID;
	public $product_name;
	public $product_price;
	public $product_brand;
	public $product_color;
	public $categories =array();
	public $products = array();
	
	public $err_info;
	
	
	public function __construct()
	{
		$con = mysql_connect('localhost','root','')or die('server not connected'.mysql_error());
		$conn = mysql_select_db('dynamic')or die('database not selected'.mysql_error());
	}
	
	public function addProduct()
	{
		foreach($this->products as $product)
		{
			$sql = "INSERT INTO tbl_products SET
			
				tp_product_name = '".mysql_real_escape_string($product['name'])."',
				tp_category_id = ".mysql_real_escape_string($product['category']).",
				tp_product_brand = '".mysql_real_escape_string($product['brand'])."',
				tp_product_color = '".mysql_real_escape_string($product['color'])."',
				tp_product_price = '".mysql_real_escape_string($product['price'])."',
				tp_quantity = '".mysql_real_escape_string($product['quantity'])."',
				tp_status = 'ACTIVE',
				tp_created_date = CURRENT_DATE(),
				tp_created_time = TIME(NOW())
			";
			//echo $sql; exit;
			$result = mysql_query($sql);
			}
			if($result)
			{
				return true;
			}
			else
			{
				$this->err_info = mysql_error();
				return false;
			}
			
	}
	
	public function getCategories()
	{
		$sql = "SELECT * FROM tbl_category WHERE tc_status = 'ACTIVE'";
		
		$resset = mysql_query($sql)or die(mysql_error());
		
		while($fetch = mysql_fetch_assoc($resset))
		{
			$this->categories[$fetch['tc_category_id']] =  $fetch;
		}
	}
	
}


?>