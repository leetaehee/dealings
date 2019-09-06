<form id="voucher-charge-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
    <input type="hidden" id="idx" name="idx" value="<?=$idx?>">
	<input type="hidden" name="mileage_type" value="<?=$mileage_type?>">
    
    <p><h3>[문화상품권 충전]</h3></p>

	<p>
		<label for="accountNo">상품권번호: </label>
		<input type="text" id="accountNo" name="account_no" value="" size="18">
		<input type="hidden" id="accountBank" name="account_bank" value="문화상품권">
	</p>
	<br>


	<p>
		<label for="chargeCost">충전금액: </label>
		<input type="text" id="chargeCost" name="charge_cost" value="" size="16">
	</p>
	<br>

	<p>
		<label for="chargeName">입금자: </label>
		<input type="text" id="chargeName" name="charge_name" value="" size="10">
	</p>
	<br>
	
	<p>
		<input type="button" id="charge-btn" value="충전하기">
	</p>
</form>
