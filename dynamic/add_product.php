<?php 
/*echo'<pre>';
print_r($_POST);
exit;*/
session_start();
require 'classes/products.php';
$obj = new products();
$obj->products = $_POST['data'];
/*echo'<pre>';
print_r($obj->products);
exit;*/
$result = $obj->addProduct();
if($result)
{
	$_SESSION['message'] = 'products added successfully';
	header('location:index.php');
}
else
{
	echo 'something went wrong'.$obj->err_info;
	$_SESSION['message'] = 'something went wrong'.$obj->err_info;
	header('location:index.php');
}

?>