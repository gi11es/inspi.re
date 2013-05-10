var current_quality;
var current_size;
var price = new Array();
var shippingcost = new Array();
var additionalshippingcost = new Array();
var markup = 0;
var current_shipping;

document.observe('dom:loaded', initEntryOrder);

function setQuality(event) {
	var element = Event.element(event);
	
	current_quality = parseInt(element.id.substr(8));
	
	var current_price = price[current_quality][current_size];
	
	if ($('margin_simulate')) {
		var real_price = current_price + current_price * parseInt($('var_real_markup').getValue()) / 100;
		$('margin_simulate').update(real_price);
	}
	
	current_price = current_price + current_price * markup / 100;
	$('total').update(current_price);
	$('paypal_amount').setValue(current_price);
	$('paypal_quality').setValue($('var_quality_' + current_quality + '_name').getValue());
}

function clickOption(event) {
	var element = Event.element(event);
	element.blur();
}

function setSize(event) {
	var element = Event.element(event);
	
	current_size = parseInt(element.id.substr(5));
	
	var current_price = price[current_quality][current_size];
	
	if ($('margin_simulate')) {
		var real_price = current_price + current_price * parseInt($('var_real_markup').getValue()) / 100;
		$('margin_simulate').update(real_price);
	}
	
	current_price = current_price + current_price * markup / 100;
	
	$('total').update(current_price);
	$('paypal_amount').setValue(current_price);
	$('paypal_size').setValue($('var_size_' + current_size + '_name').getValue());
}

function setShipping(event) {
	var element = Event.element(event);
	
	current_shipping = parseInt(element.id.substr(9));
	
	var current_shipping_cost = shippingcost[current_shipping];
	$('base_shipping').update(current_shipping_cost);
	
	var current_additional_shipping_cost = additionalshippingcost[current_shipping];
	$('additional_shipping').update(current_additional_shipping_cost);
	
	$('paypal_handling').setValue(current_shipping_cost - current_additional_shipping_cost);
	$('paypal_shipping').setValue(current_additional_shipping_cost);
	$('paypal_additional_shipping').setValue(current_additional_shipping_cost);
}

function initEntryOrder() {
	markup = parseInt($('var_markup').getValue());

	price[1] = new Array();
	price[1][1] = 18;
	price[1][2] = 26;
	price[1][3] = 40;
	
	price[2] = new Array();
	price[2][1] = 29;
	price[2][2] = 42;
	price[2][3] = 58;
	
	price[3] = new Array();
	price[3][1] = 37;
	price[3][2] = 50;
	price[3][3] = 70;
	
	shippingcost[1] = 15;
	shippingcost[2] = 28;
	shippingcost[3] = 42;
	shippingcost[4] = 47;
	
	additionalshippingcost[1] = 6;
	additionalshippingcost[2] = 11;
	additionalshippingcost[3] = 17;
	additionalshippingcost[4] = 19;
	
	if ($('quality_1')) {
		$('quality_1').observe('change', setQuality).observe('click', clickOption);
		$('quality_2').observe('change', setQuality).observe('click', clickOption);
		$('quality_3').observe('change', setQuality).observe('click', clickOption);
		
		$('size_1').observe('change', setSize).observe('click', clickOption);
		$('size_2').observe('change', setSize).observe('click', clickOption);
		$('size_3').observe('change', setSize).observe('click', clickOption);
		
		$('shipping_1').observe('change', setShipping).observe('click', clickOption);
		$('shipping_2').observe('change', setShipping).observe('click', clickOption);
		$('shipping_3').observe('change', setShipping).observe('click', clickOption);
		$('shipping_4').observe('change', setShipping).observe('click', clickOption);
		
		current_quality = 2;
		current_size = 2;
		current_shipping = parseInt($('var_shipping_region').getValue());
		
		$('quality_2').checked = true;
		$('size_2').checked = true;
		$('shipping_' + current_shipping).checked = true;
		
		var current_price = price[current_quality][current_size];
		
		if ($('margin_simulate')) {
			var real_price = current_price + current_price * parseInt($('var_real_markup').getValue()) / 100;
			$('margin_simulate').update(real_price);
		}
		
		current_price = current_price + current_price * markup / 100;
		$('total').update(current_price);
		$('paypal_amount').setValue(current_price);
		
		var current_shipping_cost = shippingcost[current_shipping];
		$('base_shipping').update(current_shipping_cost);
		
		var current_additional_shipping_cost = additionalshippingcost[current_shipping];
		$('additional_shipping').update(current_additional_shipping_cost);
		
		$('paypal_handling').setValue(current_shipping_cost - current_additional_shipping_cost);
		$('paypal_shipping').setValue(current_additional_shipping_cost);
		$('paypal_additional_shipping').setValue(current_additional_shipping_cost);
		
		$('paypal_quality').setValue($('var_quality_' + current_quality + '_name').getValue());
		$('paypal_size').setValue($('var_size_' + current_size + '_name').getValue());
	}
}