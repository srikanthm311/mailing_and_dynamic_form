<?php
session_start();

require 'classes/products.php';
$obj = new products();
$obj->getCategories();
/*echo '<pre>';print_r($_SESSION);
exit;*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style>
tr th {
	
	
	color: #337ab7}

</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/jquery.validate.js"></script>
<script src="js/script.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link rel="stylesheet" href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
<title>Products</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
            <h2>Product Adding</h2>
            <?php if(isset($_SESSION['message'])){ echo '<div style="color:green">'.$_SESSION["message"].'</div>'; unset($_SESSION['message']);}?>
                <form action="add_product.php" method="post" id="add_products">
                	<table class="table">
                        <tr>
                            <th>Product Name: </th>
                            <th>Product Category: </th>
                            <th>Product Price: </th>
                            <th>Product Brand: </th>
                            <th>Product Color: </th>
                            <th>Product Quantity: </th>
                            <th>&nbsp;</th>
                        </tr>
                        <tr class="clonned_tr">
                            <td><input type="text" name="data[0][name]" id="product_name_0" class="form-control increase_index input_validate" required="required"/></td>
                            <td width="15%"><select name="data[0][category]" id="product_category_0" class="increase_index form-control input_validate" required />
                            		<option value = "">Select category</option>
									<?php  foreach($obj->categories as $key => $category){?>
                                    <option value = "<?php echo $category['tc_category_id']?>"><?php echo $category['tc_category_name']?></option>
                                    <?php }?>
                                </select></td>
                            <td><input type="text" name="data[0][price]" id="product_price_0" class="form-control increase_index input_validate" required/></td>
                            <td><input type="text" name="data[0][brand]" id="product_brand_0" class="form-control increase_index input_validate" required/></td>
                            <td><input type="text" name="data[0][color]" id="product_color_0" class="form-control increase_index input_validate" required/></td>
                            <td >
                                <input type="text" name="data[0][quantity]" id="quantity_0" class="form-control increase_index input_validate" required/>
                            </td>
                            <td width="15%"><button type="button" id="addclone_0" name = "addButton_0" class="btn btn-primary addclone increase_index" /> <i class="fa fa-plus" aria-hidden="true"></i> </button>
                            <button type="button" id="delete_clone_0" name = "delButton_0" class="btn btn-danger del_clone increase_index" style="display:none"/> <i class="fa fa-minus" aria-hidden="true"></i> </button></td>
                        </tr>
                    </table>
                    <input type="submit" name="submit" id="submit" class="btn btn-primary" />
                </form>
            </div>
        </div>
    </div> 
</body>

</html>