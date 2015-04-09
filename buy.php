<?php
// Start the session
session_start();
?>
<html>
<head><title>Buy Products</title></head>
<?php
error_reporting(E_ALL);
ini_set('display_errors','OFF');
$CategoryTree_XMl = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
$Cat_Tree_xml = new SimpleXMLElement($CategoryTree_XMl);
?>
<?php
if (($_GET["submitResult"]==="Search")==1){
	$keywords_Value		= $_GET["searchKeyword"];
	$DropDownvalue_Value= $_GET["Category_id"];

	if($keywords_Value==""){
			$Link_value = 'http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$DropDownvalue_Value.'&numItems=20';
		}else{
			$Link_value = 'http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&category='.$DropDownvalue_Value.'&keyword='.$keywords_Value.'&numItems=20';
			}
			$_SESSION["Keyword_session"]=$keywords_Value;
			$_SESSION["DropDownvalue_Value_session"]=$DropDownvalue_Value;
	//if($_SESSION["Keyword_session"]!="" && $_SESSION["DropDownvalue_Value_session"]!="" && $DropDownvalue_Value==null && $keyword==null){
	//	$Link_value = 'http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$_SESSION["DropDownvalue_Value_session"].'&numItems=20';
	}else{
		$Link_value = 'http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$_SESSION["DropDownvalue_Value_session"].'&numItems=20';
	}
$List_Xml = file_get_contents($Link_value);
$Result_xml = new SimpleXMLElement($List_Xml);
?>
<body>
<?php
if($_GET["EmptyBasketname"]=="EmptyBasket"){
		$_SESSION["Products_list"] = array();
		$_SESSION["Total_Price"] = 0 ;
		echo "Number of Items in the cart = ".sizeof($_SESSION['Products_list']);
	}

if(isset($_GET["delete"])){
		$_SESSION["Total_Price"] = (double)$_SESSION["Total_Price"] - (double)$_SESSION["Products_list"][$_GET["delete"]]["price"];
		unset($_SESSION["Products_list"][$_GET["delete"]]);	
		}

echo empty($_SESSION["Products_list"]) ? "Array is empty.": "Array is not empty.";

if($_GET["buy"]){
	array_push($_SESSION["Products_list"],$_GET["id"]); // Items added to cart
	echo "Number of Items in the cart = ".sizeof($_SESSION['Products_list']);
try{
			$product_Link = 'http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&productId='.$_GET["buy"];
			$product_XML = file_get_contents($product_Link);
			$xml_product_details = new SimpleXMLElement($product_XML);
			if(!array_key_exists($_GET["buy"],$_SESSION["Products_list"])){
					$_SESSION["Total_Price"] += (double)$xml_product_details->categories->category->items->product->minPrice;
			}
			$_SESSION["Products_list"][$_GET["buy"]]["id"] = (string)$xml_product_details->categories->category->items->product['id'];
			$_SESSION["Products_list"][$_GET["buy"]]["img"] = (string)$xml_product_details->categories->category->items->product->images->image->sourceURL;
			$_SESSION["Products_list"][$_GET["buy"]]["price"] = (double)$xml_product_details->categories->category->items->product->minPrice;
			$_SESSION["Products_list"][$_GET["buy"]]["name"] = (string)$xml_product_details->categories->category->items->product->name;
			$_SESSION["Products_list"][$_GET["buy"]]["url"] = (string)$xml_product_details->categories->category->items->product->productOffersURL;
			}catch(Exception $e){
			}

	}//|||||| Upar wala Change this code 
?>
<div id="EmptyBasketdiv">
<form action="buy.php" method="GET">
	<input type="submit" value="EmptyBasket" name="EmptyBasketname">	
</form>
</div>
<?php echo $_SESSION["Keyword_session"];?>
<?php echo $keyword;?>
<?php echo $DropDownvalue_Value;?>
<?php echo $_SESSION["DropDownvalue_Value_session"];?>
<div id="basket">
	<label>Shopping Basket:</label>
	<p>
	<table border='1'>
	<tbody>
	<?php if(!empty($_SESSION["Products_list"])){ ?>
	
			<?php try{ ?>
			<?php foreach($_SESSION["Products_list"] as $item_product){ 
				if($item_product!=null){?>
				<tr>
					<td><a href='<?php echo $item_product["url"] ?>'>
					<img src='<?php echo $item_product['img'] ?>' /></a></td>
					<td><?php echo $item_product['name'] ?></td>
					<td>$<?php echo $item_product['price'] ?></td>
					<td><a href='buy.php?delete=<?php echo $item_product['id']; ?>'>Delete</a></td>
				</tr>
			<?php 
				}
			} ?>
			<?php }catch(Exception $e){
			
			} ?>
		</tbody>
	</table>
	<?php try{ ?> 
	<label>Total: $<?php echo $_SESSION["Total_Price"]; ?></label>
	<?php }catch(Exception $e){
	}
 } ?>
	</p>
</div>
<div id="Mainarea">
<div id="SearchProduct"><!--Search DIV starts here-->
<form action="buy.php" method="GET">
	<fieldset><legend>Find products:</legend>
<select name="Category_id">
				<option value="<?php  print $Cat_Tree_xml->category['id']?>">
				<?php print $Cat_Tree_xml->category->name ?>
				</option>
				<?php 
					foreach($Cat_Tree_xml->category->categories->category as $cat_sub1)
				{?>
						<optgroup label="<?php print $cat_sub1->name?>" value="<?php print $cat_sub1['id'] ?>">
							<option value="<?php print $cat_sub1['id']?>"><?php print $cat_sub1->name ?></option>
							<?php 
								foreach($cat_sub1->categories->category as $Main_cat)
							{ ?>
									<option value="<?php print $Main_cat['id'] ?>">
									<?php print $Main_cat->name ?></option>
							<?php 
							}
				?>
						</optgroup>
					<?php } ?>
</select>
<label>Search keywords: 
<input type="text" name="searchKeyword"/><label>		
<input type="submit" value="Search" name="submitResult" />
</fieldset>
</form>
</div><!--Search DIV Ends here-->
<div id="Results"><!--Results Div Starts -->
<!--Made custom table using javascript or php scirpting -->
<table border='1'>
				<?php foreach($Result_xml->categories->category->items->product as $product_list_xml) {?>
					<tr>
						<td><a href='?buy=<?php echo $product_list_xml['id'] ?>'>
						<img src='<?php echo $product_list_xml->images->image->sourceURL ?>'/></a></td>
						<td><?php echo $product_list_xml->name; ?></td>
						<td>$<?php echo $product_list_xml->minPrice; ?></td>
						<td><?php echo $product_list_xml->fullDescription; ?></td>
					</tr>
				<?php } ?>
		</table>
</div><!--Results Div Ends -->
</div><!--MainArea Div Ends -->
<!--For only catergory search 	   :->>>>>> http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=1627-->
<!--For only catergory and Keyword :->>>>>> http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=1627-->
</body>
</html> 