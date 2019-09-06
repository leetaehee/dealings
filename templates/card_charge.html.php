<form id="card-charge-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
    <input type="hidden" id="idx" name="idx" value="<?=$idx?>">
	<input type="hidden" name="mileage_type" value="<?=$mileage_type?>">
    
    <p><h3>[신용카드 충전]</h3></p>

	<p>
		<label for="accountBank">카드종류: </label>
		<select id="accountBank" name="account_bank">
			<option value="">선택하세요.</option>
			<option value="삼성">삼성</option>
			<option value="BC">BC</option>
			<option value="현대">현대</option>
			<option value="KB국민">KB국민</option>
			<option value="외환">외환</option>
			<option value="신한">신한</option>
			<option value="롯데">롯데</option>
			<option value="하나">하나</option>
			<option value="NH카드">NH카드</option>
		</select>
	</p>
	<br>

	<p>
		<label for="accountNo">카드번호: </label>
		<input type="text" id="accountNo" name="account_no" value="" size="22">
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
