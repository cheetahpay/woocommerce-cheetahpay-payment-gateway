<?php
$shareAndSellNo = '08035271339';
$denominations = [100, 200, 400, 500, 750, 1500];
$denominationPrice = 0;

foreach ($denominations as $value)
{
    if($actualPrice <= $value)
    {
        $denominationPrice = $value;
        
        break;
    }
}

?>

<div id="custom_input">
	<div>
		<div style="float: left; width: 50%;">
			<div id="airtime-pin" onclick="paymentMethodSelected(this)" 
				data-target="airtime-pin-form" data-object="pinDeposit">Airtime Pin</div>
		</div>
		<div style="float: left; width: 50%;">
			<div id="airtime-transfer" onclick="paymentMethodSelected(this)" 
				data-target="airtime-transfer-form" data-object="airtimeTransfer">Airtime Transfer</div>
		</div>
		<div style="clear: both;"></div>
	</div>
	<br />
	<div id="airtime-pin-form" class="payment-form">
        <p class="form-row form-row-wide">
            <label for="pin" class="">Airtime Pin <abbr class="required" title="required">*</abbr></label>
            <input type="text" name="pin" id="pin" placeholder="" value="" class="form-field" required="required" >
        </p>
        <p class="form-row form-row-wide">
            <label for="pin_amount" class="">Amount <abbr class="required" title="required">*</abbr></label>
            <select name="pin_amount" id="pin_amount" required="required" class="form-field" >
        		<option value="" >Select Amount</option>
        		<?php foreach ($denominations as $value): ?> 
        		<option value="<?= $value ?>"  <?= (($denominationPrice == $value) ? ' selected ' : '') ?>><?= $value ?></option>
        		<?php endforeach; ?>
        	</select>
    	</p>
    	<p class="form-row form-row-wide">
    		<label for="pin_network">Network <abbr class="required" title="required">*</abbr></label>
    		<select name="pin_network" id="pin_network" required="required" class="form-field">
    			<option value="MTN" selected="selected">MTN</option>
    			<option value="9 MOBILE" selected="selected">9 MOBILE</option>
    			<option value="AIRTEL" selected="selected">AIRTEL</option>
    			<option value="GLO" selected="selected">GLO</option>
    		</select>
    	</p>
    </div>
    <div id="airtime-transfer-form" class="payment-form">
    	<div style="background-color: #aab8c1; padding: 10px; border-radius: 5px; 
    	       margin-bottom: 10px; border: 1px solid #5e7484;">
    		Transfer airtime to this number <b><?= $shareAndSellNo ?></b>
    	</div>
    	<p class="form-row form-row-wide">
    		<label for="transfer_phone">
        		<small>Phone no with which you will make the transfer</small>
        		<abbr class="required" title="required">*</abbr>
    		</label>
    		<input type="text" name="transfer_phone" id="transfer_phone" value="" placeholder="eg: 07035845632" required="required" class="form-field" >
    	</p>
    	<p class="form-row form-row-wide">
    		<label for="transfer_amount">Amount <abbr class="required" title="required">*</abbr></label>
    		<input type="text" name="transfer_amount" id="transfer_amount" required="required" class="form-field" value="<?= $actualPrice ?>" >
    	</p>
    	<p class="form-row form-row-wide">
    		<label for="transfer_network">Network <abbr class="required" title="required">*</abbr></label>
    		<select name="transfer_network" id="transfer_network" required="required" class="form-field"  >
    			<option value="MTN TRANSFER" selected="selected">MTN SHARE AND SELL</option>
    		</select>
    	</p>
    </div>
	<div>
		<input type="hidden" id="cheetah_object" name="cheetah_object" />
	</div>
</div>
<style>
    #custom_input{
       /* background-color: #fff;
        -webkit-box-shadow:0px 0px 6px 2px #d4d2d2 ;
        -moz-box-shadow:0px 0px 6px 2px #d4d2d2 ;
        box-shadow:0px 0px 6px 2px #d4d2d2 ; */
    }
    .payment-form{
/*         padding: 8px 8px; */
    }
    .payment-form .form-row{
        margin-bottom: 15px !important;
    }
    .payment-form .form-field{
        padding: 6px 3px;
        border: 1px solid #bab8b5; 
        font-size: 0.9rem;
        background-color: #e6e5e5;
    }
    #airtime-pin, #airtime-transfer{
        background:-webkit-linear-gradient(0deg, rgb(183, 181, 178) 0%, rgb(225, 223, 220) 80%);
        background:-o-linear-gradient(0deg, rgb(183, 181, 178) 0%, rgb(225, 223, 220) 80%);
        background:-moz-linear-gradient(0deg, rgb(183, 181, 178) 0%, rgb(225, 223, 220) 80%);
        background:linear-gradient(0deg, rgb(183, 181, 178) 0%, rgb(225, 223, 220) 80%);
        text-align: center;
        border-bottom: 1px solid #bab8b5;
        cursor: pointer;
        font-size: 0.9rem;
        padding: 5px 3px;
    }
    #airtime-transfer{
        border-left: 1px solid #bab8b5;
    }
    #airtime-pin.active, #airtime-transfer.active{
        background:-webkit-linear-gradient(0deg, rgb(253, 171, 0) 0%, rgb(255, 198, 79) 80%);
        background:-o-linear-gradient(0deg, rgb(253, 171, 0) 0%, rgb(255, 198, 79) 80%);
        background:-moz-linear-gradient(0deg, rgb(253, 171, 0) 0%, rgb(255, 198, 79) 80%);
        background:linear-gradient(0deg, rgb(253, 171, 0) 0%, rgb(255, 198, 79) 80%);
        border-bottom: 2px solid #d28e00;
    }
    #airtime-transfer-form{
        display: none;
    }
</style>
<script type="text/javascript">

var prevActiveObj = null;
paymentMethodSelected(document.querySelector('#airtime-pin'));

function paymentMethodSelected(obj) {

	var id = obj.dataset.target;
	var z_object = obj.dataset.object;
	
	if(prevActiveObj && prevActiveObj.dataset.target == id) return;
	if(prevActiveObj) prevActiveObj.setAttribute('class', '');

	obj.setAttribute('class', 'active');
	document.querySelector('#custom_input #cheetah_object').value = z_object;
	
	
	// For the entire form
	var all = document.querySelectorAll('.payment-form');
	for (var i = 0; i < all.length; i++) {
		all[i].style.display = 'none';
	}
	
	var allFields = document.querySelectorAll('.payment-form .form-field');
	for (var i = 0; i < allFields.length; i++) {
		allFields[i].disabled = true;
	}

	// For the selected form
	var selectedForm = document.querySelector('#' + id);
	selectedForm.style.display = 'block';

	var selectedFields = document.querySelectorAll('#' + id + ' .form-field');
	for (var i = 0; i < selectedFields.length; i++) {
		selectedFields[i].disabled = false;
	}
	
	prevActiveObj = obj;
}

</script>
