<form id="virtual-charge-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
    <input type="hidden" id="idx" name="idx" value="<?=$idx?>">
	<input type="hidden" name="mileage_type" value="<?=$mileage_type?>">
    
    <p><h3>[가상계좌 충전]</h3></p>

	<p>
		<label for="accountBank">입금은행: </label>
		<select id="accountBank" name="account_bank">
			<option value="">선택하세요.</option>
			<option value="기업은행">기업은행</option>
			<option value="국민은행">국민은행</option>
			<option value="신한은행">신한은행</option>
			<option value="외환은행">외환은행</option>
			<option value="우리은행">우리은행</option>
			<option value="부산은행">부산은행</option>
			<option value="광주은행">광주은행</option>
			<option value="우체국">우체국</option>
		</select>
		<input type="button" id="issue-btn" value="계좌조회">
	</p>
	<br>

	<p>
		계좌번호: <span id="accountNo">은행을 선택하세요</span>
		<input type="hidden" class="accountNo" name="account_no" value="">
	</p>
	<br>


	<p>
		<label for="chargeCost">입금금액: </label>
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
